<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';

function sendResponse($status, $data = [], $message = '')
{
    $response = ['status' => $status];
    if (!empty($data)) $response = array_merge($response, $data);
    if (!empty($message)) $response['message'] = $message;
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('error', [], 'Method not allowed');
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['user_id', 'item_id', 'start_date', 'end_date', 'quantity'];
foreach ($required as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        sendResponse('error', [], ucfirst(str_replace('_', ' ', $field)) . ' is required');
    }
}

$user_id = intval($input['user_id']);
$item_id = intval($input['item_id']);
$start_date = $input['start_date'];
$end_date = $input['end_date'];
$quantity = intval($input['quantity']);
$notes = $input['notes'] ?? '';

// Validate dates
if (strtotime($start_date) === false || strtotime($end_date) === false) {
    sendResponse('error', [], 'Invalid date format');
}

if (strtotime($start_date) > strtotime($end_date)) {
    sendResponse('error', [], 'Start date must be before end date');
}

try {
    $db = Database::getInstance();

    // Check if item exists and available
    $item = $db->fetchOne(
        "SELECT id, name, price_per_day, quantity_available FROM items WHERE id = :id AND status = 'available'",
        ['id' => $item_id]
    );

    if (!$item) {
        sendResponse('error', [], 'Item not found or unavailable');
    }

    if ($item['quantity_available'] < $quantity) {
        sendResponse('error', [], 'Insufficient quantity available');
    }

    // Calculate total price
    $days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;
    $total_price = $item['price_per_day'] * $days * $quantity;

    // Begin transaction
    $db->beginTransaction();

    // Create booking
    $booking_data = [
        'user_id' => $user_id,
        'item_id' => $item_id,
        'booking_date' => date('Y-m-d'),
        'start_date' => $start_date,
        'end_date' => $end_date,
        'quantity' => $quantity,
        'total_price' => $total_price,
        'status' => 'pending',
        'notes' => $notes
    ];

    $booking_id = $db->insert('bookings', $booking_data);

    if (!$booking_id) {
        $db->rollback();
        sendResponse('error', [], 'Failed to create booking');
    }

    // Update item quantity
    $new_quantity = $item['quantity_available'] - $quantity;
    $db->update(
        'items',
        ['quantity_available' => $new_quantity],
        'id = :id',
        ['id' => $item_id]
    );

    // Record inventory history
    $db->insert('inventory_history', [
        'item_id' => $item_id,
        'quantity_before' => $item['quantity_available'],
        'quantity_after' => $new_quantity,
        'action' => 'booking_created',
        'reference_id' => $booking_id,
        'notes' => "Booking created: $quantity item(s) reserved"
    ]);

    $db->commit();

    sendResponse('success', [
        'booking_id' => $booking_id,
        'total_price' => $total_price,
        'days' => (int) $days
    ], 'Booking created successfully');
} catch (Exception $e) {
    if ($db) $db->rollback();
    error_log("Error: " . $e->getMessage());
    sendResponse('error', [], 'An error occurred');
}
