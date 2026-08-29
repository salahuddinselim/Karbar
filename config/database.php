<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $host = env('DB_HOST', '127.0.0.1');
    $port = env('DB_PORT', '3306');
    $name = env('DB_NAME', 'karbar');
    $user = env('DB_USER', 'root');
    $pass = env('DB_PASS', '');

    $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false, // real prepared statements, not string interpolation
        ]);
    } catch (PDOException $e) {
        error_log('DB connection failed: ' . $e->getMessage());
        http_response_code(500);
        die('Database connection failed. Please try again later.');
    }

    return $pdo;
}

function uuid(): string
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
