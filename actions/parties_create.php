<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

require_permission_for_action('parties');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /parties.php'); exit; }
csrf_verify();

$name = trim((string) ($_POST['name'] ?? ''));
$type = (string) ($_POST['type'] ?? 'CUSTOMER');
$old = $_POST;

if ($name === '') {
    redirect_with_error('/party_form.php', 'Name is required.', $old);
}
if (!in_array($type, ['CUSTOMER', 'SUPPLIER'], true)) {
    redirect_with_error('/party_form.php', 'Invalid party type.', $old);
}

$openingBalanceRaw = trim((string) ($_POST['openingBalance'] ?? '0'));
$openingBalance = is_numeric($openingBalanceRaw) ? (float) $openingBalanceRaw : 0.0;

$stmt = db()->prepare('INSERT INTO parties (id, name, type, phone, opening_balance) VALUES (?, ?, ?, ?, ?)');
$stmt->execute([
    uuid(),
    $name,
    $type,
    trim((string) ($_POST['phone'] ?? '')) ?: null,
    $openingBalance,
]);

flash_set('success', 'Party added.');
header('Location: /parties.php');
exit;
