<?php

# Set your Kanboard API URL and token. They can be found in `Settings > API`, e.g.:
# http://dev.example.com/kanboard/?controller=config&action=api
#
# Matching TortoiseGit issue tracker URL (`Settings > Hook Scripts > Issue Tracker Config`).
# Copy&paste the next line into `bugtraq.url`, without the comment hash and leading whitespace.
# 	http://dev.example.com/kanboard/?controller=task&action=show&task_id=%BUGID%
#
# API endpoint
$api_url = 'http://dev.example.com/kanboard/jsonrpc.php';
# API token
$api_token = '317bf42ed0cbf4f0e83cf6b6e13c3b4f024ff214d7ba562924035cc4cf607826';

# Project and user settings.
$project_name = 'ExampleProject';
$user_email = 'user@example.com';

# Issues marked as closed are moved to this column with the following comment.
# For convenience, add an automatic action in Kanboard project settings to close a task when it's moved to this column.
$close_column_title = 'Done';
$close_issue_comment = 'Moved to column ' . $close_column_title;

# Set to true if you want to reject commits if their message doesn't reference any issues.
$require_issue_ref = false;
