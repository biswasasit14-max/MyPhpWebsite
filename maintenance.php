<?php
/**
 * Maintenance: change user passwords (admin only)
 * Shown only when logged in as admin.
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/guard.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('admin');

$user = current_user();
$pdo = db_connect();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['new_password'], $_POST['confirm_password'])) {
    $target_id = (int) $_POST['user_id'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new === '' || strlen($new) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE id = ?');
        $stmt->execute([$target_id]);
        if ($stmt->fetch()) {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $up = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $up->execute([$hash, $target_id]);
            $message = 'Password updated.';
        } else {
            $error = 'User not found.';
        }
    }
}

$users = $pdo->query('SELECT id, username, email, role FROM users ORDER BY username')->fetchAll();

$page_title = 'Maintenance';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-page maintenance-page">
    <h1>Maintenance</h1>
    <p class="muted">Change user passwords. Admin only.</p>
    <?php if ($message): ?>
        <p class="success"><?= h($message) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?= h($error) ?></p>
    <?php endif; ?>

    <section class="card">
        <h2>Change password</h2>
        <form method="post" class="form-card">
            <label>User
                <select name="user_id" required>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= (int) $u['id'] ?>"><?= h($u['username']) ?> (<?= h($u['role']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>New password <input type="password" name="new_password" required minlength="6" autocomplete="new-password"></label>
            <label>Confirm password <input type="password" name="confirm_password" required minlength="6" autocomplete="new-password"></label>
            <button type="submit">Update password</button>
        </form>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
