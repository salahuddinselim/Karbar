<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/flash.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/permissions.php';
require_once __DIR__ . '/ledger.php';
require_once __DIR__ . '/functions.php';

// Baseline security headers for every response.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; script-src 'self' https://cdn.tailwindcss.com 'unsafe-inline'; font-src 'self' data:");
if (APP_HTTPS) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
