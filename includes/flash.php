<?php
declare(strict_types=1);

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/** Stores validation errors + submitted input, then redirects back to the given form page. */
function redirect_with_error(string $location, string $error, array $old = []): never
{
    flash_set('error', $error);
    $_SESSION['old_input'] = $old;
    header('Location: ' . $location);
    exit;
}

function old_input(string $key, string $default = ''): string
{
    $old = $_SESSION['old_input'][$key] ?? $default;
    return (string) $old;
}

function clear_old_input(): void
{
    unset($_SESSION['old_input']);
}
