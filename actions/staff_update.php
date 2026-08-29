<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

$owner = require_login();
require_owner($owner);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /staff.php'); exit; }
csrf_verify();

$id = (string) ($_POST['id'] ?? '');
$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$target = $stmt->fetch();

if ($target && $target['role'] !== 'owner') {
    $stmt = db()->prepare(
        'UPDATE users SET can_manage_products=?, can_manage_parties=?, can_record_transactions=?, can_view_reports=? WHERE id=?'
    );
    $stmt->execute([
        isset($_POST['can_manage_products']) ? 1 : 0,
        isset($_POST['can_manage_parties']) ? 1 : 0,
        isset($_POST['can_record_transactions']) ? 1 : 0,
        isset($_POST['can_view_reports']) ? 1 : 0,
        $id,
    ]);
    flash_set('success', 'Permissions updated.');
}

header('Location: /staff.php');
exit;
