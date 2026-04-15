<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;


final class Connection
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
        }

        return self::$instance;
    }

    private static function createConnection(): PDO
    {
        $host   = self::env('DB_HOST', 'db');
        $port   = self::env('DB_PORT', '3306');
        $dbname = self::env('DB_NAME', 'tecnofit');
        $user   = self::env('DB_USER', 'tecnofit');
        $pass   = self::env('DB_PASS', 'secret');

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // false = prepared statements reais no servidor MySQL, não emulados no PHP
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            return new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Não vazar credenciais/host para o caller
            throw new \RuntimeException('Database connection failed.', 0, $e);
        }
    }

    private static function env(string $key, string $default = ''): string
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}
