<?php
require_once __DIR__ . '/includes/functions.php';

$blocks = get_blocks_for_page('academics');
$pageTitle   = block_text($blocks, 'page_title', 'Programs');
$pageCaption = block_text($blocks, 'page_caption', 'English and French courses for every age and level');
$activeNav = 'academics';
require __DIR__ . '/includes/header.php';

$tuition  = db()->query('SELECT * FROM tuition_items ORDER BY sort_order ASC')->fetchAll();
$programs = db()->query("SELECT * FROM programs WHERE status = 'active' ORDER BY sort_order ASC")->fetchAll();

$columns = [
    block_text($blocks, 'table_col_program', 'Program (EN)'),
    block_text($blocks, 'table_col_program_fr', 'Programme (FR)'),
    block_text($blocks, 'table_col_hours', 'Hours'),
    block_text($blocks, 'table_col_price', 'Price'),
];
?>

<!-- ================================================================
     Programs grid
     ================================================================ -->
<?php if ($programs): ?>
<section class="section">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="eyebrow">Programs</p>
      <h2><?= h(block_text($blocks, 'programs_heading', 'English for Every Age and Level')) ?></h2>
      <?= block_richtext($blocks, 'programs_intro') ?>
    </div>
    <div class="col-2" id="levels">
      <?php foreach ($programs as $program): ?>
        <article class="card reveal">
          <span class="tag"><?= h($program['audience']) ?></span>
          <h3><?= h($program['title']) ?></h3>
          <?php if ($program['level']): ?><p class="program-meta"><?= h($program['level']) ?></p><?php endif; ?>
          <?php if ($program['description']): ?><p style="color:var(--muted);"><?= h($program['description']) ?></p><?php endif; ?>
          <ul class="program-details">
            <?php if ($program['duration']): ?><li><strong>Duration:</strong> <?= h($program['duration']) ?></li><?php endif; ?>
            <?php if ($program['schedule']): ?><li><strong>Schedule:</strong> <?= h($program['schedule']) ?></li><?php endif; ?>
          </ul>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ================================================================
     Course information (English / French tabs)
     ================================================================ -->
<section class="section section-grey" id="english">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="eyebrow">Good to know</p>
      <h2>Course Information</h2>
    </div>

    <div data-tabs class="reveal">
      <div class="tabs" role="tablist">
        <button type="button" class="tab-btn is-active">🇬🇧 English</button>
        <button type="button" class="tab-btn">🇫🇷 Français</button>
      </div>

      <div class="tab-panel is-active">
        <div class="card">
          <h3>Tuition &amp; Fees</h3>
          <?= block_richtext($blocks, 'overview_en') ?>
        </div>
      </div>

      <div class="tab-panel">
        <div class="card">
          <h3>Tarifs des Cours</h3>
          <?= block_richtext($blocks, 'overview_fr') ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ================================================================
     Tuition table
     ================================================================ -->
<?php if ($tuition): ?>
<section class="section" id="fees">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="eyebrow">Fees</p>
      <h2>Course Programs &amp; Pricing</h2>
    </div>
    <div class="table-wrap reveal">
      <table class="tuition-table">
        <thead>
          <tr>
            <?php foreach ($columns as $column): ?>
              <th><?= h($column) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tuition as $row): ?>
            <tr>
              <td><?= h($row['name_en']) ?></td>
              <td><?= h($row['name_fr']) ?></td>
              <td><?= h($row['hours_note']) ?></td>
              <td><?= h($row['price_dh']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php $feeNotes = content_item_texts('academics', 'fee_notes'); ?>
    <?php if ($feeNotes): ?>
      <div class="fee-notes reveal">
        <?php foreach ($feeNotes as $feeNote): ?>
          <div><?= h($feeNote) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- ================================================================
     French programme anchor
     ================================================================ -->
<section class="section section-navy" id="french">
  <div class="wrap" style="text-align:center; max-width:820px;">
    <p class="eyebrow reveal">Programme de français</p>
    <h2 class="reveal">French Classes &amp; Communication</h2>
    <p class="reveal" style="color:#c9dcf3;">
      Our academic team can advise on French as well as English courses, from beginner
      conversation to professional and exam preparation. Contact us to confirm current
      French offerings and levels.
    </p>
    <a class="btn btn-white reveal" href="/contact.php">Ask about French courses</a>
  </div>
</section>

<!-- ================================================================
     Call to action
     ================================================================ -->
<section class="section">
  <div class="wrap" style="text-align:center; max-width:760px;">
    <p class="eyebrow reveal">Next step</p>
    <h2 class="reveal">Ready to enroll?</h2>
    <p class="reveal" style="color:var(--muted);">
      Start the registration process and our team will help place you in the right level.
    </p>
    <a class="btn btn-navy reveal" href="/registration.php">Go to Registration</a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
