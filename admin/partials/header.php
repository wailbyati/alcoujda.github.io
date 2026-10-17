<?php
/**
 * Expects $pageTitle and $activeMenu to be set before including.
 * Requires includes/auth.php + includes/functions.php already loaded,
 * and require_login() already called by the including page.
 */
$admin = current_admin();
$adminSiteName = get_setting('site_name', 'American Language Center');

$unreadMessages = (int) db()->query("SELECT COUNT(*) c FROM messages WHERE is_read = 0")->fetch()['c'];
$unreadRegistrations = (int) db()->query("SELECT COUNT(*) c FROM registrations WHERE is_read = 0")->fetch()['c'];
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($pageTitle ?? 'Dashboard') ?> — <?= h($adminSiteName) ?> Admin</title>
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body>
<div class="admin-shell">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <img src="/assets/img/logo-white.svg" alt="<?= h($adminSiteName) ?>">
      <span>Admin</span>
    </div>
    <ul class="sidebar-nav">
      <li><a href="/admin/index.php" class="<?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>

      <li class="nav-section">Site Pages</li>
      <li><a href="/admin/page-edit.php?page=home" class="<?= ($activeMenu ?? '') === 'home' ? 'active' : '' ?>">Home</a></li>
      <li><a href="/admin/page-edit.php?page=about" class="<?= ($activeMenu ?? '') === 'about' ? 'active' : '' ?>">About</a></li>
      <li><a href="/admin/academics.php" class="<?= ($activeMenu ?? '') === 'academics' ? 'active' : '' ?>">Programs &amp; Fees</a></li>
      <li><a href="/admin/programs.php" class="<?= ($activeMenu ?? '') === 'programs' ? 'active' : '' ?>">Program Cards</a></li>
      <li><a href="/admin/careers.php" class="<?= ($activeMenu ?? '') === 'careers' ? 'active' : '' ?>">Careers</a></li>
      <li><a href="/admin/career-openings.php" class="<?= ($activeMenu ?? '') === 'openings' ? 'active' : '' ?>">Career Openings</a></li>
      <li><a href="/admin/page-edit.php?page=community" class="<?= ($activeMenu ?? '') === 'community' ? 'active' : '' ?>">Clubs &amp; Community</a></li>
      <li><a href="/admin/page-edit.php?page=contact" class="<?= ($activeMenu ?? '') === 'contact' ? 'active' : '' ?>">Contact</a></li>
      <li><a href="/admin/page-edit.php?page=registration" class="<?= ($activeMenu ?? '') === 'registration' ? 'active' : '' ?>">Registration</a></li>

      <li class="nav-section">Content</li>
      <li><a href="/admin/posts.php" class="<?= ($activeMenu ?? '') === 'posts' ? 'active' : '' ?>">Posts / News</a></li>
      <li><a href="/admin/network.php" class="<?= ($activeMenu ?? '') === 'network' ? 'active' : '' ?>">Network of Centers</a></li>

      <li class="nav-section">Inbox</li>
      <li><a href="/admin/messages.php" class="<?= ($activeMenu ?? '') === 'messages' ? 'active' : '' ?>">Messages
        <?php if ($unreadMessages): ?><span class="badge"><?= $unreadMessages ?></span><?php endif; ?>
      </a></li>
      <li><a href="/admin/registrations.php" class="<?= ($activeMenu ?? '') === 'registrations' ? 'active' : '' ?>">Registrations
        <?php if ($unreadRegistrations): ?><span class="badge"><?= $unreadRegistrations ?></span><?php endif; ?>
      </a></li>

      <li class="nav-section">System</li>
      <li><a href="/admin/settings.php" class="<?= ($activeMenu ?? '') === 'settings' ? 'active' : '' ?>">Settings</a></li>
      <li><a href="/index.php" target="_blank">View live site ↗</a></li>
    </ul>
  </aside>

  <div class="main-col">
    <div class="topbar">
      <div style="display:flex; align-items:center; gap:14px;">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle menu">☰</button>
        <h1><?= h($pageTitle ?? 'Dashboard') ?></h1>
      </div>
      <div class="who">
        Signed in as <strong><?= h($admin['display_name'] ?: $admin['username']) ?></strong>
        <a href="/admin/logout.php">Log out</a>
      </div>
    </div>
    <div class="content">
