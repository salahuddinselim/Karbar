<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /login.php');
    exit;
}
csrf_verify();

$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$password = (string) ($_POST['password'] ?? '');
$old = ['email' => $email];

if ($email === '' || $password === '') {
    redirect_with_error('/login.php', 'Email and password are required.', $old);
}

// Basic brute-force throttle: max 8 attempts per 5 minutes per session.
$_SESSION['login_attempts'] ??= [];
$_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'], fn($t) => $t > time() - 300);
if (count($_SESSION['login_attempts']) >= 8) {
    redirect_with_error('/login.php', 'Too many attempts. Please wait a few minutes and try again.', $old);
}

$stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    $_SESSION['login_attempts'][] = time();
    redirect_with_error('/login.php', 'Invalid email or password.', $old);
}

unset($_SESSION['login_attempts']);
login_user($user);
header('Location: /dashboard.php');
exit;
