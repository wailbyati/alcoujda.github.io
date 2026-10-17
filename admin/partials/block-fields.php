<?php
/**
 * Renders editable fields for a subset of a page's content blocks.
 * Expects $blocks (from get_blocks_for_page) and $keys (block_key list).
 * $hintFor lets a page override the per-type help text.
 */
$blockFields = $blocks;
$blockKeys   = $keys ?? [];
$blockHint   = $hintFor ?? [];
$blockMove   = !empty($blockControls); // show ↑/↓/Delete per field

foreach ($blockKeys as $key):
    if (!isset($blockFields[$key])) {
        continue;
    }
    $block = $blockFields[$key];
    ?>
    <div class="form-group">
      <label><?= h($block['label']) ?>
        <?php if ($blockMove): ?>
          <span class="block-controls">
            <button type="submit" class="btn btn-ghost btn-sm" name="move_up_block_<?= h($key) ?>" value="1"
                    formnovalidate title="Move this block up">↑</button>
            <button type="submit" class="btn btn-ghost btn-sm" name="move_down_block_<?= h($key) ?>" value="1"
                    formnovalidate title="Move this block down">↓</button>
            <button type="submit" class="btn btn-danger btn-sm" name="delete_block_<?= h($key) ?>" value="1"
                    formnovalidate data-confirm="Delete this block and its content?">Delete</button>
          </span>
        <?php endif; ?>
      </label>

      <?php if ($block['block_type'] === 'image'): ?>
        <div class="image-field">
          <div class="preview" id="preview-<?= h($key) ?>">
            <?php if ($block['image_path']): ?>
              <img src="<?= h(asset($block['image_path'])) ?>" alt="">
            <?php endif; ?>
          </div>
          <input type="file" name="image[<?= h($key) ?>]"
                 data-preview="preview-<?= h($key) ?>" accept="image/*">
        </div>

      <?php elseif ($block['block_type'] === 'richtext'): ?>
        <div class="hint"><?= h($blockHint[$key] ?? 'Leave a blank line between paragraphs.') ?></div>
        <textarea name="content[<?= h($key) ?>]" rows="5"><?= h($block['content']) ?></textarea>

      <?php else: ?>
        <input type="text" name="content[<?= h($key) ?>]" value="<?= h($block['content']) ?>">
      <?php endif; ?>
    </div>
<?php endforeach; ?>
