<?php

# Regular expressions used to detect issue ids in commit messages.
#
# Matching TortoiseGit regular expressions (`Settings > Hook Scripts > Issue Tracker Config`).
# Copy&paste the next two lines into `bugtraq.logregex`, without the comment hash and leading whitespace.
# 	#\d+\b
# 	\d+
#
# Mentioned issues.
$issue_ids_regexp = '/\#(\d+)\b/';
# Issues marked as closed.
$closed_issue_ids_regexp = '/\b (?: close(?: s|d)? | fix(?: es|ed)? | resolve(?: s|d)? | implement(?: s|ed)?) (?: \s+ (?: feature | bug | issue | ticket | task)s?)? (?: \s+ | : \s*) (\#\d+ (?: (?: \s* , \s* | \s+ and \s+ | \s+) \#\d+)*) \b/xi';
# Issue IDs (within matches of the previous two regular expressions).
$single_issue_id_regexp = '/\d+/';

# Regular expression used to detect git commit hashes (SHA-1) in commit comments received from the post-commit git hook.
$comment_commit_hash_regexp = '/^ [Cc]ommit \s+ \[ ([0-9a-f]{7,40}) \] \( [^)]* \/ ([0-9a-f]{7,40}) \)/x';
# Regular expression used to detect git commit hashes (SHA-1) in command-line arguments received from the git hooks.
$commit_hash_regexp = '/^ [0-9a-f]{7,40} $/x';

$cache_filepath = __DIR__ . '/cache.json';
$head_filepath = __DIR__ . '/head.json';
