<?php
session_start();

define('LOCALHOST', 'localhost');
define('DB_USERNAME', 'taskmgr_user');
define('DB_PASSWORD', 'CHANGE_ME_DB_PASSWORD');
define('DB_NAME', 'task_manager');

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
