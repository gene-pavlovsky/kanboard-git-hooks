<?php

# Regular expressions used to detect issue ids in commit messages.
#
# Matching TortoiseGit regular expressions (`Settings > Hook Scripts > Issue Tracker Config`).
# Copy&paste the next two lines into `bugtraq.logregex`, without the comment hash and leading whitespace.
# 	\b([Ff]eatures?|[Bb]ugs?|[Ii]ssues?|[Tt]ickets?|[Tt]asks?|[Ii]mplement(ed|s)|[Ff]ix(ed|es)|[Cc]los(ed|es))(\s+|:\s*)(#\d+((\s*,\s*|\s+and\s+|\s+)#\d+)*)\b
# 	\d+
#
# Related issues (including issues marked as closed).
$issue_ids_regexp = '/\b (?: feature | bug | issue | ticket | task)s? (?: \s+ | : \s*) (\#\d+ (?: (?: \s* , \s* | \s+ and \s+ | \s+) \#\d+)*) \b/xi';
# Issues marked as closes.
$closed_issue_ids_regexp = '/\b (?: implement(?: ed|s) | fix(?: ed|es) | clos(?: ed|es)) (?: \s+ (?: feature | bug | issue | ticket | task)s?)? (?: \s+ | : \s*) (\#\d+ (?: (?: \s* , \s* | \s+ and \s+ | \s+) \#\d+)*) \b/xi';
# Issue ids (within matches of the previous two regular expressions).
$single_issue_id_regexp = '/\d+/';

# Regular expression used to detect git commit hashes (SHA-1) in commit comments received from the post-commit git hook.
$comment_commit_hash_regexp = '/^ [Cc]ommit \s+ \[ ([0-9a-f]{7,40}) \] \( [^)]* \/ ([0-9a-f]{7,40}) \)/x';
# Regular expression used to detect git commit hashes (SHA-1) in command-line arguments received from the git hooks.
$commit_hash_regexp = '/^ [0-9a-f]{7,40} $/x';

$cache_filepath = __DIR__ . '/cache.json';
$head_filepath = __DIR__ . '/head.json';
