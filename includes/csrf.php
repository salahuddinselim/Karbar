<?php
declare(strict_types=1);

/** Returns the current CSRF token, creating one for this session if needed. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Emits a hidden <input> carrying the CSRF token — include inside every state-changing <form>. */
function csrf_field(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

/** Verifies the token from a POST request; kills the request with 403 on mismatch. */
function csrf_verify(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    $expected = $_SESSION['csrf_token'] ?? '';
    if ($expected === '' || !hash_equals($expected, $submitted)) {
        http_response_code(403);
        die('Invalid or expired form submission. Please go back and try again.');
    }
}
