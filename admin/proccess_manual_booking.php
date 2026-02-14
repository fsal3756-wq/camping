<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/functions.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../config/database.php';

Auth::requireAdmin();

// Temporary untuk debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/admin/manual_booking.php');
    exit;
}

try {
    // Debug: Log received data
    error_log("=== MANUAL BOOKING SUBMISSION ===");
    error_log("POST Data: " . print_r($_POST, true));
    
    // Validate required fields
    $userId = $_POST['user_id'] ?? null;
    $startDate = $_POST['start_date'] ?? null;
    $endDate = $_POST['end_date'] ?? null;
    $items = $_POST['items'] ?? [];
    $itemQuantities = $_POST['item_quantities'] ?? [];
    $notes = $_POST['notes'] ?? '';
    
    error_log("User ID: $userId");
    error_log("Start Date: $startDate");
    error_log("End Date: $endDate");
    error_log("Items: " . implode(',', $items));
    error_log("Quantities: " . print_r($itemQuantities, true));
    
    // Validation
    if (!$userId) {
        throw new Exception('Customer not selected');
    }
    
    if (!$startDate || !$endDate) {
        throw new Exception('Start date and end date are required');
    }
    
    if (empty($items)) {
        throw new Exception('No items selected');
    }
    
    if (empty($itemQuantities)) {
        throw new Exception('Item quantities not set');
    }
    
    // Validate date range
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    
    if ($end < $start) {
        throw new Exception('End date must be after start date');
    }
    
    // Calculate total days
    $interval = $start->diff($end);
    $days = $interval->days + 1;
    
    error_log("Booking duration: $days days");
    error_log("Items selected: " . count($items));
    
    // Get database instance
    $db = Database::getInstance();
    
    // Get PDO connection for transaction
    $pdo = $db->getConnection();
    
    // Begin transaction
    $pdo->beginTransaction();
    error_log("Transaction started");
    
    try {
        // Calculate total first
        $totalPrice = 0;
        $itemsData = [];
        
        foreach ($items as $itemId) {
            $quantity = $itemQuantities[$itemId] ?? 1;
            
            // Get item details
            $item = $db->fetchOne("SELECT * FROM items WHERE id = ?", [$itemId]);
            
            if (!$item) {
                throw new Exception("Item ID $itemId not found");
            }
            
            // Check availability
            if ($quantity > $item['quantity_available']) {
                throw new Exception("Insufficient quantity for item: " . $item['name'] . ". Available: " . $item['quantity_available']);
            }
            
            $pricePerDay = $item['price_per_day'];
            $subtotal = $pricePerDay * $quantity * $days;
            $totalPrice += $subtotal;
            
            $itemsData[] = [
                'item' => $item,
                'quantity' => $quantity,
                'price_per_day' => $pricePerDay,
                'subtotal' => $subtotal
            ];
            
            error_log("Item: {$item['name']}, Qty: $quantity, Subtotal: $subtotal");
        }
        
        error_log("Total price calculated: $totalPrice");
        
        // Create booking record
        $bookingData = [
            'user_id' => $userId,
            'item_id' => $items[0], // Item pertama sebagai referensi
            'booking_date' => date('Y-m-d'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'quantity' => array_sum($itemQuantities),
            'total_price' => $totalPrice,
            'status' => 'confirmed',
            'notes' => $notes
        ];
        
        error_log("Inserting booking: " . print_r($bookingData, true));
        
        // Insert booking using PDO directly
        $sql = "INSERT INTO bookings (user_id, item_id, booking_date, start_date, end_date, quantity, total_price, status, notes, created_at, updated_at) 
                VALUES (:user_id, :item_id, :booking_date, :start_date, :end_date, :quantity, :total_price, :status, :notes, NOW(), NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':item_id' => $items[0],
            ':booking_date' => date('Y-m-d'),
            ':start_date' => $startDate,
            ':end_date' => $endDate,
            ':quantity' => array_sum($itemQuantities),
            ':total_price' => $totalPrice,
            ':status' => 'confirmed',
            ':notes' => $notes
        ]);
        
        $bookingId = $pdo->lastInsertId();
        
        if (!$bookingId) {
            throw new Exception('Failed to create booking');
        }
        
        error_log("✓ Booking created with ID: $bookingId");
        
        // Insert booking items and update stock
        foreach ($itemsData as $data) {
            $item = $data['item'];
            $quantity = $data['quantity'];
            $pricePerDay = $data['price_per_day'];
            $subtotal = $data['subtotal'];
            
            // Insert booking item
            $sql = "INSERT INTO booking_items (booking_id, item_id, quantity, price_per_day, subtotal, created_at) 
                    VALUES (:booking_id, :item_id, :quantity, :price_per_day, :subtotal, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':booking_id' => $bookingId,
                ':item_id' => $item['id'],
                ':quantity' => $quantity,
                ':price_per_day' => $pricePerDay,
                ':subtotal' => $subtotal
            ]);
            
            error_log("✓ Booking item inserted for item ID: {$item['id']}");
            
            // Update item stock
            $newQuantity = $item['quantity_available'] - $quantity;
            
            $sql = "UPDATE items SET quantity_available = :quantity WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':quantity' => $newQuantity,
                ':id' => $item['id']
            ]);
            
            error_log("✓ Item stock updated: {$item['id']} from {$item['quantity_available']} to $newQuantity");
        }
        
        // Commit transaction
        $pdo->commit();
        error_log("✓ Transaction committed successfully");
        
        // Success
        $_SESSION['success'] = "Booking created successfully! Booking ID: #$bookingId - Total: Rp " . number_format($totalPrice, 0, ',', '.');
        error_log("=== BOOKING SUCCESS ===");
        error_log("Booking ID: $bookingId");
        error_log("Total Price: $totalPrice");
        
        // Redirect
        header('Location: ' . APP_URL . '/admin/bookings.php');
        exit;
        
    } catch (Exception $e) {
        // Rollback on error
        error_log("Error in transaction, rolling back: " . $e->getMessage());
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("=== BOOKING ERROR ===");
    error_log("Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header('Location: ' . APP_URL . '/admin/manual_booking.php');
    exit;
}