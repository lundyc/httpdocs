<?php

declare(strict_types=1);

use MyClubHub\Controllers\HealthController;
use MyClubHub\Controllers\HomeController;
use MyClubHub\Core\Database;
use MyClubHub\Core\Request;
use MyClubHub\Core\Router;

if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(__DIR__));
}

error_reporting(E_ALL);
ini_set('display_errors', '0');

spl_autoload_register(static function (string $class): void {
    $prefix = 'MyClubHub\\';
    $baseDir = PROJECT_ROOT . '/src/';
    $len = strlen($prefix);

    if (strncmp($class, $prefix, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

$appConfig = require PROJECT_ROOT . '/config/app.php';
$databaseConfig = require PROJECT_ROOT . '/config/database.php';
Database::configure($databaseConfig);

$request = new Request($_SERVER, $_GET, $_POST);
$router = new Router();
$homeController = new HomeController($appConfig);
$healthController = new HealthController();

$router->get('/', [$homeController, 'index']);
$router->get('/login', [$homeController, 'login']);
$router->get('/admin', [$homeController, 'admin']);
$router->get('/health', [$healthController, 'index']);

$response = $router->dispatch($request);
$response->send();
