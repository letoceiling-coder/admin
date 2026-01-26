<?php
// КРИТИЧЕСКАЯ ДИАГНОСТИКА: Проверка БД через HTTP
// Доступ: http://admin.loc/debug_db.php

// Проверка переменных окружения
$envVars = [
    'DB_DATABASE (getenv)' => getenv('DB_DATABASE'),
    'DB_DATABASE ($_ENV)' => $_ENV['DB_DATABASE'] ?? 'not set',
    'DB_DATABASE ($_SERVER)' => $_SERVER['DB_DATABASE'] ?? 'not set',
];

// Проверка через Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$laravelDb = config('database.connections.mysql.database');
$defaultConnection = config('database.default');

// Проверка реального подключения
try {
    $pdo = DB::connection()->getPdo();
    $stmt = $pdo->query('SELECT DATABASE()');
    $currentDb = $stmt->fetchColumn();
} catch (\Exception $e) {
    $currentDb = 'ERROR: ' . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode([
    'project' => 'ADMIN',
    'expected_db' => 'laravel_admin',
    'environment_variables' => $envVars,
    'laravel_config' => [
        'default_connection' => $defaultConnection,
        'db_database' => $laravelDb,
    ],
    'actual_db_connection' => $currentDb,
    'problem' => $currentDb !== 'laravel_admin' ? 'БД НЕ СОВПАДАЕТ!' : null,
    'env_file_check' => [
        'exists' => file_exists(__DIR__ . '/.env'),
        'readable' => is_readable(__DIR__ . '/.env'),
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
