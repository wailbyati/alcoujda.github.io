<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

function start_session(): void
{
    bootstrap_session();
}

function current_admin(): ?array
{
    start_session();
    return $_SESSION['admin'] ?? null;
}

function require_login(): array
{
    $admin = current_admin();
    if (!$admin) {
        header('Location: /admin/login.php');
        exit;
    }
    return $admin;
}

function attempt_login(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM admin_users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    start_session();
    session_regenerate_id(true);
    $_SESSION['admin'] = [
        'id'           => $user['id'],
        'username'     => $user['username'],
        'display_name' => $user['display_name'],
    ];
    return true;
}

function logout(): void
{
    start_session();
    $_SESSION = [];
    session_destroy();
}
