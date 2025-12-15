<?php

declare(strict_types=1);

namespace MyClubHub\Core;

use PDO;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    /**
     * @var array<string, mixed>
     */
    private static array $config = [];

    private function __construct()
    {
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function configure(array $config): void
    {
        self::$config = $config;
        self::$connection = null;
    }

    public static function connect(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        if (self::$config === []) {
            throw new RuntimeException('Database configuration has not been provided.');
        }

        $host = (string)(self::$config['host'] ?? 'localhost');
        $port = (int)(self::$config['port'] ?? 3306);
        $database = (string)(self::$config['database'] ?? '');
        $charset = (string)(self::$config['charset'] ?? 'utf8mb4');
        $username = (string)(self::$config['username'] ?? '');
        $password = (string)(self::$config['password'] ?? '');

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $database, $charset);

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        self::$connection = new PDO($dsn, $username, $password, $options);

        return self::$connection;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getConfig(): array
    {
        return self::$config;
    }
}
