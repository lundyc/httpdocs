<?php

declare(strict_types=1);

$sharedConfig = dirname(__DIR__) . '/shared/config/config.local.php';
if (is_file($sharedConfig)) {
    require_once $sharedConfig;
}

$hostEnv = getenv('DB_HOST');
$portEnv = getenv('DB_PORT');
$dbEnv = getenv('DB_NAME');
$userEnv = getenv('DB_USER');
$passEnv = getenv('DB_PASS');
$charsetEnv = getenv('DB_CHARSET');

$host = $hostEnv !== false && $hostEnv !== '' ? $hostEnv : (defined('DB_HOST') ? DB_HOST : 'localhost');
$port = $portEnv !== false && $portEnv !== '' ? (int)$portEnv : 3306;
$database = $dbEnv !== false && $dbEnv !== '' ? $dbEnv : (defined('DB_NAME') ? DB_NAME : 'mch_os');
$username = $userEnv !== false && $userEnv !== '' ? $userEnv : (defined('DB_USER') ? DB_USER : 'myclubhub');
$password = $passEnv !== false && $passEnv !== '' ? $passEnv : (defined('DB_PASS') ? DB_PASS : 'change_this_password');
$charset = $charsetEnv !== false && $charsetEnv !== '' ? $charsetEnv : 'utf8mb4';

return [
    'host' => $host,
    'port' => $port,
    'database' => $database,
    'username' => $username,
    'password' => $password,
    'charset' => $charset,
];
