<?php
$footerNav = [
    ['label' => 'Home',         'href' => '/'],
    ['label' => 'About',        'href' => '/about.php'],
    ['label' => 'Registration', 'href' => '/registration.php'],
    ['label' => 'Programs',     'href' => '/academics.php'],
    ['label' => 'Clubs',        'href' => '/community.php'],
    ['label' => 'Join our team','href' => '/careers.php'],
    ['label' => 'Contact',      'href' => '/contact.php'],
];
$footerPrograms = [
    ['label' => 'English Program',  'href' => '/academics.php#english'],
    ['label' => 'French Program',   'href' => '/academics.php#french'],
    ['label' => 'Kids & Teens',     'href' => '/academics.php#levels'],
    ['label' => 'Exam Preparation', 'href' => '/academics.php#english'],
    ['label' => 'Tuition & Fees',   'href' => '/academics.php#fees'],
];

$footerAddress = get_setting('address', '');
$footerPhone   = get_setting('phone', '');
$footerEmail   = get_setting('email', '');
$footerNote    = get_setting('footer_note', '© ' . date('Y') . ' ' . get_setting('site_name', 'American Language Center') . '. All rights reserved.');
$mapHref       = maps_href($footerAddress);
?>
</main>

<footer class="site-footer">
  <div class="wrap">
    <div class="footer-grid">
      <div class="footer-brand">
        <img src="/assets/img/logo-white.svg" alt="<?= h(get_setting('site_name', 'American Language Center')) ?>">
        <p class="footer-about"><?= h(get_setting('site_tagline', 'Promoting understanding between the peoples of Morocco and the United States through language and culture.')) ?></p>
        <div class="footer-social">
          <?php if ($fb = get_setting('facebook_url')): ?>
            <a href="<?= h($fb) ?>" target="_blank" rel="noopener" title="Facebook" aria-label="Facebook"><?= icon('facebook', '') ?></a>
          <?php endif; ?>
          <?php if ($ig = get_setting('instagram_url')): ?>
            <a href="<?= h($ig) ?>" target="_blank" rel="noopener" title="Instagram" aria-label="Instagram"><?= icon('instagram', '') ?></a>
          <?php endif; ?>
          <?php if ($li = get_setting('linkedin_url')): ?>
            <a href="<?= h($li) ?>" target="_blank" rel="noopener" title="LinkedIn" aria-label="LinkedIn"><?= icon('linkedin', '') ?></a>
          <?php endif; ?>
          <?php if ($yt = get_setting('youtube_url')): ?>
            <a href="<?= h($yt) ?>" target="_blank" rel="noopener" title="YouTube" aria-label="YouTube"><?= icon('youtube', '') ?></a>
          <?php endif; ?>
          <?php if ($wa = whatsapp_href(get_setting('whatsapp_number'))): ?>
            <a href="<?= h($wa) ?>" target="_blank" rel="noopener" title="WhatsApp" aria-label="WhatsApp"><?= icon('whatsapp', '') ?></a>
          <?php endif; ?>
        </div>
      </div>

      <div>
        <h3>Quick Links</h3>
        <ul class="footer-menu">
          <?php foreach ($footerNav as $item): ?>
            <li><a href="<?= h($item['href']) ?>"><?= h($item['label']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div>
        <h3>Programs</h3>
        <ul class="footer-menu">
          <?php foreach ($footerPrograms as $item): ?>
            <li><a href="<?= h($item['href']) ?>"><?= h($item['label']) ?></a></li>
          <?php endforeach; ?>
          <?php if ($examResults = get_setting('exam_results_url')): ?>
            <li><a href="<?= h($examResults) ?>" target="_blank" rel="noopener">Exam Results ↗</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <div>
        <h3>Contact</h3>
        <?php if ($footerAddress): ?>
          <p class="footer-contact-item"><?= icon('map') ?><a href="<?= h($mapHref) ?>" target="_blank" rel="noopener"><?= h($footerAddress) ?></a></p>
        <?php endif; ?>
        <?php if ($footerPhone): ?>
          <p class="footer-contact-item"><?= icon('phone') ?><a href="tel:<?= h(tel_href($footerPhone)) ?>"><?= h($footerPhone) ?></a></p>
        <?php endif; ?>
        <?php if ($footerEmail): ?>
          <p class="footer-contact-item"><?= icon('envelope') ?><a href="mailto:<?= h($footerEmail) ?>"><?= h($footerEmail) ?></a></p>
        <?php endif; ?>
        <?php if ($hours = get_setting('office_hours')): ?>
          <p class="footer-contact-item"><?= icon('clock') ?><span><?= h($hours) ?></span></p>
        <?php endif; ?>
      </div>
    </div>

    <div class="copyright-bar">
      <div class="copyright-inner">
        <p><?= h($footerNote) ?></p>
        <p>Member of the <a href="https://aca.org.ma" target="_blank" rel="noopener">American Cultural Association</a></p>
      </div>
    </div>
  </div>
</footer>

<button class="to-top" id="toTop" aria-label="Back to top">&uarr;</button>

<script src="/assets/js/site.js"></script>
</body>
</html>
