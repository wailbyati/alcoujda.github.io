<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pageTitle = 'Registrations';
$activeMenu = 'registrations';
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
            $pdo->prepare('UPDATE registrations SET is_read = 1 WHERE id = ?')->execute([$id]);
        } elseif ($action === 'delete') {
            $pdo->prepare('DELETE FROM registrations WHERE id = ?')->execute([$id]);
        }
    }
}

$registrations = $pdo->query('SELECT * FROM registrations ORDER BY created_at DESC')->fetchAll();

require __DIR__ . '/partials/header.php';
?>

<?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-header"><h2>Registration inquiries</h2></div>

  <?php if (!$registrations): ?>
    <div class="empty-state">No registrations yet.</div>
  <?php else: ?>
    <table class="admin-table">
      <thead><tr><th>Name</th><th>Contact</th><th>Program</th><th>Notes</th><th>Received</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($registrations as $r): ?>
          <tr>
            <td>
              <strong><?= h($r['full_name']) ?></strong>
              <?php if (!$r['is_read']): ?><br><span class="pill pill-unread">New</span><?php endif; ?>
            </td>
            <td>
              <a href="mailto:<?= h($r['email']) ?>"><?= h($r['email']) ?></a>
              <?php if ($r['phone']): ?><br><?= h($r['phone']) ?><?php endif; ?>
            </td>
            <td><?= h($r['program_interest'] ?: '—') ?></td>
            <td style="max-width:260px; white-space:pre-wrap;"><?= h($r['notes'] ?: '—') ?></td>
            <td><?= h(date('M j, Y g:ia', strtotime($r['created_at']))) ?></td>
            <td class="row-actions">
              <?php if (!$r['is_read']): ?>
                <form method="post">
                  <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                  <input type="hidden" name="action" value="mark_read">
                  <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                  <button type="submit" class="btn btn-ghost btn-sm">Mark read</button>
                </form>
              <?php endif; ?>
              <form method="post">
                <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete this registration?">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
