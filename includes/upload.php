<?php
/**
 * Handle a single uploaded image file (a $_FILES[...] sub-array with
 * name/tmp_name/error/size keys), save it under public/assets/img/uploads/,
 * and return its site-relative path (e.g. "assets/img/uploads/2026/09/abc123.jpg")
 * or null if no file was uploaded.
 *
 * @throws RuntimeException on an actual upload error (too big, bad type)
 */
function handle_image_upload(?array $file): ?string
{
    if (!$file || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed (error code ' . $file['error'] . ').');
    }

    $maxBytes = 6 * 1024 * 1024; // 6MB
    if ($file['size'] > $maxBytes) {
        throw new RuntimeException('Image is too large — please keep it under 6MB.');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Please upload a JPG, PNG, WEBP, or GIF image.');
    }

    $ext = $allowed[$mime];
    $subdir = 'assets/img/uploads/' . date('Y/m');
    $destDir = __DIR__ . '/../' . $subdir;

    if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
        throw new RuntimeException('Could not create upload directory.');
    }

    $filename = bin2hex(random_bytes(8)) . '.' . $ext;
    $destPath = $destDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        throw new RuntimeException('Could not save the uploaded file.');
    }

    return $subdir . '/' . $filename;
}

/**
 * Pull a single file's sub-array out of a grouped $_FILES entry produced by
 * an input named e.g. "image[some_key]" — PHP groups these as
 * $_FILES['image']['name']['some_key'], $_FILES['image']['tmp_name']['some_key'], etc.
 * Returns null if that key had no file input.
 */
function grouped_file(array $filesGroup, string $key): ?array
{
    if (!isset($filesGroup['name'][$key])) {
        return null;
    }
    return [
        'name'     => $filesGroup['name'][$key],
        'type'     => $filesGroup['type'][$key],
        'tmp_name' => $filesGroup['tmp_name'][$key],
        'error'    => $filesGroup['error'][$key],
        'size'     => $filesGroup['size'][$key],
    ];
}
