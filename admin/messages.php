<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pageTitle = 'Messages';
$activeMenu = 'messages';
$token = csrf_token();
$error = '';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired — please try again.';
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';
        if ($action === 'mark_read') {
            $pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([$id]);
        } elseif ($action === 'delete') {
            $pdo->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
        }
    }
}

$messages = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC')->fetchAll();

require __DIR__ . '/partials/header.php';
?>

<?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-header"><h2>Contact form messages</h2></div>

  <?php if (!$messages): ?>
    <div class="empty-state">No messages yet.</div>
  <?php else: ?>
    <table class="admin-table">
      <thead><tr><th>From</th><th>Message</th><th>Received</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($messages as $m): ?>
          <tr>
            <td>
              <strong><?= h($m['name']) ?></strong><br>
              <a href="mailto:<?= h($m['email']) ?>"><?= h($m['email']) ?></a>
              <?php if (!$m['is_read']): ?><br><span class="pill pill-unread">New</span><?php endif; ?>
            </td>
            <td style="max-width:340px; white-space:pre-wrap;"><?= h($m['message']) ?></td>
            <td><?= h(date('M j, Y g:ia', strtotime($m['created_at']))) ?></td>
            <td class="row-actions">
              <?php if (!$m['is_read']): ?>
                <form method="post">
                  <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                  <input type="hidden" name="action" value="mark_read">
                  <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                  <button type="submit" class="btn btn-ghost btn-sm">Mark read</button>
                </form>
              <?php endif; ?>
              <form method="post">
                <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete this message?">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
