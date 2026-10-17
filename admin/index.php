<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pageTitle = 'Dashboard';
$activeMenu = 'dashboard';

$postCount = (int) db()->query("SELECT COUNT(*) c FROM posts")->fetch()['c'];
$publishedCount = (int) db()->query("SELECT COUNT(*) c FROM posts WHERE status='published'")->fetch()['c'];
$messageCount = (int) db()->query("SELECT COUNT(*) c FROM messages")->fetch()['c'];
$unreadMessages = (int) db()->query("SELECT COUNT(*) c FROM messages WHERE is_read=0")->fetch()['c'];
$registrationCount = (int) db()->query("SELECT COUNT(*) c FROM registrations")->fetch()['c'];
$unreadRegistrations = (int) db()->query("SELECT COUNT(*) c FROM registrations WHERE is_read=0")->fetch()['c'];
$openPositions = (int) db()->query("SELECT COUNT(*) c FROM career_openings WHERE status='open'")->fetch()['c'];

$recentMessages = db()->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentRegistrations = db()->query("SELECT * FROM registrations ORDER BY created_at DESC LIMIT 5")->fetchAll();

require __DIR__ . '/partials/header.php';
?>

<div class="stat-grid">
  <div class="stat-card">
    <div class="num"><?= $publishedCount ?> / <?= $postCount ?></div>
    <div class="label">Posts published</div>
  </div>
  <div class="stat-card">
    <div class="num"><?= $messageCount ?></div>
    <div class="label">Contact messages (<?= $unreadMessages ?> unread)</div>
  </div>
  <div class="stat-card">
    <div class="num"><?= $registrationCount ?></div>
    <div class="label">Registrations (<?= $unreadRegistrations ?> unread)</div>
  </div>
  <div class="stat-card">
    <div class="num"><?= $openPositions ?></div>
    <div class="label">Open career positions</div>
  </div>
</div>

<div class="card">
  <div class="card-header"><h2>Edit a page</h2></div>
  <ul class="page-list">
    <li><a href="/admin/page-edit.php?page=home"><h3>Home</h3><p>Hero slides, highlights, FAQs, partners, stats — all reorderable, plus site text</p></a></li>
    <li><a href="/admin/page-edit.php?page=about"><h3>About</h3><p>Mission, history, initiatives, network of centers</p></a></li>
    <li><a href="/admin/academics.php"><h3>Programs &amp; Fees</h3><p>Tuition &amp; fees table, fee notes, course information</p></a></li>
    <li><a href="/admin/programs.php"><h3>Program Cards</h3><p>The course cards shown on the Programs page</p></a></li>
    <li><a href="/admin/careers.php"><h3>Careers</h3><p>Overview, how-to-apply checklist, FAQs, benefits, job openings</p></a></li>
    <li><a href="/admin/page-edit.php?page=community"><h3>Clubs &amp; Community</h3><p>Activities, clubs, and the Community Service Club</p></a></li>
    <li><a href="/admin/page-edit.php?page=contact"><h3>Contact</h3><p>Intro text (address/phone in Settings)</p></a></li>
  </ul>
</div>

<div class="card">
  <div class="card-header">
    <h2>Recent messages</h2>
    <a href="/admin/messages.php" class="btn btn-ghost btn-sm">View all</a>
  </div>
  <?php if (!$recentMessages): ?>
    <div class="empty-state">No messages yet.</div>
  <?php else: ?>
    <table class="admin-table">
      <thead><tr><th>Name</th><th>Email</th><th>Received</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($recentMessages as $m): ?>
          <tr>
            <td><?= h($m['name']) ?> <?php if (!$m['is_read']): ?><span class="pill pill-unread">New</span><?php endif; ?></td>
            <td><?= h($m['email']) ?></td>
            <td><?= h(date('M j, Y g:ia', strtotime($m['created_at']))) ?></td>
            <td><a href="/admin/messages.php">View</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<div class="card">
  <div class="card-header">
    <h2>Recent registrations</h2>
    <a href="/admin/registrations.php" class="btn btn-ghost btn-sm">View all</a>
  </div>
  <?php if (!$recentRegistrations): ?>
    <div class="empty-state">No registrations yet.</div>
  <?php else: ?>
    <table class="admin-table">
      <thead><tr><th>Name</th><th>Program</th><th>Received</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($recentRegistrations as $r): ?>
          <tr>
            <td><?= h($r['full_name']) ?> <?php if (!$r['is_read']): ?><span class="pill pill-unread">New</span><?php endif; ?></td>
            <td><?= h($r['program_interest'] ?: '—') ?></td>
            <td><?= h(date('M j, Y g:ia', strtotime($r['created_at']))) ?></td>
            <td><a href="/admin/registrations.php">View</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
