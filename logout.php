<?php
/**
 * Logout
 */
session_start();
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/guard.php';

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

header('Location: ' . url('index.php'));
exit;
