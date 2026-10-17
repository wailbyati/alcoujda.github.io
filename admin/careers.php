<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_login();

$pageTitle = 'Careers';
$activeMenu = 'careers';
$token = csrf_token();
$success = '';
$error = '';
$pdo = db();

$textKeys = [
    'page_title', 'page_caption',
    'overview', 'how_to_apply', 'living_city_title', 'living_city',
];
$imageKeys = [
    'how_to_apply_image',
    'living_image_1', 'living_image_2', 'living_image_3', 'living_image_4',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired — please try again.';
    } else {
        $itemResult = handle_content_item_actions('careers', ['apply_points'], $_POST);
        if ($itemResult['handled']) {
            $success = $itemResult['success'];
            $error   = $itemResult['error'];
        } else {
            $action = $_POST['action'] ?? '';
            try {
                if ($action === 'save_overview') {
                    save_block_texts('careers', $textKeys, $_POST);

                    $updateImage = $pdo->prepare(
                        'UPDATE content_blocks SET image_path = ? WHERE page_slug = ? AND block_key = ?'
                    );
                    foreach ($imageKeys as $key) {
                        $newPath = handle_image_upload(grouped_file($_FILES['image'] ?? [], $key));
                        if ($newPath !== null) {
                            $updateImage->execute([$newPath, 'careers', $key]);
                        }
                    }
                    $success = 'Overview text saved.';

                } elseif ($action === 'add_faq') {
                    $nextOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order),0)+1 n FROM career_faqs')->fetch()['n'];
                    $stmt = $pdo->prepare('INSERT INTO career_faqs (question, answer, sort_order) VALUES (?, ?, ?)');
                    $stmt->execute([trim($_POST['question'] ?? ''), trim($_POST['answer'] ?? ''), $nextOrder]);
                    $success = 'FAQ added.';
                } elseif ($action === 'update_faq') {
                    $stmt = $pdo->prepare('UPDATE career_faqs SET question=?, answer=? WHERE id=?');
                    $stmt->execute([trim($_POST['question'] ?? ''), trim($_POST['answer'] ?? ''), (int) $_POST['id']]);
                    $success = 'FAQ updated.';
                } elseif ($action === 'faq_move_up' || $action === 'faq_move_down') {
                    move_row('career_faqs', (int) ($_POST['id'] ?? 0), $action === 'faq_move_up' ? -1 : 1);
                    $success = 'Order updated.';
                } elseif ($action === 'delete_faq') {
                    $pdo->prepare('DELETE FROM career_faqs WHERE id=?')->execute([(int) $_POST['id']]);
                    $success = 'FAQ removed.';

                } elseif ($action === 'add_benefit') {
                    $nextOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order),0)+1 n FROM career_benefits')->fetch()['n'];
                    $stmt = $pdo->prepare('INSERT INTO career_benefits (text, sort_order) VALUES (?, ?)');
                    $stmt->execute([trim($_POST['text'] ?? ''), $nextOrder]);
                    $success = 'Benefit added.';
                } elseif ($action === 'update_benefit') {
                    $stmt = $pdo->prepare('UPDATE career_benefits SET text=? WHERE id=?');
                    $stmt->execute([trim($_POST['text'] ?? ''), (int) $_POST['id']]);
                    $success = 'Benefit updated.';
                } elseif ($action === 'benefit_move_up' || $action === 'benefit_move_down') {
                    move_row('career_benefits', (int) ($_POST['id'] ?? 0), $action === 'benefit_move_up' ? -1 : 1);
                    $success = 'Order updated.';
                } elseif ($action === 'delete_benefit') {
                    $pdo->prepare('DELETE FROM career_benefits WHERE id=?')->execute([(int) $_POST['id']]);
                    $success = 'Benefit removed.';
                }
            } catch (Throwable $e) {
                $error = 'Could not save: ' . $e->getMessage();
            }
        }
    }
}

$blocks = get_blocks_for_page('careers');
$faqs = $pdo->query('SELECT * FROM career_faqs ORDER BY sort_order ASC')->fetchAll();
$benefits = $pdo->query('SELECT * FROM career_benefits ORDER BY sort_order ASC')->fetchAll();
$keys = array_merge($textKeys, $imageKeys);
$hintFor = [];

