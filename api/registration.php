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

$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$program = trim($_POST['program_interest'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please provide your full name and a valid email.']);
    exit;
}

$stmt = db()->prepare(
    'INSERT INTO registrations (full_name, email, phone, program_interest, notes) VALUES (?, ?, ?, ?, ?)'
);
$stmt->execute([$fullName, $email, $phone, $program, $notes]);

echo json_encode(['success' => true, 'message' => 'Thanks, ' . $fullName . ' — our team will reach out to confirm your registration.']);
