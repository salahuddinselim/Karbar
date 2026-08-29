<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

$owner = require_login();
require_owner($owner);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /staff.php'); exit; }
csrf_verify();

$id = (string) ($_POST['id'] ?? '');
$stmt = db()->prepare('SELECT role FROM users WHERE id = ?');
$stmt->execute([$id]);
$target = $stmt->fetch();

if ($target && $target['role'] !== 'owner') {
    $stmt = db()->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$id]);
    flash_set('success', 'Staff account removed.');
}

header('Location: /staff.php');
exit;