require __DIR__ . '/partials/header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-header">
    <h2>Page &amp; overview text</h2>
    <a href="/careers.php" target="_blank" class="btn btn-ghost btn-sm">View page ↗</a>
  </div>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <input type="hidden" name="action" value="save_overview">
    <?php require __DIR__ . '/partials/block-fields.php'; ?>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Save overview</button>
    </div>
  </form>
</div>

<?php
$items     = get_content_items('careers', 'apply_points');
$section   = 'apply_points';
$cardTitle = 'How to apply — bullet points';
$fields    = ['content'];
$itemLabel = 'point';
$hint      = 'One bullet point per entry — shown as the "How to Apply" checklist on the Careers page.';
$addLabel  = 'Add point';
require __DIR__ . '/partials/item-editor.php';
?>

<div class="card">
  <div class="card-header">
    <h2>Frequently asked questions</h2>
    <span class="hint" style="margin:0 0 0 auto;">Use ↑ ↓ to set the order.</span>
  </div>
  <div class="grid-table">
    <div class="grid-table-head" style="grid-template-columns: 2fr 3fr auto;">
      <div>Question</div><div>Answer</div><div></div>
    </div>
    <?php foreach ($faqs as $faq): ?>
      <form method="post" class="grid-table-row" style="grid-template-columns: 2fr 3fr auto; align-items:start;">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <input type="hidden" name="action" value="update_faq">
        <input type="hidden" name="id" value="<?= (int) $faq['id'] ?>">
        <input type="text" name="question" value="<?= h($faq['question']) ?>">
        <textarea name="answer" rows="2"><?= h($faq['answer']) ?></textarea>
        <div class="row-actions">
          <button type="submit" class="btn btn-ghost btn-sm" name="action" value="faq_move_up"
                  formnovalidate title="Move up">↑</button>
          <button type="submit" class="btn btn-ghost btn-sm" name="action" value="faq_move_down"
                  formnovalidate title="Move down">↓</button>
          <button type="submit" class="btn btn-ghost btn-sm">Save</button>
          <button type="submit" name="action" value="delete_faq" formnovalidate class="btn btn-danger btn-sm" data-confirm="Remove this FAQ?">Delete</button>
        </div>
      </form>
    <?php endforeach; ?>
  </div>

  <h3 style="font-size:1rem; margin-top:22px;">Add a question</h3>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <input type="hidden" name="action" value="add_faq">
    <div class="form-group"><label>Question</label><input type="text" name="question" required></div>
    <div class="form-group"><label>Answer</label><textarea name="answer" rows="3" required></textarea></div>
    <button type="submit" class="btn btn-gold">Add FAQ</button>
  </form>
</div>

<div class="card">
  <div class="card-header"><h2>Benefits</h2></div>
  <div class="grid-table">
    <div class="grid-table-head" style="grid-template-columns: 1fr auto;">
      <div>Benefit</div><div></div>
    </div>
    <?php foreach ($benefits as $b): ?>
      <form method="post" class="grid-table-row" style="grid-template-columns: 1fr auto;">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <input type="hidden" name="action" value="update_benefit">
        <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
        <input type="text" name="text" value="<?= h($b['text']) ?>">
        <div class="row-actions">
          <button type="submit" class="btn btn-ghost btn-sm" name="action" value="benefit_move_up"
                  formnovalidate title="Move up">↑</button>
          <button type="submit" class="btn btn-ghost btn-sm" name="action" value="benefit_move_down"
                  formnovalidate title="Move down">↓</button>
          <button type="submit" class="btn btn-ghost btn-sm">Save</button>
          <button type="submit" name="action" value="delete_benefit" formnovalidate class="btn btn-danger btn-sm" data-confirm="Remove this benefit?">Delete</button>
        </div>
      </form>
    <?php endforeach; ?>
  </div>

  <h3 style="font-size:1rem; margin-top:22px;">Add a benefit</h3>
  <form method="post" style="display:flex; gap:12px; align-items:end;">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <input type="hidden" name="action" value="add_benefit">
    <div class="form-group" style="flex:1; margin-bottom:0;">
      <label>Benefit text</label>
      <input type="text" name="text" required>
    </div>
    <button type="submit" class="btn btn-gold">Add</button>
  </form>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
