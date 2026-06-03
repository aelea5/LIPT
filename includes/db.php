<?php
/**
 * PDO database connection for Lunch in the Park.
 * Credentials are read from .env in the project root (never commit .env).
 */

require_once __DIR__ . '/env.php';

/**
 * @return PDO Shared connection instance.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = env('DB_HOST');
    $name = env('DB_NAME');
    $user = env('DB_USER');
    $pass = env('DB_PASS');

    if ($host === null || $name === null || $user === null || $pass === null) {
        throw new RuntimeException(
            'Database configuration missing. Copy .env.example to .env and set DB_HOST, DB_NAME, DB_USER, and DB_PASS.'
        );
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $host,
        $name
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        throw new RuntimeException('Unable to connect to the database.', 0, $e);
    }

    return $pdo;
}
