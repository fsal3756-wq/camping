<?php

/**
 * 
 * api_login.php
 * API Endpoint untuk Login Authentication
 */

// Enable CORS for testing
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed. Use POST request.'
    ], JSON_PRETTY_PRINT);
    exit;
}

session_start();

// Include dependencies
require_once 'config/database.php';
require_once 'config/app.php';
require_once 'lib/auth.php';

// INIT DB & AUTH (PENTING)
$db   = Database::getInstance();
$auth = new Auth($db);

// Base response
$response = [
    'timestamp' => date('Y-m-d H:i:s'),
    'request_id' => uniqid('req_'),
    'status' => 'error',
    'message' => '',
    'data' => []
];

$start_time = microtime(true);

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    $username = trim($input['username'] ?? '');
    $password = trim($input['password'] ?? '');

    // Validation
    if ($username === '' || $password === '') {
        http_response_code(400);
        $response['message'] = 'Username dan password harus diisi';
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    // AUTH PROCESS
    $result = $auth->login($username, $password);

    // LOGIN SUCCESS
    if (isset($result['success']) && $result['success'] === true) {

        http_response_code(200);
        $response['status']  = 'success';
        $response['message'] = $result['message'];

        $response['data'] = [
            'user' => [
                'id'       => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email'    => $_SESSION['email'],
                'role'     => $_SESSION['role']
            ],
            'session' => [
                'session_id' => session_id(),
                'logged_in'  => true
            ],
            'redirect_url' => ($_SESSION['role'] === 'admin')
                ? '/admin/index.php'
                : '/index.php'
        ];
    }
    // LOGIN FAILED
    else {
        http_response_code(401);
        $response['status'] = 'error';
        $response['message'] = $result['message'];
        $response['data'] = [
            'username_provided' => $username,
            'authentication' => 'failed'
        ];

        // Log failed attempt (optional)
        error_log("Failed login attempt: {$username}");
    }
} catch (Throwable $e) {
    // Server error
    http_response_code(500);
    $response['status'] = 'error';
    $response['message'] = 'Internal server error';
    $response['error_details'] = $e->getMessage();

    // Log error
    error_log("API Login Error: " . $e->getMessage());
}

// Response time
$response['response_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);

// Output
echo json_encode($response, JSON_PRETTY_PRINT);
exit;
