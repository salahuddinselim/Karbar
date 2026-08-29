<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /setup.php');
    exit;
}
csrf_verify();

$existingCount = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($existingCount > 0) {
    redirect_with_error('/login.php', 'Setup already completed.');
}

$storeName = trim((string) ($_POST['storeName'] ?? ''));
$category = trim((string) ($_POST['category'] ?? '')) ?: null;
$name = trim((string) ($_POST['name'] ?? ''));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$password = (string) ($_POST['password'] ?? '');

$old = ['storeName' => $storeName, 'category' => $category ?? '', 'name' => $name, 'email' => $email];

if ($storeName === '' || $name === '' || $email === '' || $password === '') {
    redirect_with_error('/setup.php', 'All fields are required.', $old);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_error('/setup.php', 'Enter a valid email address.', $old);
}
if (strlen($password) < 6) {
    redirect_with_error('/setup.php', 'Password must be at least 6 characters.', $old);
}

$passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
$userId = uuid();

$pdo = db();
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare(
        'INSERT INTO users (id, name, email, password_hash, role) VALUES (?, ?, ?, ?, "owner")'
    );
    $stmt->execute([$userId, $name, $email, $passwordHash]);

    $stmt = $pdo->prepare(
        "INSERT INTO store_settings (id, store_name, category) VALUES ('singleton', ?, ?)
         ON DUPLICATE KEY UPDATE store_name = VALUES(store_name), category = VALUES(category)"
    );
    $stmt->execute([$storeName, $category]);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('Setup failed: ' . $e->getMessage());
    redirect_with_error('/setup.php', 'Could not complete setup. Please try again.', $old);
}

login_user(['id' => $userId]);
header('Location: /dashboard.php');
exit;
