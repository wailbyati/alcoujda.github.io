<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_login();

$validPages = [
    'home'         => 'Home',
    'about'        => 'About',
    'community'    => 'Clubs &amp; Community',
    'contact'      => 'Contact',
    'registration' => 'Registration',
];

$page = $_GET['page'] ?? '';
if (!isset($validPages[$page])) {
    header('Location: /admin/index.php');
    exit;
}

/* Sections managed as repeatable items — add / delete / reorder item-by-item. */
$itemSections = [
    'home' => [
        ['section' => 'hero',         'title' => 'Hero slides',        'addLabel' => 'Add slide',  'itemLabel' => 'slide',  'fields' => ['image', 'title', 'link'], 'hint' => 'One slide per entry. The "Label" is optional and the "Link" is where the slide points.'],
        ['section' => 'offers',       'title' => 'What we offer',      'addLabel' => 'Add highlight', 'itemLabel' => 'highlight', 'fields' => ['title', 'content'], 'hint' => 'Featured highlights — title plus one line of text.'],
        ['section' => 'about_points', 'title' => 'About — bullet points', 'itemLabel' => 'point'],
        ['section' => 'stat_band',    'title' => 'By the numbers',     'itemLabel' => 'statistic', 'hint' => 'Format each one like "150+ classes / week" — the number and its label are split on the first space.'],
        ['section' => 'faqs',         'title' => 'Frequently asked questions', 'addLabel' => 'Add question', 'itemLabel' => 'question', 'fields' => ['title', 'content'], 'hint' => 'Title = the question, text = the answer.'],
        ['section' => 'partners',     'title' => 'Partners',           'addLabel' => 'Add partner', 'itemLabel' => 'partner', 'fields' => ['image', 'title', 'link'], 'hint' => 'Optional logo, plus a name and link. Plain text is shown when no logo is uploaded.'],
    ],
    'about' => [
        ['section' => 'initiatives',  'title' => 'ALC &amp; ACA initiatives', 'addLabel' => 'Add initiative', 'itemLabel' => 'initiative', 'fields' => ['title', 'content', 'image'], 'hint' => 'Each entry is a full initiative section: title, body text, and a photo.'],
    ],
    'community' => [
        ['section' => 'clubs',        'title' => 'Clubs',              'addLabel' => 'Add club', 'itemLabel' => 'club', 'fields' => ['title', 'content'], 'hint' => 'One card per club: title and a short description.'],
        ['section' => 'csc_points',   'title' => 'Community Service Club — highlights', 'itemLabel' => 'point'],
    ],
    'contact' => [],
    'registration' => [
        ['section' => 'before_register', 'title' => 'Before you register — bullet points', 'itemLabel' => 'point'],
    ],
];

/* Legacy content_blocks that now live in the item editors above — hidden
   from the plain block list so there is only one way to edit them. */
$hiddenBlocks = [
    'home' => array_merge(
        ['hero_image_1', 'hero_image_2', 'hero_image_3', 'hero_image_4', 'about_points', 'stat_classes', 'stat_students', 'stat_clubs'],
        array_map(fn($i) => "offer_{$i}_title", range(1, 8)),
        array_map(fn($i) => "offer_{$i}_body", range(1, 8)),
        array_map(fn($i) => "faq_{$i}_question", range(1, 12)),
        array_map(fn($i) => "faq_{$i}_answer", range(1, 12)),
        array_map(fn($i) => "partner_{$i}_name", range(1, 12)),
        array_map(fn($i) => "partner_{$i}_url", range(1, 12)),
        array_map(fn($i) => "partner_{$i}_image", range(1, 12))
    ),
    'about' => array_merge(
        array_map(fn($i) => "initiative_{$i}_title", range(1, 8)),
        array_map(fn($i) => "initiative_{$i}_body", range(1, 8)),
        array_map(fn($i) => "initiative_{$i}_image", range(1, 8))
    ),
    'community' => array_merge(
        ['csc_points'],
        array_map(fn($i) => "club_{$i}_title", range(1, 12)),
        array_map(fn($i) => "club_{$i}_body", range(1, 12)),
        array_map(fn($i) => "club_{$i}_image", range(1, 12))
    ),
    'contact'      => [],
    'registration' => ['before_you_register'],
];

