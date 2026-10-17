<?php
/**
 * Shared header. Expects $pageTitle and $activeNav to be set by the including page.
 * Optionally set $pageCaption for the interior-page title band.
 */
require_once __DIR__ . '/functions.php';

$siteName    = get_setting('site_name', 'American Language Center');
$pageTitle   = $pageTitle ?? $siteName;
$pageCaption = $pageCaption ?? '';
$activeNav   = $activeNav ?? '';

// Primary navigation — the "Programs" item opens a dropdown of course families.
$nav = [
    'home'         => ['label' => 'Home',         'href' => '/'],
    'registration' => ['label' => 'Registration', 'href' => '/registration.php'],
    'academics'    => ['label' => 'Programs',     'href' => '/academics.php'],
    'community'    => ['label' => 'Clubs',        'href' => '/community.php'],
    'careers'      => ['label' => 'Join',         'href' => '/careers.php'],
    'about'        => ['label' => 'About',        'href' => '/about.php'],
    'contact'      => ['label' => 'Contact',      'href' => '/contact.php'],
];

$programMenu = [
    ['label' => 'English Program',      'href' => '/academics.php#english'],
    ['label' => 'French Program',       'href' => '/academics.php#french'],
    ['label' => 'General English',      'href' => '/academics.php#english'],
    ['label' => 'Business English',     'href' => '/academics.php#english'],
    ['label' => 'Conversation Classes', 'href' => '/academics.php#english'],
    ['label' => 'Exam Prep (TOEFL/IELTS/TOEIC)', 'href' => '/academics.php#english'],
    ['label' => 'Kids, Teens & Adults', 'href' => '/academics.php#levels'],
    ['label' => 'Tuition & Fees',       'href' => '/academics.php#fees'],
];

$examResults = get_setting('exam_results_url', '');
$phone   = get_setting('phone', '');
$email   = get_setting('email', '');
$address = get_setting('address', '');
$mapHref = maps_href($address);
?><!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($pageTitle) ?> — <?= h($siteName) ?></title>
<meta name="description" content="<?= h(get_setting('meta_description', get_setting('site_tagline', $siteName))) ?>">
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/site.css">
<script>
  // Apply the saved theme before paint (no flash of the wrong theme).
  (function () {
    try {
      var t = localStorage.getItem("alc-theme");
      if (t === "dark") { document.documentElement.setAttribute("data-theme", "dark"); }
    } catch (e) {}
  })();
