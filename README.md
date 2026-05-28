# DBS401 SQL Injection Playground

Intentionally-vulnerable Task Manager for the FPT University DBS401 project.
Contains **6 SQL injection challenges** at Easy, Medium, and Hard difficulty.

## Quick Start (Fresh Linux VM)

```bash
sudo bash setup.sh
```

The script installs Apache, MariaDB, PHP, clones this repo, imports the
database, and prints hints for all 6 flags.

## Manual Installation

1. Place the files in your web root (e.g. `/var/www/html/task-manager`).
2. Import `task_manager.sql` into MySQL/MariaDB.
3. Update `config/constants.php` with your database credentials.
4. Browse to `http://localhost/task-manager/`.

## Flag Overview

| # | Level   | Technique            | Entry Point                   | Flag |
|---|---------|----------------------|-------------------------------|------|
| 1 | Easy    | In-band numeric OR   | `list-task.php?list_id=`      | `n0t_s0_h4rd_t0_f1nd` |
| 2 | Easy    | In-band string OR    | `search.php?q=`               | `s3Arch_n0t_s0_s3cur3` |
| 3 | Medium  | UNION SELECT         | `search.php?q=`               | `un10n_1s_p0w3rful` |
| 4 | Medium  | Error-based          | `search.php?q=`               | `3rr0r_b4s3d_m4st3r` |
| 5 | Hard    | Boolean blind        | `user-check.php?id=`          | `b00l34n_bl1nd_pr0` |
| 6 | Hard    | Time-based blind     | `list-task.php?list_id=`      | `t1m3_1s_1lus10n` |
| 7 | Easy    | Auth Bypass          | `login.php`                   | `byp4ss_auth_w1th_sql1`|
| 8 | Medium  | UNION SELECT         | `profile.php?user_id=`        | `un10n_strik3s_b4ck`   |
| 9 | Hard    | ORDER BY             | `index.php?sort=`             | `0rd3r_by_1nj3ct10n`   |
| 10| Hard    | INSERT               | `contact.php`                 | `1ns3rt_1nt0_pwn3d`    |

No table or column name contains the word "flag" — values are hidden as
task descriptions and API tokens.

## Technologies

- PHP 8.x (procedural)
- MySQL / MariaDB
- Apache 2
