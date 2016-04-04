#!/usr/bin/env php
<?php

require __DIR__ . '/config.default.php';
require __DIR__ . '/config.php';

# Make sure this script doesn't run via the webserver.
if (php_sapi_name() != 'cli')
	quit('This script can only be run with PHP-CLI.', 1);

# Parse command-line options.
$options = array_slice($argv, 1);
$validate = false;
$head = array();
foreach ($options as $opt) {
	if ($opt == '--validate')
		$validate = true;
	else if (preg_match($commit_hash_regexp, $opt))
		$head[] = $opt;
	else
		quit('Unrecognized option "' . $opt . '".', 1);
}

function quit($message, $code = 0) {
	fwrite(STDERR, $message . "\n");
	exit($code);
}

function match_issues($regexp, $issue_regexp, $line, &$issue_ids) {
	if (preg_match_all($regexp, $line, $matches)) {
		$count = count($matches[0]);
		for ($i = 0; $i < $count; ++$i) {
			if (preg_match_all($issue_regexp, $matches[1][$i], $issue_matches)) {
				$issue_count = count($issue_matches[0]);
				for ($j = 0; $j < $issue_count; ++$j)
					$issue_ids[] = $issue_matches[0][$j];
			}
		}
	}
}

# Detect references to issues; concat all lines to get the commit message.
$post_comment = '';
$issue_ids = array();
$closed_issue_ids = array();
while (($line = fgets(STDIN, 1024))) {
	$post_comment .= $line;
	match_issues($issue_ids_regexp, $single_issue_id_regexp, $line, $issue_ids);
	match_issues($closed_issue_ids_regexp, $single_issue_id_regexp, $line, $closed_issue_ids);
}

$closed_issue_ids = array_unique($closed_issue_ids);
$issue_ids = array_unique(array_merge($issue_ids, $closed_issue_ids));
sort($closed_issue_ids, SORT_NUMERIC);
sort($issue_ids, SORT_NUMERIC);
$touched_ids = array_diff($issue_ids, $closed_issue_ids);

$hash_tag = function($value) {
	return '#' . $value;
};

if ($validate) {
	if (!count($issue_ids)) {
		if ($require_issue_ref)
			quit('Commit message must reference an issue.', 1);
		echo "No related issues.\n";
		exit(0);
	}
	# Print detected issue ids.
	if (count($touched_ids))
		echo 'Touched issues: ' . implode(', ', array_map($hash_tag, $touched_ids)) . ".\n";
	if (count($closed_issue_ids))
		echo 'Closed  issues: ' . implode(', ', array_map($hash_tag, $closed_issue_ids)) . ".\n";
	
	# Save commit history.
	file_put_contents($head_filepath, implode("\n", $head));
}
else {
	if (!count($issue_ids))
		exit(0);
	
	# Load commit history.
	if (file_exists($head_filepath)) {
		$old_head = explode("\n", file_get_contents($head_filepath));
		unlink($head_filepath);
	}
	else {
		$old_head = array();
	}
}

# Setup Kanboard JSON-RPC API.
require __DIR__ . '/vendor/autoload.php';

use JsonRPC\Client;

$client = new Client($api_url);
$client->authentication('jsonrpc', $api_token);

$checksum = md5($project_name . "\n" . $close_column_title . "\n" . $user_email);
if ($cache_filepath && file_exists($cache_filepath)) {
	# Load and decode cached data (if exists).
	$cache_str = file_get_contents($cache_filepath);
	if ($cache_str)
		$cache_data = json_decode($cache_str, true);
}

