<?php
session_start();

define('LOCALHOST', 'localhost');
define('DB_USERNAME', 'taskmgr_user');
define('DB_PASSWORD', 'CHANGE_ME_DB_PASSWORD');
define('DB_NAME', 'task_manager');

$proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host  = $_SERVER['HTTP_HOST'];
$dir   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
define('SITEURL', "$proto://$host$dir/");

// Require login for all pages except login.php
if (!isset($_SESSION['user']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header("Location: ".SITEURL."login.php");
    exit;
}
