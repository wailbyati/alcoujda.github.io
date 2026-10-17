<?php
/**
 * Generic "repeater" editor for list-style content.
 * Renders existing items with inline edit + ↑/↓ reorder + delete,
 * plus a form to add new items. Every action posts back to the same
 * admin page, where handle_content_item_actions() in includes/functions.php
 * does the work — one code path for fee notes, bullets, stats,
 * hero slides, initiatives, etc.
 *
 * Expected before include:
 *   $page   — page slug      (e.g. 'academics')
 *   $section— section key    (e.g. 'fee_notes')
 *   $cardTitle — card heading
 *   $token  — CSRF token
 *   $items  — rows from get_content_items($page, $section)
 * Optional:
 *   $hint      — help text under the heading
 *   $itemLabel — singular noun for buttons (default 'item')
 *   $addLabel  — label for the add button
 *   $fields    — which fields to show, subset of
 *                ['title','content','image','link'] (default ['content'])
 */
$itemFields = $fields ?? ['content'];
$itemNoun   = $itemLabel ?? 'item';
$addLabel   = $addLabel ?? 'Add item';
$hintText   = $hint ?? '';

$show = [
    'image'   => in_array('image', $itemFields, true),
    'title'   => in_array('title', $itemFields, true),
    'content' => in_array('content', $itemFields, true),
    'link'    => in_array('link', $itemFields, true),
];

$colWidths = [];
if ($show['image']) $colWidths[] = '150px';
if ($show['title']) $colWidths[] = '1.3fr';
if ($show['content']) $colWidths[] = '2.2fr';
if ($show['link'])   $colWidths[] = '1.1fr';
$colWidths[] = 'auto';
$gridCols = implode(' ', $colWidths);
?>

<div class="card">
  <div class="card-header"><h2><?= h($cardTitle) ?></h2></div>

  <?php if ($hintText): ?>
    <div class="hint" style="margin-bottom:14px;"><?= h($hintText) ?></div>
  <?php endif; ?>

  <?php if (!$items): ?>
    <div class="empty-state">Nothing here yet — add the first <?= h($itemNoun) ?> below.</div>
  <?php else: ?>
    <div class="grid-table">
      <div class="grid-table-head" style="grid-template-columns: <?= $gridCols ?>;">
        <?php if ($show['image']): ?><div>Photo</div><?php endif; ?>
        <?php if ($show['title']): ?><div><?= $show['content'] ? 'Title' : 'Label' ?></div><?php endif; ?>
        <?php if ($show['content']): ?><div><?= ucfirst($itemNoun) ?></div><?php endif; ?>
        <?php if ($show['link']): ?><div>Link</div><?php endif; ?>
        <div></div>
      </div>

      <?php foreach ($items as $item): ?>
        <form method="post" enctype="multipart/form-data" class="grid-table-row"
              style="grid-template-columns: <?= $gridCols ?>; align-items:start;">
          <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
          <input type="hidden" name="action" value="<?= h($section) ?>_update">
          <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">

          <?php if ($show['image']): ?>
            <div>
              <div class="preview" id="preview-<?= h($section) ?>-<?= (int) $item['id'] ?>" style="width:100%;">
                <?php if ($item['image_path']): ?>
                  <img src="<?= h(asset($item['image_path'])) ?>" alt="">
                <?php endif; ?>
              </div>
              <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                     data-preview="preview-<?= h($section) ?>-<?= (int) $item['id'] ?>">
            </div>
          <?php endif; ?>

          <?php if ($show['title']): ?>
            <input type="text" name="title" value="<?= h($item['title'] ?? '') ?>">
          <?php endif; ?>

          <?php if ($show['content']): ?>
            <textarea name="content" rows="2"><?= h($item['content'] ?? '') ?></textarea>
          <?php endif; ?>

          <?php if ($show['link']): ?>
            <input type="text" name="link" value="<?= h($item['link'] ?? '') ?>" placeholder="https://… or /page.php">
          <?php endif; ?>

          <div class="row-actions">
            <button type="submit" class="btn btn-ghost btn-sm" name="action" value="<?= h($section) ?>_up"
                    formnovalidate title="Move up">↑</button>
            <button type="submit" class="btn btn-ghost btn-sm" name="action" value="<?= h($section) ?>_down"
                    formnovalidate title="Move down">↓</button>
            <button type="submit" class="btn btn-ghost btn-sm">Save</button>
            <button type="submit" class="btn btn-danger btn-sm" name="action" value="<?= h($section) ?>_delete"
                    formnovalidate data-confirm="Remove this <?= h($itemNoun) ?>?">Delete</button>
          </div>
        </form>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <h3 style="font-size:1rem; margin-top:22px;">Add a <?= h($itemNoun) ?></h3>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <input type="hidden" name="action" value="<?= h($section) ?>_add">
    <div class="item-add" style="display:grid; grid-template-columns: <?= $gridCols ?>; gap:12px; align-items:end;">

      <?php if ($show['image']): ?>
        <div class="form-group" style="margin-bottom:0;">
          <label>Photo</label>
          <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
        </div>
      <?php endif; ?>

      <?php if ($show['title']): ?>
        <div class="form-group" style="margin-bottom:0;">
          <label><?= $show['content'] ? 'Title' : 'Label' ?></label>
          <input type="text" name="title">
        </div>
      <?php endif; ?>

      <?php if ($show['content']): ?>
        <div class="form-group" style="margin-bottom:0;">
          <label><?= ucfirst($itemNoun) ?></label>
          <input type="text" name="content" style="width:100%;">
        </div>
      <?php endif; ?>

      <?php if ($show['link']): ?>
        <div class="form-group" style="margin-bottom:0;">
          <label>Link</label>
          <input type="text" name="link" placeholder="https://… or /page.php">
        </div>
      <?php endif; ?>

      <button type="submit" class="btn btn-gold"><?= h($addLabel) ?></button>
    </div>
  </form>
</div>