#!/usr/bin/env bash
#
# DBS401 SQL Injection Automated Setup
# ==================================================
# Installs & configures everything needed to run the
# intentionally-vulnerable Task Manager on a fresh Debian/Ubuntu VM.
#
# Usage:  sudo bash setup.sh
#

set -u

# ──────────────────────────────────────────────
# 0.  Configuration
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

log "DBS401 SQL Injection Automated Setup"
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

start_service() {
    local svc="$1"

    if command -v systemctl >/dev/null 2>&1; then
        if systemctl is-active --quiet "${svc}" 2>/dev/null; then
            log "${svc} already running"
            return 0
        fi

        if systemctl start "${svc}" 2>/dev/null; then
            log "Started ${svc} with systemctl"
            return 0
        fi
    fi

    if command -v service >/dev/null 2>&1; then
        if service "${svc}" status >/dev/null 2>&1; then
            log "${svc} already running"
            return 0
        fi

        if service "${svc}" start 2>/dev/null; then
            log "Started ${svc} with service"
            return 0
        fi
    fi

    if [[ -x "/etc/init.d/${svc}" ]]; then
        if "/etc/init.d/${svc}" start 2>/dev/null; then
            log "Started ${svc} with init script"
            return 0
        fi
    fi

    warn "Could not start ${svc} automatically"
    return 1
}

prepare_mariadb_runtime_dir() {
    mkdir -p /run/mysqld 2>/dev/null || true
    chown -R mysql:mysql /run/mysqld 2>/dev/null || true
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

start_service "apache2" || true

prepare_mariadb_runtime_dir
if ! start_service "mariadb"; then
    start_service "mysql" || warn "Could not start MySQL/MariaDB"
fi

# ──────────────────────────────────────────────
# 5.  Clone / Update Source Code
# ──────────────────────────────────────────────
echo ""
log "Fetching source code from ${REPO_URL} ..."

if echo "${REPO_URL}" | grep -q "GITHUB_USERNAME"; then
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
        cd "${SITE_DIR}" && {
            git config --global --add safe.directory "${SITE_DIR}"
            git pull origin "${REPO_BRANCH}" 2>&1 || warn "git pull failed"
        }
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
mysql -u root -e "DROP DATABASE IF EXISTS \`${DB_NAME}\`;" 2>&1
mysql -u root -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;" 2>&1 || {
    err "Failed to create database — is MySQL running?"
    exit 1
}

# Create application user
# On MariaDB 10.3+, CREATE USER IF NOT EXISTS may leave an empty auth plugin,
# causing PHP's mysql_native_password to fail.  Drop + recreate to force the
# correct plugin.
mysql -u root -e "DROP USER IF EXISTS '${DB_USER}'@'localhost';" 2>&1
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
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $proto = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0]);
} elseif (!empty($_SERVER['REQUEST_SCHEME'])) {
    $proto = $_SERVER['REQUEST_SCHEME'];
}

$host = $_SERVER['HTTP_HOST'];
if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    $host = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_HOST'])[0]);
}

$dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
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
echo "║       DBS401 SQL Injection Playground — Ready!           ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""
echo "  Local access:  http://localhost/task-manager/"
echo "  LAN access:    http://$(hostname -I 2>/dev/null | awk '{print $1}')/task-manager/"
echo "  (SITEURL auto-detects the IP — other machines on your"
echo "   LAN can reach it at the VM's IP shown above.)"
echo ""
echo "  DB:   ${DB_NAME}  |  User: ${DB_USER}  |  Pass: ${DB_PASS}"
echo ""

log "Setup complete."