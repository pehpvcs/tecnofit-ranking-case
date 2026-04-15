<?php

declare(strict_types=1);

$envFile = dirname(__DIR__) . '/.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);

        if (!isset($_ENV[$key]) && getenv($key) === false) {
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

// Autoloader PSR-4 manual: App\Service\RankingService → src/Service/RankingService.php
spl_autoload_register(function (string $fullyQualifiedClassName): void {
    $namespacePrefix = 'App\\';
    $baseDirectory   = __DIR__ . '/';

    if (!str_starts_with($fullyQualifiedClassName, $namespacePrefix)) {
        return;
    }

    $relativeClass = substr($fullyQualifiedClassName, strlen($namespacePrefix));
    $filePath      = $baseDirectory . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($filePath)) {
        require $filePath;
    }
});

$isProduction = ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production') === 'production';

ini_set('display_errors', $isProduction ? '0' : '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);
