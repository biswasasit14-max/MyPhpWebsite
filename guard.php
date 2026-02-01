<?php
/**
 * Auth guard: require logged-in user for protected pages.
 * Include this at the top of any protected page.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        require_once __DIR__ . '/../config/app.php';
        $redirect = url('login.php') . '?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '');
        header('Location: ' . $redirect);
        exit;
    }
}

function require_role(string ...$roles): void {
    require_login();
    if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Access denied.';
        exit;
    }
}

function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'       => (int) $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'email'    => $_SESSION['email'] ?? '',
        'role'     => $_SESSION['role'] ?? 'user',
    ];
}
