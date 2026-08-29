<?php
declare(strict_types=1);

/**
 * Checks whether $user can access $module. Owners always have full access;
 * staff access is gated by their per-module boolean flags. Staff can never
 * manage other staff, regardless of flags.
 */
function can_access(array $user, string $module): bool
{
    if ($user['role'] === 'owner') {
        return true;
    }
    return match ($module) {
        'staff' => false,
        'products' => (bool) $user['can_manage_products'],
        'parties' => (bool) $user['can_manage_parties'],
        'transactions' => (bool) $user['can_record_transactions'],
        'reports' => (bool) $user['can_view_reports'],
        default => false,
    };
}

/** For pages: requires login AND the given permission, else redirects home. */
function require_permission_for_page(string $module): array
{
    $user = require_login();
    if (!can_access($user, $module)) {
        header('Location: /dashboard.php');
        exit;
    }
    return $user;
}

/** For POST action handlers: requires login AND the given permission, else 403s. */
function require_permission_for_action(string $module): array
{
    $user = require_login();
    if (!can_access($user, $module)) {
        http_response_code(403);
        die('You do not have permission to perform this action.');
    }
    return $user;
}

function require_owner(array $user): void
{
    if ($user['role'] !== 'owner') {
        http_response_code(403);
        die('Only the store owner can manage staff.');
    }
}