</script>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header" id="siteHeader">
  <div class="topbar">
    <div class="wrap topbar-inner">
      <ul class="topbar-list">
        <?php if ($address): ?>
          <li><?= icon('map') ?><a href="<?= h($mapHref) ?>" target="_blank" rel="noopener"><?= h($address) ?></a></li>
        <?php endif; ?>
        <?php if ($phone): ?>
          <li><?= icon('phone') ?><a href="tel:<?= h(tel_href($phone)) ?>"><?= h($phone) ?></a></li>
        <?php endif; ?>
        <?php if ($email): ?>
          <li><?= icon('envelope') ?><a href="mailto:<?= h($email) ?>"><?= h($email) ?></a></li>
        <?php endif; ?>
      </ul>
      <div class="topbar-right">
        <div class="topbar-social">
          <?php if ($examResults = get_setting('exam_results_url')): ?>
            <a href="<?= h($examResults) ?>" target="_blank" rel="noopener">Exam Results</a>
          <?php endif; ?>
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
    </div>
  </div>

  <div class="wrap main-nav">
    <a class="brand" href="/">
      <img src="/assets/img/logo.svg" alt="<?= h($siteName) ?>">
    </a>

    <nav class="primary-nav" aria-label="Primary">
      <ul>
        <?php foreach ($nav as $key => $item): ?>
          <?php $isActive = $activeNav === $key; ?>
          <?php if ($key === 'academics'): ?>
            <li class="has-drop <?= $isActive ? 'is-active' : '' ?>">
              <a class="nav-drop-toggle" href="<?= h($item['href']) ?>" role="button" aria-expanded="false">
                <?= h($item['label']) ?><span class="chev ico"><?= icon('chevron', '') ?></span>
              </a>
              <ul class="nav-drop">
                <?php foreach ($programMenu as $sub): ?>
                  <li><a href="<?= h($sub['href']) ?>"><?= h($sub['label']) ?></a></li>
                <?php endforeach; ?>
              </ul>
            </li>
          <?php else: ?>
            <li class="<?= $isActive ? 'is-active' : '' ?>">
              <a href="<?= h($item['href']) ?>"><?= h($item['label']) ?></a>
            </li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </nav>

    <div class="nav-tools">
      <div class="lang-switch">
        <button type="button" aria-haspopup="true" aria-expanded="false">
          <span class="flag">🇬🇧</span> EN <?= icon('chevron', '') ?>
        </button>
        <ul class="lang-menu">
          <li><a href="/" class="is-active"><span class="flag">🇬🇧</span> English</a></li>
          <li><a href="/" lang="fr"><span class="flag">🇫🇷</span> Français</a></li>
          <li><a href="/" lang="ar" dir="rtl"><span class="flag">🇲🇦</span> العربية</a></li>
        </ul>
      </div>

      <button class="theme-toggle" type="button" aria-label="Toggle dark mode" aria-pressed="false">
        <span class="knob">🌙</span>
      </button>

      <button class="nav-toggle" id="navToggle" aria-label="Open navigation" aria-expanded="false" aria-controls="mobileDrawer">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>

<div class="mobile-drawer" id="mobileDrawer" aria-hidden="true">
  <div class="drawer-head">
    <img src="/assets/img/logo-white.svg" alt="<?= h($siteName) ?>">
    <button class="drawer-close" id="drawerClose" aria-label="Close navigation"><?= icon('close', '') ?></button>
  </div>
  <nav class="drawer-nav" aria-label="Mobile">
    <?php foreach ($nav as $key => $item): ?>
      <a href="<?= h($item['href']) ?>" class="<?= $activeNav === $key ? 'is-active' : '' ?>"><?= h($item['label']) ?></a>
      <?php if ($key === 'academics'): ?>
        <div class="drawer-group-title">Programs</div>
        <?php foreach ($programMenu as $sub): ?>
          <a href="<?= h($sub['href']) ?>" style="padding-left:24px; font-weight:400;"><?= h($sub['label']) ?></a>
        <?php endforeach; ?>
      <?php endif; ?>
    <?php endforeach; ?>
    <?php if ($examResults = get_setting('exam_results_url')): ?>
      <a href="<?= h($examResults) ?>" target="_blank" rel="noopener">Exam Results ↗</a>
    <?php endif; ?>
  </nav>
  <div class="drawer-contact">
    <?php if ($address): ?><p><?= icon('map', '') ?><span><?= h($address) ?></span></p><?php endif; ?>
    <?php if ($phone): ?><p><?= icon('phone', '') ?><a href="tel:<?= h(tel_href($phone)) ?>"><?= h($phone) ?></a></p><?php endif; ?>
    <?php if ($email): ?><p><?= icon('envelope', '') ?><a href="mailto:<?= h($email) ?>"><?= h($email) ?></a></p><?php endif; ?>
  </div>
</div>
<div class="drawer-backdrop" id="drawerBackdrop"></div>

<?php if ($activeNav !== 'home'): ?>
<div class="page-hero">
  <div class="wrap">
    <p class="crumbs"><a href="/">Home</a> / <?= h($pageTitle) ?></p>
    <h1><?= h($pageTitle) ?></h1>
    <?php if ($pageCaption): ?><span class="caption"><?= h($pageCaption) ?></span><?php endif; ?>
  </div>
</div>
<?php endif; ?>

<main id="main">
