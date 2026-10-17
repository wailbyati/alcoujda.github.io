<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_login();

$pageTitle = 'Network of Centers';
$activeMenu = 'network';
$token = csrf_token();
$success = '';
$error = '';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired — please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        try {
            if ($action === 'add_center') {
                $imagePath = handle_image_upload($_FILES['image'] ?? null);
                if (!$imagePath) {
                    throw new RuntimeException('Please choose a photo for this center.');
                }
                $nextOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order),0)+1 n FROM network_centers')->fetch()['n'];
                $stmt = $pdo->prepare('INSERT INTO network_centers (name, image_path, website_url, sort_order) VALUES (?, ?, ?, ?)');
                $stmt->execute([trim($_POST['name'] ?? ''), $imagePath, trim($_POST['website_url'] ?? ''), $nextOrder]);
                $success = 'Center added.';

            } elseif ($action === 'update_center') {
                $id = (int) $_POST['id'];
                $url = trim($_POST['website_url'] ?? '');
                $imagePath = handle_image_upload($_FILES['image'] ?? null);
                if ($imagePath) {
                    $pdo->prepare('UPDATE network_centers SET name=?, image_path=?, website_url=? WHERE id=?')
                        ->execute([trim($_POST['name'] ?? ''), $imagePath, $url, $id]);
                } else {
                    $pdo->prepare('UPDATE network_centers SET name=?, website_url=? WHERE id=?')
                        ->execute([trim($_POST['name'] ?? ''), $url, $id]);
                }
                $success = 'Center updated.';

            } elseif ($action === 'center_move_up' || $action === 'center_move_down') {
                move_row('network_centers', (int) ($_POST['id'] ?? 0), $action === 'center_move_up' ? -1 : 1);
                $success = 'Order updated.';

            } elseif ($action === 'delete_center') {
                $pdo->prepare('DELETE FROM network_centers WHERE id=?')->execute([(int) $_POST['id']]);
                $success = 'Center removed.';
            }
        } catch (Throwable $e) {
            $error = 'Could not save: ' . $e->getMessage();
        }
    }
}

$centers = $pdo->query('SELECT * FROM network_centers ORDER BY sort_order ASC')->fetchAll();

require __DIR__ . '/partials/header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-header">
    <h2>ALC network cities</h2>
    <a href="/about.php" target="_blank" class="btn btn-ghost btn-sm">View on About page ↗</a>
  </div>

  <div class="grid-table">
    <div class="grid-table-head" style="grid-template-columns: 90px 2fr 1.3fr 1.2fr auto;">
      <div>Photo</div><div>City name</div><div>Website (optional)</div><div>Replace photo</div><div></div>
    </div>
    <?php foreach ($centers as $center): ?>
      <form method="post" enctype="multipart/form-data" class="grid-table-row" style="grid-template-columns: 90px 2fr 1.3fr 1.2fr auto;">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <input type="hidden" name="action" value="update_center">
        <input type="hidden" name="id" value="<?= (int) $center['id'] ?>">
        <div class="preview" id="preview-center-<?= (int) $center['id'] ?>" style="width:60px; height:60px;">
          <img src="<?= h(asset($center['image_path'])) ?>" alt="">
        </div>
        <input type="text" name="name" value="<?= h($center['name']) ?>">
        <input type="text" name="website_url" value="<?= h($center['website_url'] ?? '') ?>" placeholder="https://…">
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" data-preview="preview-center-<?= (int) $center['id'] ?>">
        <div class="row-actions">
          <button type="submit" class="btn btn-ghost btn-sm" name="action" value="center_move_up"
                  formnovalidate title="Move up">↑</button>
          <button type="submit" class="btn btn-ghost btn-sm" name="action" value="center_move_down"
                  formnovalidate title="Move down">↓</button>
          <button type="submit" class="btn btn-ghost btn-sm">Save</button>
          <button type="submit" name="action" value="delete_center" formnovalidate class="btn btn-danger btn-sm" data-confirm="Remove this city from the network list?">Delete</button>
        </div>
      </form>
    <?php endforeach; ?>
  </div>

  <h3 style="font-size:1rem; margin-top:22px;">Add a city</h3>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <input type="hidden" name="action" value="add_center">
    <div style="display:grid; grid-template-columns: 2fr 1.5fr 2fr auto; gap:12px; align-items:end;">
      <div class="form-group" style="margin-bottom:0;">
        <label>City name</label>
        <input type="text" name="name" required>
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label>Photo</label>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" required>
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label>Website (optional)</label>
        <input type="text" name="website_url" placeholder="https://…">
      </div>
      <button type="submit" class="btn btn-gold">Add city</button>
    </div>
  </form>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
