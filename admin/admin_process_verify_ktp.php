<?php
/**
 * ============================================
 * ADMIN - Process KTP Verification
 * ============================================
 * File: admin_process_verify_ktp.php
 * Location: /camping-rental-apps/admin/process_verify_ktp.php
 * 
 * Purpose:
 * - Handle KTP approval/rejection by admin
 * - Update verification status
 * - Store verification notes
 * - Log verification activity
 * - Send notification to user (optional)
 * 
 * Security:
 * - Admin authentication required
 * - Validate user existence
 * - Validate KTP existence
 * - Prevent duplicate verification
 * ============================================
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/functions.php';
require_once __DIR__ . '/../models/User.php';

// ============================================
// 1. AUTHENTICATION CHECK - ADMIN ONLY
// ============================================
if (!Auth::check() || !Auth::isAdmin()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Admin access required.'
    ]);
    exit;
}

$currentAdmin = Auth::user();
$userModel = new User();

// ============================================
// 2. VALIDATE REQUEST METHOD
// ============================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST request.'
    ]);
    exit;
}

// ============================================
// 3. GET JSON INPUT
// ============================================
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON input.'
    ]);
    exit;
}

// ============================================
// 4. VALIDATE INPUT DATA
// ============================================
$userId = isset($input['user_id']) ? (int)$input['user_id'] : 0;
$action = isset($input['action']) ? trim($input['action']) : '';
$notes = isset($input['notes']) ? trim($input['notes']) : '';

// Validate user ID
if ($userId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid user ID.'
    ]);
    exit;
}

// Validate action
$allowedActions = ['approved', 'rejected'];
if (!in_array($action, $allowedActions)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action. Must be "approved" or "rejected".'
    ]);
    exit;
}

// ============================================
// 5. GET USER DATA
// ============================================
$userData = $userModel->getById($userId);

if (!$userData) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'User not found.'
    ]);
    exit;
}

// ============================================
// 6. VALIDATE KTP EXISTS
// ============================================
if (empty($userData['ktp_image'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'User has not uploaded KTP yet.'
    ]);
    exit;
}

// Check if KTP file exists on server
$ktpPath = __DIR__ . '/../' . $userData['ktp_image'];
if (!file_exists($ktpPath)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'KTP file not found on server.'
    ]);
    exit;
}

// ============================================
// 7. CHECK IF ALREADY VERIFIED WITH SAME STATUS
// ============================================
if ($userData['ktp_verified'] === $action) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'KTP already ' . $action . '.'
    ]);
    exit;
}

// ============================================
// 8. UPDATE DATABASE
// ============================================
if (!$userModel->verifyKTP($userId, $action, $currentAdmin['id'], $notes)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update verification status. Please try again.'
    ]);
    exit;
}

// ============================================
// 9. LOG VERIFICATION ACTIVITY
// ============================================
// Log to database
$userModel->logKTPVerification(
    $userId,
    $currentAdmin['id'],
    $action,
    $notes,
    $userData['ktp_image']
);

// Also log to file
$logFile = __DIR__ . '/../logs/ktp_verification.log';
$logDir = dirname($logFile);

if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$logEntry = date('Y-m-d H:i:s') . " | " . 
            "Admin: {$currentAdmin['username']} | " .
            "User: {$userData['username']} (ID: {$userId}) | " .
            "Action: " . strtoupper($action) . " | " .
            "Notes: " . ($notes ?: 'No notes') . " | " .
            "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . PHP_EOL;

file_put_contents($logFile, $logEntry, FILE_APPEND);

// ============================================
// 10. SEND NOTIFICATION TO USER (Optional)
// ============================================
// You can implement email notification here
// Example:
// if ($action === 'approved') {
//     sendEmail($userData['email'], 'KTP Approved', 'Your KTP has been verified...');
// } else {
//     sendEmail($userData['email'], 'KTP Rejected', 'Your KTP was rejected...');
// }

// ============================================
// 11. SUCCESS RESPONSE
// ============================================
$message = $action === 'approved' 
    ? 'KTP has been approved successfully.' 
    : 'KTP has been rejected.';

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => $message,
    'data' => [
        'user_id' => $userId,
        'username' => $userData['username'],
        'full_name' => $userData['full_name'],
        'action' => $action,
        'verified_by' => $currentAdmin['username'],
        'verified_at' => date('Y-m-d H:i:s'),
        'notes' => $notes
    ]
]);
exit;