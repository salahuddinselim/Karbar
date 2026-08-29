<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
}
logout_user();
header('Location: /login.php');
exit;