if (isset($cache_data) && ($cache_data['checksum'] == $checksum)) {
	# Use cached data.
	$project_id = $cache_data['project_id'];
	$column_id = $cache_data['column_id'];
	$user_id = $cache_data['user_id'];
}
else {
	# Get the project id.
	$project = $client->getProjectByName($project_name);
	if (!$project)
		quit('Project "' . $project_name . '" not found.', 1);
	$project_id = $project['id'];
	
	# Get columns and users.
	$batch = $client->batch();
	$batch->getColumns($project_id);
	$batch->getAllUsers();
	list($columns, $users) = $batch->send();
	
	# Get the column id for moving closed issues to.
	if (($key = array_search($close_column_title, array_column($columns, 'title'))) === false)
		quit('Column "' . $close_column_title . '" not found in project #' . $project_id . '.', 1);
	$column_id = $columns[$key]['id'];
	
	# Get the user id for posting comments.
	if (($key = array_search($user_email, array_column($users, 'email'))) === false)
		quit('User "' . $user_email . '" not found.', 1);
	$user_id = $users[$key]['id'];
	
	# Encode and save cached data.
	$cache_data = array('checksum' => $checksum, 'project_id' => $project_id, 'column_id' => $column_id, 'user_id' => $user_id);
	file_put_contents($cache_filepath, json_encode($cache_data, JSON_PRETTY_PRINT) . "\n");
	echo "Received project info.\n";
}

if ($validate) {
	$batch = $client->batch();
	foreach ($issue_ids as $issue_id)
		$batch->getTask($issue_id);
	$issues = $batch->send();

	# Validate all issue ids.
	foreach ($issues as $batch_id => $issue) {
		if (!$issue)
			quit('Issue #' . $client->batch[$batch_id]['params'][0] . ' not found.', 1);
		if ($issue['project_id'] != $project_id)
			quit('Issue #' . $client->batch[$batch_id]['params'][0] . ' belongs to project #' . $issue['project_id'] . '.', 1);
	}
	echo "Validated issue ids.\n";
}
else {
	if ((count($head) >= 2) && (count($old_head) >= 2) && ($head[1] == $old_head[1])) {
		echo "Amended commit detected.\n";
		# The commit is an amend commit.
		$old_commit = $old_head[0];
		$new_commit = $head[0];
		
		# Get comments for all issues.
		$batch = $client->batch();
		foreach ($issue_ids as $issue_id)
			$batch->getAllComments($issue_id);
		$all_comments = $batch->send();
		
		# Check if any comments reference the amended commit.
		$amended_issue_ids = array();
		$batch = $client->batch();
		foreach ($all_comments as $batch_id => $comments) {
			$count = count($comments);
			for ($i = $count - 1; $i >= 0; --$i) {
				$comment = $comments[$i];
				if (preg_match($comment_commit_hash_regexp, $comment['comment'], $matches)) {
					if ($matches[2] == $old_commit) {
						$batch->removeComment($comment['id']);
						$amended_issue_ids[] = $comment['task_id'];
						for (++$i; $i < $count; ++$i) {
							$comment = $comments[$i];
							if ($comment['comment'] == $close_issue_comment) {
								$batch->removeComment($comment['id']);
								break;
							}
						}
					}
					break;
				}
			}
		}
		# Print amended issue ids.
		if (count($issue_ids))
			echo 'Amended issues: ' . implode(', ', array_map($hash_tag, $amended_issue_ids)) . ".\n";
		else
			echo "No amended issues.\n";
	}
	else {
		$batch = $client->batch();
	}
	
	# Post comments and close issues.
	foreach ($issue_ids as $issue_id) {
		$batch->createComment($issue_id, $user_id, $post_comment);
	}
	foreach ($closed_issue_ids as $issue_id) {
		$batch->moveTaskPosition($project_id, $issue_id, $column_id, 1);
		$batch->createComment($issue_id, $user_id, $close_issue_comment);
	}
	$results = $batch->send();
	$errors = false;
	foreach ($results as $batch_id => $result) {
		if ($result === false) {
			$call = $client->batch[$batch_id];
			$method = $call['method'];
			$params = $call['params'];
			if ($method == 'createComment')
				$params[2] = '...';
			fwrite(STDERR, 'Error: ' . $method . '(' . join($params, ', ') . ") failed.\n");
			$errors = true;
		}
	}
	if ($errors)
		exit(1);
}
