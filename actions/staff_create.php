<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

$owner = require_login();
require_owner($owner);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /staff.php'); exit; }
csrf_verify();

$name = trim((string) ($_POST['name'] ?? ''));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$password = (string) ($_POST['password'] ?? '');
$old = ['name' => $name, 'email' => $email];

if ($name === '' || $email === '' || $password === '') {
    redirect_with_error('/staff_form.php', 'Name, email, and password are required.', $old);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_error('/staff_form.php', 'Enter a valid email address.', $old);
}
if (strlen($password) < 6) {
    redirect_with_error('/staff_form.php', 'Password must be at least 6 characters.', $old);
}

$stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    redirect_with_error('/staff_form.php', 'A user with that email already exists.', $old);
}

$passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

$stmt = db()->prepare(
    'INSERT INTO users (id, name, email, password_hash, role, can_manage_products, can_manage_parties, can_record_transactions, can_view_reports)
     VALUES (?, ?, ?, ?, "staff", ?, ?, ?, ?)'
);
$stmt->execute([
    uuid(), $name, $email, $passwordHash,
    isset($_POST['canManageProducts']) ? 1 : 0,
    isset($_POST['canManageParties']) ? 1 : 0,
    isset($_POST['canRecordTransactions']) ? 1 : 0,
    isset($_POST['canViewReports']) ? 1 : 0,
]);

flash_set('success', 'Staff account created.');
header('Location: /staff.php');
exit;
