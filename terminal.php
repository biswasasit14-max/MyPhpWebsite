<?php
/**
 * Admin Terminal: add, edit, delete guard requests (admin only)
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/guard.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('admin');

$user = current_user();
$pdo = db_connect();

$message = '';
$edit_id = isset($_GET['edit']) ? (int) $_GET['edit'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'], $_POST['request_id'])) {
        $id = (int) $_POST['request_id'];
        $stmt = $pdo->prepare('DELETE FROM guard_requests WHERE id = ?');
        $stmt->execute([$id]);
        $message = 'Request deleted.';
        $edit_id = null;
        header('Location: ' . url('admin/terminal.php') . '?msg=deleted');
        exit;
    }
    if (isset($_POST['add'])) {
        $user_id = (int) ($_POST['user_id'] ?? 0);
        $location = trim($_POST['location'] ?? '') ?: null;
        $msg = trim($_POST['message'] ?? '') ?: null;
        $status = $_POST['status'] ?? 'pending';
        if ($user_id && in_array($status, ['pending', 'assigned', 'resolved', 'cancelled'], true)) {
            $stmt = $pdo->prepare('INSERT INTO guard_requests (user_id, location, message, status) VALUES (?, ?, ?, ?)');
            $stmt->execute([$user_id, $location, $msg, $status]);
            $message = 'Request added.';
            header('Location: ' . url('admin/terminal.php') . '?msg=added');
            exit;
        } else {
            $message = 'Invalid data: select a user and valid status.';
        }
    }
    if (isset($_POST['update'], $_POST['request_id'])) {
        $id = (int) $_POST['request_id'];
        $user_id = (int) ($_POST['user_id'] ?? 0);
        $location = trim($_POST['location'] ?? '') ?: null;
        $msg = trim($_POST['message'] ?? '') ?: null;
        $status = $_POST['status'] ?? 'pending';
        if ($user_id && in_array($status, ['pending', 'assigned', 'resolved', 'cancelled'], true)) {
            $stmt = $pdo->prepare('UPDATE guard_requests SET user_id = ?, location = ?, message = ?, status = ?, resolved_at = IF(? = "resolved", NOW(), resolved_at) WHERE id = ?');
            $stmt->execute([$user_id, $location, $msg, $status, $status, $id]);
            $message = 'Request updated.';
            $edit_id = null;
            header('Location: ' . url('admin/terminal.php') . '?msg=updated');
            exit;
        } else {
            $message = 'Invalid data.';
            $edit_id = $id;
        }
    }
}

$msg = $_GET['msg'] ?? '';
if ($msg === 'added') $message = 'Request added.';
if ($msg === 'updated') $message = 'Request updated.';
if ($msg === 'deleted') $message = 'Request deleted.';

$users = $pdo->query('SELECT id, username FROM users ORDER BY username')->fetchAll();
$stmt = $pdo->query('
    SELECT r.id, r.user_id, r.location, r.message, r.status, r.created_at, r.resolved_at, u.username
    FROM guard_requests r
    JOIN users u ON u.id = r.user_id
    ORDER BY r.created_at DESC
');
$requests = $stmt->fetchAll();

$editing = null;
if ($edit_id) {
    foreach ($requests as $r) {
        if ((int) $r['id'] === $edit_id) { $editing = $r; break; }
    }
}

$page_title = 'Terminal';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-page terminal-page">
    <h1>Terminal</h1>
    <p class="muted">Add, edit, or delete guard requests.</p>
    <?php if ($message): ?>
        <p class="success"><?= h($message) ?></p>
    <?php endif; ?>

    <section class="card terminal-add">
        <h2><?= $editing ? 'Edit request' : 'Add request' ?></h2>
        <form method="post" class="form-card">
            <?php if ($editing): ?>
                <input type="hidden" name="request_id" value="<?= (int) $editing['id'] ?>">
                <input type="hidden" name="update" value="1">
            <?php else: ?>
                <input type="hidden" name="add" value="1">
            <?php endif; ?>
            <label>User
                <select name="user_id" required>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= (int) $u['id'] ?>" <?= ($editing && (int) $editing['user_id'] === (int) $u['id']) ? 'selected' : '' ?>><?= h($u['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Location <input type="text" name="location" value="<?= $editing ? h($editing['location'] ?? '') : '' ?>" placeholder="e.g. Building A"></label>
            <label>Message <textarea name="message" rows="2" placeholder="Optional"><?= $editing ? h($editing['message'] ?? '') : '' ?></textarea></label>
            <label>Status
                <select name="status">
                    <?php foreach (['pending', 'assigned', 'resolved', 'cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($editing && ($editing['status'] ?? '') === $s) ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit"><?= $editing ? 'Update' : 'Add request' ?></button>
            <?php if ($editing): ?>
                <a href="<?= url('admin/terminal.php') ?>" class="btn-cancel">Cancel</a>
            <?php endif; ?>
        </form>
    </section>

    <section class="card">
        <h2>All requests</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Location</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                    <tr>
                        <td><?= (int) $r['id'] ?></td>
                        <td><?= h($r['username']) ?></td>
                        <td><?= h($r['location'] ?? '—') ?></td>
                        <td><?= h($r['message'] ?? '—') ?></td>
                        <td><span class="status status-<?= h($r['status']) ?>"><?= h($r['status']) ?></span></td>
                        <td><?= h($r['created_at']) ?></td>
                        <td>
                            <a href="<?= url('admin/terminal.php') ?>?edit=<?= (int) $r['id'] ?>" class="btn-edit">Edit</a>
                            <form method="post" class="inline-form" onsubmit="return confirm('Delete this request?');">
                                <input type="hidden" name="request_id" value="<?= (int) $r['id'] ?>">
                                <input type="hidden" name="delete" value="1">
                                <button type="submit" class="btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($requests)): ?>
                    <tr><td colspan="7">No guard requests.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
