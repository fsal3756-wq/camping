<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once __DIR__ . '/../config/database.php';

// Response helper function
function sendResponse($status, $data = [], $message = '')
{
    $response = ['status' => $status];

    if (!empty($data)) {
        $response = array_merge($response, $data);
    }

    if (!empty($message)) {
        $response['message'] = $message;
    }

    echo json_encode($response);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse('error', [], 'Method not allowed');
}

// Validate user_id parameter
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    sendResponse('error', [], 'User ID is required');
}

$user_id = intval($_GET['user_id']);

// Validate user_id is a positive integer
if ($user_id <= 0) {
    sendResponse('error', [], 'Invalid user ID');
}

try {
    // Get database instance
    $db = Database::getInstance();

    // Query to get bookings with item details
    $sql = "
        SELECT 
            b.id,
            b.item_id,
            i.name AS item_name,
            b.start_date,
            b.end_date,
            b.total_price,
            b.status
        FROM bookings b
        INNER JOIN items i ON b.item_id = i.id
        WHERE b.user_id = :user_id
        ORDER BY b.created_at DESC
    ";

    $bookings_data = $db->fetchAll($sql, ['user_id' => $user_id]);

    // Format bookings data
    $bookings = [];
    foreach ($bookings_data as $row) {
        $bookings[] = [
            'id' => (int) $row['id'],
            'item_id' => (int) $row['item_id'],
            'item_name' => $row['item_name'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
            'total_price' => (float) $row['total_price'],
            'status' => $row['status']
        ];
    }

    // Return success with bookings data (empty array if no bookings found)
    sendResponse('success', ['bookings' => $bookings]);
} catch (PDOException $e) {
    // Log error (jangan tampilkan detail error ke client di production)
    error_log("Database error: " . $e->getMessage());
    sendResponse('error', [], 'Database error occurred');
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    sendResponse('error', [], 'An error occurred');
}
