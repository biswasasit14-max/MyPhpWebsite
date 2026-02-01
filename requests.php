<?php
/**
 * Admin: list and manage guard requests (admin only)
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/guard.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('admin');

$user = current_user();
$pdo = db_connect();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $id = (int) $_POST['request_id'];
    if (isset($_POST['delete'])) {
        $stmt = $pdo->prepare('DELETE FROM guard_requests WHERE id = ?');
        $stmt->execute([$id]);
        $message = 'Request deleted.';
    } elseif (isset($_POST['status'])) {
        $status = $_POST['status'];
        if (in_array($status, ['pending', 'assigned', 'resolved', 'cancelled'], true)) {
            $stmt = $pdo->prepare('UPDATE guard_requests SET status = ?, resolved_at = IF(? = "resolved", NOW(), resolved_at) WHERE id = ?');
            $stmt->execute([$status, $status, $id]);
            $message = 'Request updated.';
        }
    }
}

$stmt = $pdo->query('
    SELECT r.id, r.location, r.message, r.status, r.created_at, r.resolved_at, u.username
    FROM guard_requests r
    JOIN users u ON u.id = r.user_id
    ORDER BY r.created_at DESC
');
$requests = $stmt->fetchAll();

$page_title = 'Guard Requests';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-page">
    <h1>Guard requests</h1>
    <?php if ($message): ?>
        <p class="success"><?= h($message) ?></p>
    <?php endif; ?>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Location</th>
                <th>Message</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
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
                        <form method="post" class="inline-form">
                            <input type="hidden" name="request_id" value="<?= (int) $r['id'] ?>">
                            <select name="status">
                                <option value="pending" <?= $r['status'] === 'pending' ? 'selected' : '' ?>>pending</option>
                                <option value="assigned" <?= $r['status'] === 'assigned' ? 'selected' : '' ?>>assigned</option>
                                <option value="resolved" <?= $r['status'] === 'resolved' ? 'selected' : '' ?>>resolved</option>
                                <option value="cancelled" <?= $r['status'] === 'cancelled' ? 'selected' : '' ?>>cancelled</option>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                        <form method="post" class="inline-form" onsubmit="return confirm('Delete this request?');">
                            <input type="hidden" name="request_id" value="<?= (int) $r['id'] ?>">
                            <input type="hidden" name="delete" value="1">
                            <button type="submit" class="btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($requests)): ?>
                <tr><td colspan="7">No guard requests yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
