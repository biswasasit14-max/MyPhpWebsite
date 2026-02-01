<?php
/**
 * Protected dashboard
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/guard.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$user = current_user();

$page_title = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="dashboard">
    <h1>Dashboard</h1>
    <p>Welcome, <strong><?= h($user['username']) ?></strong> (<?= h($user['role']) ?>).</p>

    <section class="card">
        <h2>Quick actions</h2>
        <ul>
            <li><a href="<?= url('call-guard.php') ?>">Request guard assistance</a></li>
            <?php if ($user['role'] === 'admin'): ?>
                <li><a href="<?= url('admin/requests.php') ?>">View guard requests</a></li>
            <?php endif; ?>
        </ul>
    </section>

    <section class="card">
        <h2>Account</h2>
        <p>Email: <?= h($user['email']) ?></p>
        <p>Role: <?= h($user['role']) ?></p>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