$token = csrf_token();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired — please try again.';
    } else {
        $allowedSections = array_column($itemSections[$page], 'section');
        $result = handle_content_item_actions($page, $allowedSections, $_POST);

        if ($result['handled']) {
            $success = $result['success'];
            $error   = $result['error'];
        } elseif ($blockAction = detect_block_action($_POST)) {
            try {
                if ($blockAction['action'] === 'move_up_block') {
                    move_content_block($page, $blockAction['key'], -1);
                } elseif ($blockAction['action'] === 'move_down_block') {
                    move_content_block($page, $blockAction['key'], 1);
                } else {
                    delete_content_block($page, $blockAction['key']);
                }
                $success = 'Block updated.';
            } catch (Throwable $e) {
                $error = 'Could not update block: ' . $e->getMessage();
            }
        } elseif (($_POST['action'] ?? '') === 'add_block') {
            try {
                add_content_block(
                    $page,
                    '',
                    trim((string) ($_POST['label'] ?? '')),
                    (string) ($_POST['block_type'] ?? 'text')
                );
                $success = 'Block added — fill it in below and save.';
            } catch (Throwable $e) {
                $error = 'Could not add block: ' . $e->getMessage();
            }
        } else {
            try {
                $blocks = get_blocks_for_page($page);
                $pdo = db();

                $updateText  = $pdo->prepare('UPDATE content_blocks SET content = ? WHERE page_slug = ? AND block_key = ?');
                $updateImage = $pdo->prepare('UPDATE content_blocks SET image_path = ? WHERE page_slug = ? AND block_key = ?');
                $hidden     = $hiddenBlocks[$page];

                foreach ($blocks as $key => $block) {
                    if (in_array($key, $hidden, true)) {
                        continue;
                    }
                    if ($block['block_type'] === 'image') {
                        $file = grouped_file($_FILES['image'] ?? [], $key);
                        $newPath = handle_image_upload($file);
                        if ($newPath !== null) {
                            $updateImage->execute([$newPath, $page, $key]);
                        }
                    } else {
                        $value = $_POST['content'][$key] ?? '';
                        $updateText->execute([trim($value), $page, $key]);
                    }
                }

                $success = 'Changes saved.';
            } catch (RuntimeException $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// Re-fetch fresh content, keyed by block_key, so the form shows what was just saved
$stmt = db()->prepare(
    'SELECT block_key, block_type, label, content, image_path
     FROM content_blocks WHERE page_slug = ? ORDER BY sort_order ASC'
);
$stmt->execute([$page]);
$blocks = [];
foreach ($stmt->fetchAll() as $row) {
    $blocks[$row['block_key']] = $row;
}

$hidden = $hiddenBlocks[$page];
$keys = array_values(array_filter(array_keys($blocks), static fn(string $k): bool => !in_array($k, $hidden, true)));

$hintFor = [];
foreach ($blocks as $k => $b) {
    if (!in_array($k, $hidden, true) && stripos((string) $b['label'], 'one per line') !== false) {
        $hintFor[$k] = 'One item per line.';
    }
}

$pageTitle = strip_tags($validPages[$page]) . ' — Page Content';
$activeMenu = $page;
require __DIR__ . '/partials/header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?= h($token) ?>">

  <div class="card">
    <div class="card-header">
      <h2><?= $validPages[$page] ?> page content</h2>
      <a href="/<?= $page === 'home' ? 'index' : h($page) ?>.php" target="_blank" class="btn btn-ghost btn-sm">View page ↗</a>
    </div>

    <div class="hint" style="margin-bottom:20px;">
      Use the ↑ ↓ arrows on any block to reorder it. Lists (bullets, stats, hero slides…) are edited
      in their own sections below this one.
    </div>

    <?php
    $blockControls = true;
    require __DIR__ . '/partials/block-fields.php';
    ?>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Save changes</button>
    </div>
  </div>
</form>

<?php foreach ($itemSections[$page] as $editorCfg): ?>
  <?php
  $items = get_content_items($page, $editorCfg['section']);
  $cardTitle = $editorCfg['title'];
  $section   = $editorCfg['section'];
  $fields    = $editorCfg['fields'] ?? ['content'];
  $itemLabel = $editorCfg['itemLabel'] ?? 'item';
  $hint      = $editorCfg['hint'] ?? '';
  $addLabel  = $editorCfg['addLabel'] ?? null;
  require __DIR__ . '/partials/item-editor.php'; ?>
<?php endforeach; ?>

<div class="card">
  <div class="card-header"><h2>Add a new block</h2></div>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <input type="hidden" name="action" value="add_block">
    <div style="display:grid; grid-template-columns: 2fr 1.2fr auto; gap:12px; align-items:end;">
      <div class="form-group" style="margin-bottom:0;">
        <label>Block label (shown to you, e.g. "Special note")</label>
        <input type="text" name="label" required>
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label>Type</label>
        <select name="block_type">
          <option value="text">Single line</option>
          <option value="richtext">Paragraphs</option>
          <option value="image">Photo</option>
          <option value="stat">Number + label</option>
        </select>
      </div>
      <button type="submit" class="btn btn-gold">Add block</button>
    </div>
  </form>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
