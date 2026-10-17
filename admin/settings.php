<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pageTitle = 'Settings';
$activeMenu = 'settings';
$token = csrf_token();
$success = '';
$error = '';
$pdo = db();
$admin = current_admin();

$settingKeys = [
    'site_name'        => 'Site name',
    'city_name'        => 'City name',
    'site_tagline'     => 'Tagline',
    'meta_description' => 'Default meta description',
    'address'          => 'Address',
    'phone'            => 'Phone',
    'email'            => 'General email',
    'office_hours'     => 'Office hours',
    'careers_email'    => 'Careers application email',
    'facebook_url'     => 'Facebook URL',
    'instagram_url'    => 'Instagram URL',
    'linkedin_url'     => 'LinkedIn URL',
    'youtube_url'      => 'YouTube URL',
    'whatsapp_number'  => 'WhatsApp number (used for the WhatsApp link)',
    'exam_results_url' => 'Exam results portal URL',
    'footer_note'      => 'Footer copyright line',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired — please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'save_settings') {
            $stmt = $pdo->prepare(
                'INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
            );
            foreach (array_keys($settingKeys) as $key) {
                $stmt->execute([$key, trim($_POST[$key] ?? '')]);
            }
            $success = 'Settings saved.';

        } elseif ($action === 'change_password') {
            $current = (string) ($_POST['current_password'] ?? '');
            $new = (string) ($_POST['new_password'] ?? '');
            $confirm = (string) ($_POST['confirm_password'] ?? '');

            $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE id = ?');
            $stmt->execute([$admin['id']]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($current, $user['password_hash'])) {
                $error = 'Current password is incorrect.';
            } elseif (strlen($new) < 8) {
                $error = 'New password must be at least 8 characters.';
            } elseif ($new !== $confirm) {
                $error = 'New password and confirmation do not match.';
            } else {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')->execute([$hash, $admin['id']]);
                $success = 'Password updated.';
            }
        } elseif ($action === 'resync_content') {
            try {
                $filled = resync_content_items([
                    ['home', 'hero'],
                    ['home', 'offers'],
                    ['home', 'about_points'],
                    ['home', 'stat_band'],
                    ['home', 'faqs'],
                    ['home', 'partners'],
                    ['about', 'initiatives'],
                    ['academics', 'fee_notes'],
                    ['careers', 'apply_points'],
                    ['community', 'clubs'],
                    ['community', 'csc_points'],
                    ['registration', 'before_register'],
                ]);
                $success = $filled > 0
                    ? 'Content re-synced — ' . $filled . ' section(s) were re-populated from the page blocks.'
                    : 'Content is already in sync — every list section already has its items.';
            } catch (PDOException $e) {
                $error = 'Could not re-sync content: ' . $e->getMessage();
            }
        }
    }
}

$settings = get_settings();

require __DIR__ . '/partials/header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-header"><h2>Site information</h2></div>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <input type="hidden" name="action" value="save_settings">
    <?php foreach ($settingKeys as $key => $label): ?>
      <div class="form-group">
        <label for="setting-<?= h($key) ?>"><?= h($label) ?></label>
        <input type="text" id="setting-<?= h($key) ?>" name="<?= h($key) ?>" value="<?= h($settings[$key] ?? '') ?>">
      </div>
    <?php endforeach; ?>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Save settings</button>
    </div>
  </form>
</div>

<div class="card">
  <div class="card-header"><h2>Change your password</h2></div>
  <form method="post" style="max-width:420px;">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <input type="hidden" name="action" value="change_password">
    <div class="form-group">
      <label>Current password</label>
      <input type="password" name="current_password" required>
    </div>
    <div class="form-group">
      <label>New password</label>
      <div class="hint">At least 8 characters.</div>
      <input type="password" name="new_password" required minlength="8">
    </div>
    <div class="form-group">
      <label>Confirm new password</label>
      <input type="password" name="confirm_password" required minlength="8">
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Update password</button>
    </div>
  </form>
</div>

<div class="card">
  <div class="card-header"><h2>Content maintenance</h2></div>
  <form method="post" style="max-width:560px;">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <input type="hidden" name="action" value="resync_content">
    <p class="hint" style="margin-bottom:14px;">
      If a page looks empty even though its blocks are listed in the dashboard,
      its list sections (hero slides, bullets, stats, fee notes…) were migrated
      before the content was imported. This button re-populates any section that
      is <strong>currently empty</strong> from the page blocks. Existing items
      are never changed or deleted.
    </p>
    <button type="submit" class="btn btn-gold">Re-sync content</button>
  </form>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
