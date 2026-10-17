<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pageTitle = 'Posts / News';
$activeMenu = 'posts';
$token = csrf_token();
$error = '';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_post') {
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired — please try again.';
    } else {
        $pdo->prepare('DELETE FROM posts WHERE id = ?')->execute([(int) $_POST['id']]);
    }
}

$posts = $pdo->query('SELECT * FROM posts ORDER BY created_at DESC')->fetchAll();

require __DIR__ . '/partials/header.php';
?>

<?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-header">
    <h2>All posts</h2>
    <a href="/admin/post-edit.php" class="btn btn-gold">New post</a>
  </div>

  <?php if (!$posts): ?>
    <div class="empty-state">No posts yet — create your first one.</div>
  <?php else: ?>
    <table class="admin-table">
      <thead><tr><th>Title</th><th>Status</th><th>Published</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($posts as $post): ?>
          <tr>
            <td><?= h($post['title']) ?></td>
            <td><span class="pill <?= $post['status'] === 'published' ? 'pill-published' : 'pill-draft' ?>"><?= h(ucfirst($post['status'])) ?></span></td>
            <td><?= $post['published_at'] ? h(date('M j, Y', strtotime($post['published_at']))) : '—' ?></td>
            <td class="row-actions">
              <a href="/admin/post-edit.php?id=<?= (int) $post['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
              <form method="post" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                <input type="hidden" name="action" value="delete_post">
                <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete this post permanently?">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
