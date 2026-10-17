<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pageTitle = 'Programs';
$activeMenu = 'programs';
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
            if ($action === 'program_move_up' || $action === 'program_move_down') {
                move_row('programs', (int) ($_POST['id'] ?? 0), $action === 'program_move_up' ? -1 : 1);
                $success = 'Order updated.';
            } elseif ($action === 'add_program') {
                $stmt = $pdo->prepare(
                    'INSERT INTO programs (title, audience, level, description, duration, schedule, status, sort_order)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $nextOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order),0)+1 n FROM programs')->fetch()['n'];
                $stmt->execute([
                    trim($_POST['title'] ?? ''),
                    trim($_POST['audience'] ?? ''),
                    trim($_POST['level'] ?? ''),
                    trim($_POST['description'] ?? ''),
                    trim($_POST['duration'] ?? ''),
                    trim($_POST['schedule'] ?? ''),
                    ($_POST['status'] ?? '') === 'hidden' ? 'hidden' : 'active',
                    $nextOrder,
                ]);
                $success = 'Program added.';
            } elseif ($action === 'update_program') {
                $id = (int) ($_POST['id'] ?? 0);
                $stmt = $pdo->prepare(
                    'UPDATE programs
                     SET title=?, audience=?, level=?, description=?, duration=?, schedule=?, status=?
                     WHERE id=?'
                );
                $stmt->execute([
                    trim($_POST['title'] ?? ''),
                    trim($_POST['audience'] ?? ''),
                    trim($_POST['level'] ?? ''),
                    trim($_POST['description'] ?? ''),
                    trim($_POST['duration'] ?? ''),
                    trim($_POST['schedule'] ?? ''),
                    ($_POST['status'] ?? '') === 'hidden' ? 'hidden' : 'active',
                    $id,
                ]);
                $success = 'Program updated.';
            } elseif ($action === 'delete_program') {
                $pdo->prepare('DELETE FROM programs WHERE id=?')->execute([(int) $_POST['id']]);
                $success = 'Program removed.';
            }
        } catch (Throwable $e) {
            $error = 'Could not save: ' . $e->getMessage();
        }
    }
}

$programs = $pdo->query('SELECT * FROM programs ORDER BY sort_order ASC, id ASC')->fetchAll();

require __DIR__ . '/partials/header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-header">
    <h2>English programs</h2>
    <a href="/academics.php" target="_blank" class="btn btn-ghost btn-sm">View page ↗</a>
  </div>
  <div class="hint" style="margin-bottom:20px;">
    These are the program cards shown on the public Academics page. Use ↑ ↓ to
    set the order, edit inline, or delete. Programs hidden here disappear from the page.
  </div>

  <div class="grid-table" style="grid-template-columns: 2fr 1.2fr 1.2fr 1fr 1fr 1.2fr auto;">
    <div class="grid-table-head">
      <div>Program</div><div>Audience</div><div>Level</div><div>Duration</div><div>Schedule</div><div>Status</div><div></div>
    </div>
    <?php foreach ($programs as $item): ?>
      <form method="post" class="grid-table-row" style="grid-template-columns: inherit;">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <input type="hidden" name="action" value="update_program">
        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
        <input type="text" name="title" value="<?= h($item['title']) ?>">
        <input type="text" name="audience" value="<?= h($item['audience']) ?>">
        <input type="text" name="level" value="<?= h($item['level']) ?>">
        <input type="text" name="duration" value="<?= h($item['duration']) ?>">
        <input type="text" name="schedule" value="<?= h($item['schedule']) ?>">
        <select name="status">
          <option value="active" <?= $item['status'] === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="hidden" <?= $item['status'] === 'hidden' ? 'selected' : '' ?>>Hidden</option>
        </select>
        <div class="row-actions">
          <button type="submit" class="btn btn-ghost btn-sm" name="action" value="program_move_up"
                  formnovalidate title="Move up">↑</button>
          <button type="submit" class="btn btn-ghost btn-sm" name="action" value="program_move_down"
                  formnovalidate title="Move down">↓</button>
          <button type="submit" class="btn btn-ghost btn-sm">Save</button>
          <button type="submit" name="action" value="delete_program"
                  formnovalidate class="btn btn-danger btn-sm"
                  data-confirm="Remove this program?">Delete</button>
        </div>
      </form>
    <?php endforeach; ?>
  </div>

  <p class="hint" style="margin-top:10px;">Descriptions are edited below the table — click Save on a row after making changes.</p>

  <h3 style="font-size:1rem; margin-top:24px;">Add a program</h3>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <input type="hidden" name="action" value="add_program">
    <div class="form-group">
      <label>Program title</label>
      <input type="text" name="title" required placeholder="e.g. Kids English">
    </div>
    <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:12px;">
      <div class="form-group">
        <label>Audience</label>
        <input type="text" name="audience" placeholder="Kids (5–11)">
      </div>
      <div class="form-group">
        <label>Level</label>
        <input type="text" name="level" placeholder="Beginner–Intermediate">
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <option value="active">Active</option>
          <option value="hidden">Hidden</option>
        </select>
      </div>
    </div>
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
      <div class="form-group">
        <label>Duration</label>
        <input type="text" name="duration" placeholder="3 months per session">
      </div>
      <div class="form-group">
        <label>Schedule</label>
        <input type="text" name="schedule" placeholder="Weekday afternoons & Saturdays">
      </div>
    </div>
    <div class="form-group">
      <label>Description</label>
      <textarea name="description" rows="3" placeholder="One or two sentences about the program."></textarea>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-gold">Add program</button>
    </div>
  </form>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>