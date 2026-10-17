<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pageTitle = 'Career Openings';
$activeMenu = 'openings';
$token = csrf_token();
$error = '';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_opening') {
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired — please try again.';
    } else {
        $pdo->prepare('DELETE FROM career_openings WHERE id = ?')->execute([(int) $_POST['id']]);
    }
}

$openings = $pdo->query('SELECT * FROM career_openings ORDER BY sort_order ASC, created_at DESC')->fetchAll();

require __DIR__ . '/partials/header.php';
?>

<?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-header">
    <h2>Current openings</h2>
    <a href="/admin/opening-edit.php" class="btn btn-gold">New opening</a>
  </div>
  <p class="hint" style="margin-top:-8px;">These appear in the "Current Openings" section on the public Careers page. The static overview, FAQ and benefits text is still edited separately under <a href="/admin/careers.php">Careers</a>.</p>

  <?php if (!$openings): ?>
    <div class="empty-state">No openings yet — the Careers page will show a "check back soon" message until you add one.</div>
  <?php else: ?>
    <table class="admin-table">
      <thead><tr><th>Title</th><th>Type</th><th>Status</th><th>Deadline</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($openings as $job): ?>
          <tr>
            <td><?= h($job['title']) ?><?php if ($job['department']): ?><div class="hint"><?= h($job['department']) ?></div><?php endif; ?></td>
            <td><?= h(ucwords(str_replace('-', ' ', $job['employment_type']))) ?></td>
            <td><span class="pill <?= $job['status'] === 'open' ? 'pill-published' : 'pill-draft' ?>"><?= h(ucfirst($job['status'])) ?></span></td>
            <td><?= $job['application_deadline'] ? h(date('M j, Y', strtotime($job['application_deadline']))) : '—' ?></td>
            <td class="row-actions">
              <a href="/admin/opening-edit.php?id=<?= (int) $job['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
              <form method="post" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                <input type="hidden" name="action" value="delete_opening">
                <input type="hidden" name="id" value="<?= (int) $job['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete this opening permanently?">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
