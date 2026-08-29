<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

const SESSION_MAX_IDLE_SECONDS = 60 * 60 * 24 * 30; // 30 days, matches the cookie lifetime

function login_user(array $user): void
{
    // Prevent session fixation: rotate the session id on privilege change.
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['last_activity'] = time();
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

/** Returns the logged-in user's full row, or null if not authenticated / session expired. */
function current_user(): ?array
{
    static $cached = false;
    static $user = null;
    if ($cached) {
        return $user;
    }
    $cached = true;

    if (empty($_SESSION['user_id'])) {
        return null;
    }
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_MAX_IDLE_SECONDS) {
        logout_user();
        return null;
    }
    $_SESSION['last_activity'] = time();

    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch();
    $user = $row ?: null;
    return $user;
}

/** Redirects to /login.php if not authenticated. Call at the top of every protected page. */
function require_login(): array
{
    $user = current_user();
    if (!$user) {
        header('Location: /login.php');
        exit;
    }
    return $user;
}
