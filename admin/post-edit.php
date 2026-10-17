<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_login();

$pdo = db();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$post = null;

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    if (!$post) {
        header('Location: /admin/posts.php');
        exit;
    }
}

$pageTitle = $post ? 'Edit post' : 'New post';
$activeMenu = 'posts';
$token = csrf_token();
$error = '';

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: 'post-' . time();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired — please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
        $slug = trim($_POST['slug'] ?? '') ?: slugify($title);

        if ($title === '') {
            $error = 'Please give the post a title.';
        } else {
            try {
                $imagePath = handle_image_upload($_FILES['image'] ?? null);
                $publishedAt = $status === 'published' ? ($post['published_at'] ?? date('Y-m-d H:i:s')) : null;

                if ($post) {
                    $sql = 'UPDATE posts SET title=?, slug=?, excerpt=?, body=?, status=?, published_at=?';
                    $params = [$title, $slug, $excerpt, $body, $status, $publishedAt];
                    if ($imagePath) {
                        $sql .= ', image_path=?';
                        $params[] = $imagePath;
                    }
                    $sql .= ' WHERE id=?';
                    $params[] = $post['id'];
                    $pdo->prepare($sql)->execute($params);
                    $id = $post['id'];
                } else {
                    $stmt = $pdo->prepare(
                        'INSERT INTO posts (title, slug, excerpt, body, image_path, status, published_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?)'
                    );
                    $stmt->execute([$title, $slug, $excerpt, $body, $imagePath, $status, $publishedAt]);
                    $id = (int) $pdo->lastInsertId();
                }

                header('Location: /admin/post-edit.php?id=' . $id . '&saved=1');
                exit;
            } catch (Throwable $e) {
                $error = 'Could not save: ' . $e->getMessage();
            }
        }
    }
}

// After a POST redirect (or on first GET), make sure $post reflects the current DB row.
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
    $stmt->execute([$id]);
    $post = $stmt->fetch() ?: $post;
}

require __DIR__ . '/partials/header.php';
?>

<?php if (!empty($_GET['saved'])): ?><div class="alert alert-success">Post saved.</div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

<div class="card">
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">

    <div class="form-group">
      <label>Title</label>
      <input type="text" name="title" value="<?= h($post['title'] ?? '') ?>" required>
    </div>

    <div class="form-group">
      <label>URL slug</label>
      <div class="hint">Leave blank to generate automatically from the title.</div>
      <input type="text" name="slug" value="<?= h($post['slug'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label>Excerpt</label>
      <div class="hint">Short summary shown on the homepage news list.</div>
      <textarea name="excerpt" rows="2"><?= h($post['excerpt'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
      <label>Body</label>
      <textarea name="body" rows="8"><?= h($post['body'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
      <label>Cover image</label>
      <div class="image-field">
        <div class="preview" id="preview-post-image">
          <?php if (!empty($post['image_path'])): ?>
            <img src="<?= h(asset($post['image_path'])) ?>" alt="">
          <?php endif; ?>
        </div>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" data-preview="preview-post-image">
      </div>
    </div>

    <div class="form-group">
      <label>Status</label>
      <select name="status">
        <option value="draft" <?= ($post['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
        <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
      </select>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Save post</button>
      <a href="/admin/posts.php" class="btn btn-ghost">Back to posts</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
