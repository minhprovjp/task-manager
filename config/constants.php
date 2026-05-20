<?php
session_start();
define('LOCALHOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'task_manager');
ini_set('mysqli.default_socket', '/tmp/mysql-run/mysql.sock');
define('SITEURL', 'http://localhost:8080/');
