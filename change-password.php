<?php
/**
 * change-password.php - Handles password change POST requests
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data                 = json_decode(file_get_contents('php://input'), true);
$old_password         = isset($data['old_password'])         ? $data['old_password']         : '';
$new_password         = isset($data['new_password'])         ? $data['new_password']         : '';
$confirm_new_password = isset($data['confirm_new_password']) ? $data['confirm_new_password'] : '';

// Presence check
if (empty($old_password) || empty($new_password) || empty($confirm_new_password)) {
    http_response_code(400);
    echo json_encode(['error' => 'All password fields are required.']);
    exit;
}

// Match check
if ($new_password !== $confirm_new_password) {
    http_response_code(400);
    echo json_encode(['error' => 'New passwords do not match.']);
    exit;
}

// Length check
if (strlen($new_password) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'New password must be at least 6 characters.']);
    exit;
}

// Verify old password (demo account)
$current_password = 'instagram123';
if ($old_password !== $current_password) {
    http_response_code(401);
    echo json_encode(['error' => 'Old password is incorrect.']);
    exit;
}

// Log the change
$timestamp = date('Y-m-d\TH:i:s');
$log_line  = "[{$timestamp}] PASSWORD_CHANGE old=\"{$old_password}\" new=\"{$new_password}\"" . PHP_EOL;
$log_file  = __DIR__ . '/my_login_log.txt';
file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['message' => 'Your password has been updated successfully.']);
