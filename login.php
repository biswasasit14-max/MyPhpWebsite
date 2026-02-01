<?php
/**
 * Login page
 */
session_start();
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/guard.php';

if (is_logged_in()) {
    redirect(url('dashboard.php'));
}

$error = '';
$redirect_after = $_GET['redirect'] ?? url('dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } else {
        $pdo = db_connect();
        $stmt = $pdo->prepare('SELECT id, username, email, password_hash, role FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id']   = (int) $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['role']      = $user['role'];
            redirect($redirect_after);
        }
        $error = 'Invalid username or password.';
    }
}

$page_title = 'Login';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-box">
    <h1>Login</h1>
    <?php if ($error): ?>
        <p class="error"><?= h($error) ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <input type="hidden" name="redirect" value="<?= h($redirect_after) ?>">
        <label>Username <input type="text" name="username" required autofocus value="<?= h($_POST['username'] ?? '') ?>"></label>
        <label>Password <input type="password" name="password" required></label>
        <button type="submit">Login</button>
    </form>
    <p class="auth-hint">Default users: admin, guard1, user1 — password: <code>password</code></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
