Git hooks for integration with Kanboard
=======================================

Developed by Gene Pavlovsky <gene.pavlovsky@gmail.com>

Description
-----------

Provides git hooks for integration with [Kanboard](http://kanboard.net/).
Automatically post comments and optionally close Kanboard tasks by referencing them in git commit messages. 
```
Mention single issue #55. Mention tasks #77, #78, and #79. Mention more bugs #123, #124 and #125 and some more separate ones: Feature #45, ticket #46 and bug #47.

Closes task #202
Fixed ticket #123
Resolves bug #88.
Implements feature #69.
Closes issue #99.
```

The hooks are written in `bash`, the Kanboard interface script in `php` using [fguillot/JsonRPC](https://github.com/fguillot/JsonRPC).

- `hooks/commit-msg` detects task ids in the commit message and checks if they exist in Kanboard and belong to the correct project. If validation fails, commit is aborted.
- `hooks/post-commit` posts comments and optionally closes Kanboard tasks. If commit is amended, attempts to amend the task comment.

Downloads
------------
[Complete ZIP of the repository](https://github.com/gene-pavlovsky/kanboard-git-hooks/archive/master.zip)

Installation
------------

1. Copy the contents of `hooks` directory to `.git/hooks` inside your repository.
2. In `hooks/kanboard`, install [fguillot/JsonRPC](https://github.com/fguillot/JsonRPC) using `php composer.phar install`.
3. Edit `hooks/post-commit` to customize the comment format.
4. Edit `hooks/kanboard/config.php` and optionally `hooks/kanboard/config.default.php`.  
Read the comments for explanations, as well as suggested TortoiseGit Issue Tracker Config settings.
