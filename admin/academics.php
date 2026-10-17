<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pageTitle = 'Academics';
$activeMenu = 'academics';
$token = csrf_token();
$success = '';
$error = '';

$pdo = db();

$overviewKeys = [
    'page_title', 'page_caption',
    'overview_en', 'overview_fr',
    'table_col_program', 'table_col_program_fr', 'table_col_hours', 'table_col_price',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired — please try again.';
    } else {
        $itemResult = handle_content_item_actions('academics', ['fee_notes'], $_POST);
        if ($itemResult['handled']) {
            $success = $itemResult['success'];
            $error   = $itemResult['error'];
        } else {
            $action = $_POST['action'] ?? '';
            try {
                if ($action === 'save_overview') {
                    save_block_texts('academics', $overviewKeys, $_POST);
                    $success = 'Overview text saved.';
                } elseif ($action === 'tuition_move_up' || $action === 'tuition_move_down') {
                    move_row('tuition_items', (int) ($_POST['id'] ?? 0), $action === 'tuition_move_up' ? -1 : 1);
                    $success = 'Order updated.';
                } elseif ($action === 'add_item') {
                    $stmt = $pdo->prepare(
                        'INSERT INTO tuition_items (name_en, name_fr, hours_note, price_dh, sort_order)
                         VALUES (?, ?, ?, ?, ?)'
                    );
                    $nextOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order),0)+1 n FROM tuition_items')->fetch()['n'];
                    $stmt->execute([
                        trim($_POST['name_en'] ?? ''),
                        trim($_POST['name_fr'] ?? ''),
                        trim($_POST['hours_note'] ?? ''),
                        trim($_POST['price_dh'] ?? ''),
                        $nextOrder,
                    ]);
                    $success = 'Program added.';
                } elseif ($action === 'update_item') {
                    $id = (int) ($_POST['id'] ?? 0);
                    $stmt = $pdo->prepare(
                        'UPDATE tuition_items SET name_en=?, name_fr=?, hours_note=?, price_dh=? WHERE id=?'
                    );
                    $stmt->execute([
                        trim($_POST['name_en'] ?? ''),
                        trim($_POST['name_fr'] ?? ''),
                        trim($_POST['hours_note'] ?? ''),
                        trim($_POST['price_dh'] ?? ''),
                        $id,
                    ]);
                    $success = 'Program updated.';
                } elseif ($action === 'delete_item') {
                    $id = (int) ($_POST['id'] ?? 0);
                    $stmt = $pdo->prepare('DELETE FROM tuition_items WHERE id=?');
                    $stmt->execute([$id]);
                    $success = 'Program removed.';
                }
            } catch (Throwable $e) {
                $error = 'Could not save: ' . $e->getMessage();
            }
        }
    }
}

$blocks = get_blocks_for_page('academics');
$tuitionItems = $pdo->query('SELECT * FROM tuition_items ORDER BY sort_order ASC')->fetchAll();
$keys = $overviewKeys;

require __DIR__ . '/partials/header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-header">
    <h2>Page &amp; overview text</h2>
    <a href="/academics.php" target="_blank" class="btn btn-ghost btn-sm">View page ↗</a>
  </div>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <input type="hidden" name="action" value="save_overview">
    <?php require __DIR__ . '/partials/block-fields.php'; ?>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Save overview</button>
    </div>
  </form>
</div>

<?php
$items       = get_content_items('academics', 'fee_notes');
$section     = 'fee_notes';
$cardTitle   = 'Fee notes';
$fields      = ['content'];
$itemLabel   = 'fee note';
$hint        = 'One fee note per entry — shown as a small card under the tuition table.';
$addLabel    = 'Add fee note';
require __DIR__ . '/partials/item-editor.php';
?>

<div class="card">
  <div class="card-header"><h2>Tuition &amp; fees table</h2>
    <span class="hint" style="margin:0 0 0 auto;">Use ↑ ↓ to set the order rows appear in.</span></div>

  <div class="grid-table">
    <div class="grid-table-head">
      <div>Program (EN)</div><div>Programme (FR)</div><div>Hours</div><div>Price</div><div></div>
    </div>
    <?php foreach ($tuitionItems as $item): ?>
      <form method="post" class="grid-table-row">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <input type="hidden" name="action" value="update_item">
        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
        <input type="text" name="name_en" value="<?= h($item['name_en']) ?>">
        <input type="text" name="name_fr" value="<?= h($item['name_fr']) ?>">
        <input type="text" name="hours_note" value="<?= h($item['hours_note']) ?>">
        <input type="text" name="price_dh" value="<?= h($item['price_dh']) ?>">
        <div class="row-actions">
          <button type="submit" class="btn btn-ghost btn-sm" name="action" value="tuition_move_up"
                  formnovalidate title="Move up">↑</button>
          <button type="submit" class="btn btn-ghost btn-sm" name="action" value="tuition_move_down"
                  formnovalidate title="Move down">↓</button>
          <button type="submit" class="btn btn-ghost btn-sm">Save</button>
          <button type="submit" name="action" value="delete_item"
                  formnovalidate class="btn btn-danger btn-sm"
                  data-confirm="Remove this program from the tuition table?">Delete</button>
        </div>
      </form>
    <?php endforeach; ?>
  </div>

  <h3 style="font-size:1rem; margin-top:24px;">Add a program</h3>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <input type="hidden" name="action" value="add_item">
    <div style="display:grid; grid-template-columns: 2fr 2fr 1.5fr 1.5fr auto; gap:12px; align-items:end;">
      <div class="form-group" style="margin-bottom:0;">
        <label>Name (EN)</label>
        <input type="text" name="name_en" required>
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label>Nom (FR)</label>
        <input type="text" name="name_fr" required>
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label>Hours</label>
        <input type="text" name="hours_note" placeholder="30 hours">
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label>Price</label>
        <input type="text" name="price_dh" placeholder="1.250,00 DH" required>
      </div>
      <button type="submit" class="btn btn-gold">Add</button>
    </div>
  </form>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
