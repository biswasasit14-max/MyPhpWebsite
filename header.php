<?php
require_once __DIR__ . '/../config/app.php';
$current_user = function_exists('current_user') ? current_user() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? h($page_title) . ' - ' : '' ?>Website</title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
    <header class="site-header">
        <nav class="nav">
            <a href="<?= url('index.php') ?>" class="nav-brand">Website</a>
            <?php if ($current_user): ?>
                <a href="<?= url('dashboard.php') ?>">Dashboard</a>
                <a href="<?= url('call-guard.php') ?>">Call Guard</a>
                <?php if (($current_user['role'] ?? '') === 'admin'): ?>
                    <a href="<?= url('admin/requests.php') ?>">Guard Requests</a>
                    <a href="<?= url('admin/terminal.php') ?>">Terminal</a>
                    <a href="<?= url('admin/maintenance.php') ?>">Maintenance</a>
                <?php endif; ?>
                <span class="nav-user"><?= h($current_user['username']) ?> (<?= h($current_user['role']) ?>)</span>
                <a href="<?= url('logout.php') ?>" class="nav-logout">Logout</a>
            <?php else: ?>
                <a href="<?= url('login.php') ?>">Login</a>
            <?php endif; ?>
        </nav>
    </header>
    <main class="main">
