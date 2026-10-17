<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pdo = db();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$job = null;

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM career_openings WHERE id = ?');
    $stmt->execute([$id]);
    $job = $stmt->fetch();
    if (!$job) {
        header('Location: /admin/career-openings.php');
        exit;
    }
}

$pageTitle = $job ? 'Edit opening' : 'New opening';
$activeMenu = 'openings';
$token = csrf_token();
$error = '';

$defaultLocation = get_setting('city_name', '') !== '' ? get_setting('city_name') . ', Morocco' : 'Morocco';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired — please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $location = trim($_POST['location'] ?? '') ?: $defaultLocation;
        $employmentType = in_array($_POST['employment_type'] ?? '', ['full-time', 'part-time', 'contract'], true)
            ? $_POST['employment_type'] : 'full-time';
        $description = trim($_POST['description'] ?? '');
        $requirements = trim($_POST['requirements'] ?? '');
        $deadline = trim($_POST['application_deadline'] ?? '') ?: null;
        $status = ($_POST['status'] ?? 'open') === 'closed' ? 'closed' : 'open';

        if ($title === '') {
            $error = 'Please give the opening a title.';
        } else {
            try {
                if ($job) {
                    $pdo->prepare(
                        'UPDATE career_openings SET title=?, department=?, location=?, employment_type=?,
                         description=?, requirements=?, application_deadline=?, status=? WHERE id=?'
                    )->execute([$title, $department, $location, $employmentType, $description, $requirements, $deadline, $status, $job['id']]);
                    $id = $job['id'];
                } else {
                    $nextOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order),0)+1 n FROM career_openings')->fetch()['n'];
                    $stmt = $pdo->prepare(
                        'INSERT INTO career_openings (title, department, location, employment_type, description, requirements, application_deadline, status, sort_order)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    );
                    $stmt->execute([$title, $department, $location, $employmentType, $description, $requirements, $deadline, $status, $nextOrder]);
                    $id = (int) $pdo->lastInsertId();
                }

                header('Location: /admin/opening-edit.php?id=' . $id . '&saved=1');
                exit;
            } catch (Throwable $e) {
                $error = 'Could not save: ' . $e->getMessage();
            }
        }
    }
}

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM career_openings WHERE id = ?');
    $stmt->execute([$id]);
    $job = $stmt->fetch() ?: $job;
}

require __DIR__ . '/partials/header.php';
?>

<?php if (!empty($_GET['saved'])): ?><div class="alert alert-success">Opening saved.</div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

<div class="card">
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">

    <div class="form-group">
      <label>Job title</label>
      <input type="text" name="title" value="<?= h($job['title'] ?? '') ?>" placeholder="e.g. English Language Teacher" required>
    </div>

    <div class="form-group">
      <label>Department</label>
      <input type="text" name="department" value="<?= h($job['department'] ?? '') ?>" placeholder="e.g. Academic">
    </div>

    <div class="form-group">
      <label>Location</label>
      <input type="text" name="location" value="<?= h($job['location'] ?? $defaultLocation) ?>">
    </div>

    <div class="form-group">
      <label>Employment type</label>
      <select name="employment_type">
        <?php $type = $job['employment_type'] ?? 'full-time'; ?>
        <option value="full-time" <?= $type === 'full-time' ? 'selected' : '' ?>>Full-time</option>
        <option value="part-time" <?= $type === 'part-time' ? 'selected' : '' ?>>Part-time</option>
        <option value="contract" <?= $type === 'contract' ? 'selected' : '' ?>>Contract</option>
      </select>
    </div>

    <div class="form-group">
      <label>Description</label>
      <div class="hint">What the role involves day to day.</div>
      <textarea name="description" rows="4"><?= h($job['description'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
      <label>Requirements</label>
      <div class="hint">Certifications, experience, language proficiency, etc.</div>
      <textarea name="requirements" rows="4"><?= h($job['requirements'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
      <label>Application deadline</label>
      <div class="hint">Optional — leave blank if the role stays open until filled.</div>
      <input type="date" name="application_deadline" value="<?= h($job['application_deadline'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label>Status</label>
      <select name="status">
        <?php $status = $job['status'] ?? 'open'; ?>
        <option value="open" <?= $status === 'open' ? 'selected' : '' ?>>Open</option>
        <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed</option>
      </select>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Save opening</button>
      <a href="/admin/career-openings.php" class="btn btn-ghost">Back to openings</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
