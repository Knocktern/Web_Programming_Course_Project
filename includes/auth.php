<?php

declare(strict_types=1);

function current_user(): ?array
{
    static $user = false;
    if ($user !== false) {
        return $user;
    }
    if (empty($_SESSION['user_id'])) {
        return $user = null;
    }

    $statement = database()->prepare('SELECT id, email, role, is_active FROM users WHERE id = ?');
    $statement->execute([$_SESSION['user_id']]);
    $user = $statement->fetch() ?: null;
    if (!$user || !$user['is_active']) {
        session_unset();
        session_destroy();
        return $user = null;
    }
    return $user;
}

function require_login(?string $role = null): array
{
    $user = current_user();
    if (!$user || ($role !== null && $user['role'] !== $role)) {
        flash('error', 'Please sign in with an authorized account.');
        redirect('../public/login.php');
    }
    return $user;
}

function dashboard_path(string $role): string
{
    return match ($role) {
        'admin' => '../admin/dashboard.php',
        'employer' => '../employer/dashboard.php',
        default => '../seeker/dashboard.php',
    };
}
