<?php
/**
 * Home / redirect
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/guard.php';

if (is_logged_in()) {
    header('Location: ' . url('dashboard.php'));
} else {
    header('Location: ' . url('login.php'));
}
exit;
