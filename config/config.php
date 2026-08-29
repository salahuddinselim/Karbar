<?php
declare(strict_types=1);

// Loads key=value pairs from .env into environment variables without any
// external dependency. Silently does nothing if the file is missing (values
// can then come from real server env vars instead, e.g. in production).
function load_env(string $path): void
{
    if (!is_file($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

load_env(__DIR__ . '/../.env');

function env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

define('APP_HTTPS', env('APP_HTTPS', '0') === '1');
define('APP_SECRET', env('APP_SECRET', 'dev-only-secret-change-me'));
define('UPLOAD_DIR', __DIR__ . '/../uploads/receipts');
define('UPLOAD_URL_PREFIX', '/uploads/receipts');
define('MAX_RECEIPT_BYTES', 5 * 1024 * 1024);

// Hardened session cookie, must run before session_start().
session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * 30,
    'path' => '/',
    'secure' => APP_HTTPS,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

error_reporting(E_ALL);
ini_set('display_errors', '0'); // never leak stack traces to users
ini_set('log_errors', '1');
