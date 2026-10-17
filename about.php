<?php
require_once __DIR__ . '/includes/functions.php';

$blocks = get_blocks_for_page('about');
$pageTitle   = block_text($blocks, 'page_title', 'About Us');
$pageCaption = block_text($blocks, 'page_caption', 'Bringing Morocco and the United States closer together');
$activeNav = 'about';
require __DIR__ . '/includes/header.php';

$siteName = get_setting('site_name', 'American Language Center');
$centers  = db()->query('SELECT * FROM network_centers ORDER BY sort_order ASC')->fetchAll();
$initiativeItems = get_content_items('about', 'initiatives');
?>

<!-- ================================================================
     Mission
     ================================================================ -->
<section class="section">
  <div class="wrap split">
    <div class="split-copy reveal">
      <p class="eyebrow">Who we are</p>
      <h2>Our Mission</h2>
      <?= block_richtext($blocks, 'mission') ?>
    </div>
    <div class="split-media reveal">
      <?php $missionImage = block_image($blocks, 'mission_image', 'assets/img/about.svg'); ?>
      <?php if ($missionImage): ?>
        <div class="media-frame wide rounded-lg"><img src="<?= h(asset($missionImage)) ?>" alt="<?= h($siteName) ?>"></div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ================================================================
     ALC / ACA initiatives
     ================================================================ -->
<?php if ($initiativeItems): ?>
<section class="section section-grey">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="eyebrow">Beyond the classroom</p>
      <h2>ALC &amp; ACA Initiatives</h2>
      <?= block_richtext($blocks, 'initiatives_intro') ?>
    </div>

    <div class="wrap-narrow" style="margin:0 auto;">
      <?php foreach ($initiativeItems as $i => $initiative):
          $title = trim((string) ($initiative['title'] ?? ''));
          $body  = (string) ($initiative['content'] ?? '');
          $image = (string) ($initiative['image_path'] ?? '');
          $flip  = $i % 2 === 1;
      ?>
        <div class="initiative <?= $flip ? 'flip' : '' ?> reveal">
          <div>
            <?php if ($title): ?><h3><?= h($title) ?></h3><?php endif; ?>
            <?php if ($body): ?><?= richtext($body) ?><?php endif; ?>
          </div>
          <div class="initiative-media">
            <?php if ($image): ?>
              <div class="media-frame"><img src="<?= h(asset($image)) ?>" alt="<?= h($title ?: $siteName) ?>" loading="lazy"></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ================================================================
     History
     ================================================================ -->
<section class="section">
  <div class="wrap-narrow">
    <div class="reveal">
      <p class="eyebrow">Our story</p>
      <h2>History of the ALC Network</h2>
      <?= block_richtext($blocks, 'history') ?>
    </div>
  </div>
</section>

<!-- ================================================================
     Network of centers
     ================================================================ -->
<?php if ($centers): ?>
<section class="section section-navy">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="eyebrow">One network, twelve cities</p>
      <h2>A Network of Twelve Cities</h2>
      <?= block_richtext($blocks, 'network_intro') ?>
    </div>
    <div class="col-4">
      <?php foreach ($centers as $center): ?>
        <?php if (!empty($center['website_url'])): ?>
          <a class="city-card reveal" href="<?= h($center['website_url']) ?>" target="_blank" rel="noopener">
            <?php if ($center['image_path']): ?>
              <img src="<?= h(asset($center['image_path'])) ?>" alt="ALC <?= h($center['name']) ?>" loading="lazy">
            <?php endif; ?>
            <span><?= h($center['name']) ?></span>
          </a>
        <?php else: ?>
          <div class="city-card reveal">
            <?php if ($center['image_path']): ?>
              <img src="<?= h(asset($center['image_path'])) ?>" alt="ALC <?= h($center['name']) ?>" loading="lazy">
            <?php endif; ?>
            <span><?= h($center['name']) ?></span>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
