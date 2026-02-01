<?php
/**
 * Call guard — protected page to request guard assistance
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/guard.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$user = current_user();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location = trim($_POST['location'] ?? '');
    $msg = trim($_POST['message'] ?? '');

    if ($location === '' && $msg === '') {
        $error = 'Please provide a location or message.';
    } else {
        $pdo = db_connect();
        $stmt = $pdo->prepare('INSERT INTO guard_requests (user_id, location, message, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([$user['id'], $location ?: null, $msg ?: null, 'pending']);
        $message = 'Guard request submitted. A guard will be notified.';
    }
}

$page_title = 'Call Guard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-content">
    <h1>Call Guard</h1>
    <p>Use this form to request guard assistance. Provide your location and/or a short message.</p>

    <?php if ($message): ?>
        <p class="success"><?= h($message) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?= h($error) ?></p>
    <?php endif; ?>

    <form method="post" class="form-card">
        <label>Location (optional) <input type="text" name="location" placeholder="e.g. Building A, Room 101" value="<?= h($_POST['location'] ?? '') ?>"></label>
        <label>Message (optional) <textarea name="message" rows="3" placeholder="Describe the situation..."><?= h($_POST['message'] ?? '') ?></textarea></label>
        <button type="submit">Submit request</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
