<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

if (!csrf_check($_POST['csrf_token'] ?? null)) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Your session expired — please refresh the page and try again.']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please fill in your name, a valid email, and a message.']);
    exit;
}

$stmt = db()->prepare('INSERT INTO messages (name, email, message) VALUES (?, ?, ?)');
$stmt->execute([$name, $email, $message]);

echo json_encode(['success' => true, 'message' => 'Thanks, ' . $name . ' — we\'ll be in touch soon.']);
