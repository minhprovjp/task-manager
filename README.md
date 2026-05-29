# DBS401 SQL Injection Playground

Intentionally-vulnerable Task Manager for the FPT University DBS401 project.
Contains **10 SQL injection challenges** at Easy, Medium, and Hard difficulty.

## Quick Start (Fresh Linux VM)

```bash
git clone https://github.com/minhprovjp/task-manager
cd task-manager
sudo setup.sh
```

or

```bash
sudo apt install curl -y
curl -fsSL https://raw.githubusercontent.com/minhprovjp/task-manager/master/setup.sh | sudo bash
```

The script installs Apache, MariaDB, PHP, clones this repo, imports the
database, and prints hints for all 6 flags.

## Manual Installation

1. Place the files in your web root (e.g. `/var/www/html/task-manager`).
2. Import `task_manager.sql` into MySQL/MariaDB.
3. Update `config/constants.php` with your database credentials.
4. Browse to `http://localhost/task-manager/`.

## Flag Overview

There are 10 flags in the project, they do not have any format.

No table or column name contains the word "flag" — values are hidden as
task descriptions and API tokens.

## Technologies

- PHP 8.x (procedural)
- MySQL / MariaDB
- Apache 2
