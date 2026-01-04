Git Execution Architecture (WSL / Linux / Apache)
Overview

CodeInSync executes Git commands from a PHP/Apache runtime in a variety of environments:

Native Linux
WSL (Linux on Windows)
Windows-hosted filesystems (/mnt/c)
Apache (www-data)
CLI (developer user)

Due to filesystem and permission constraints—especially under WSL + NTFS—Git must not be executed as the Apache user when performing mutating operations (commit, config, pull, etc.).

Instead, Git commands are executed as the repository owner using sudo, when required.

This document describes the required system configuration to support this architecture.

Why This Is Necessary
The Problem

When repositories are located on Windows-mounted filesystems (e.g. /mnt/c/...):

NTFS does not fully support POSIX permission semantics
Git relies on lockfiles (*.lock) and chmod() for atomic writes
Running Git as www-data may fail with errors such as:

chmod on .git/config.lock failed: Operation not permitted

This affects more than just git config — it also impacts:

git commit
git add
git pull
git merge
git checkout
git gc

The Solution

Git commands must be executed as the repository owner, not as Apache.

This is achieved by allowing the Apache user (www-data) to invoke only the Git binary as the repository owner via sudo.

Required Sudo Configuration
⚠️ This step is mandatory before Git features will work correctly

Create the following sudoers file:

sudo editor /etc/sudoers.d/codeinsync-git


Add exactly this line:

www-data ALL=(debianuser) NOPASSWD: /usr/bin/git

What this allows

www-data may run /usr/bin/git
Commands execute as debianuser (the repo owner)
No password prompt
No shell access
No arbitrary command execution

What this does NOT allow
No sudo su
No shell (/bin/bash, /bin/sh)
No wildcard commands
No environment escalation

This is a minimal, tightly-scoped privilege.

Security Considerations

This approach follows established best practices used by:

CI/CD systems
Deployment tools
Build servers
Git hosting platforms

Key principles:
Apache remains unprivileged
Git mutations run under the repo owner
Privilege scope is limited to a single binary
No interactive sudo is possible

This is safer than allowing Git corruption caused by improper execution context.

Runtime Behavior (High Level)

At runtime, CodeInSync:

Detects the current execution user
Detects the repository owner
Detects whether sudo is available
Uses sudo -u <repoOwner> git ... only when necessary
Falls back to direct Git execution when safe

This logic is centralized and environment-aware.

Environment Compatibility Matrix
Environment	Repo Location	Git Execution
Linux ext4	/home/...	Direct
WSL ext4	/home/...	Direct
WSL NTFS	/mnt/c/...	sudo -u repoOwner
Apache (www-data)	Any	sudo -u repoOwner (if needed)
Windows PHP	Any	No sudo, Git mutations disabled

Important Notes

This configuration must be applied before using Git features in CodeInSync
Failure to do so may result in:
    Git command failures
    Stale lockfiles
    Repository corruption
This is not a workaround — it is an architectural requirement

Summary

    All Git mutations must be executed as the repository owner.

The sudo configuration described above is a foundational requirement for reliable Git automation in mixed Windows / WSL / Linux environments.