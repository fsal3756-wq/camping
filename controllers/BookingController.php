<?php
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Item.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/functions.php';
require_once __DIR__ . '/../config/database.php';

class BookingController
{
    private $bookingModel;
    private $itemModel;

    public function __construct()
    {
        $this->bookingModel = new Booking();
        $this->itemModel = new Item();
    }

    public function create()
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/camping-rental-apps/index.php');
        }

        $user = Auth::user();
        $itemId = $_POST['item_id'] ?? null;
        $startDate = $_POST['start_date'] ?? null;
        $endDate = $_POST['end_date'] ?? null;
        $quantity = (int)($_POST['quantity'] ?? 1);
        $notes = $_POST['notes'] ?? null;

        // Validation
        if (!$itemId || !$startDate || !$endDate || $quantity < 1) {
            setFlashMessage('danger', 'Please fill all required fields');
            redirect($_SERVER['HTTP_REFERER'] ?? '/camping-rental-apps/index.php');
        }

        // Validate dates
        if (strtotime($startDate) < strtotime(date('Y-m-d'))) {
            setFlashMessage('danger', 'Start date cannot be in the past');
            redirect($_SERVER['HTTP_REFERER']);
        }

        if (strtotime($endDate) < strtotime($startDate)) {
            setFlashMessage('danger', 'End date must be after start date');
            redirect($_SERVER['HTTP_REFERER']);
        }

        // Check item exists
        $item = $this->itemModel->getById($itemId);
        if (!$item) {
            setFlashMessage('danger', 'Item not found');
            redirect('/camping-rental-apps/index.php');
        }

        // Check availability
        if (!isItemAvailable($itemId, $startDate, $endDate, $quantity)) {
            setFlashMessage('danger', 'Item is not available for selected dates');
            redirect($_SERVER['HTTP_REFERER']);
        }

        // Create booking
        $bookingData = [
            'user_id' => $user['id'],
            'item_id' => $itemId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'quantity' => $quantity,
            'notes' => $notes
        ];

        $bookingId = $this->bookingModel->create($bookingData);

        if ($bookingId) {
            setFlashMessage('success', 'Booking created successfully');
            redirect("/camping-rental-apps/views/payment/checkout.php?booking_id=$bookingId");
        } else {
            setFlashMessage('danger', 'Failed to create booking');
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function myBookings()
    {
        Auth::requireLogin();

        $user = Auth::user();
        $status = $_GET['status'] ?? null;

        $bookings = $this->bookingModel->getUserBookings($user['id'], $status);
        $stats = $this->bookingModel->getBookingStats($user['id']);

        return [
            'bookings' => $bookings,
            'stats' => $stats,
            'current_status' => $status
        ];
    }

    public function detail($id)
    {
        Auth::requireLogin();

        $user = Auth::user();
        $booking = $this->bookingModel->getById($id);

        if (!$booking) {
            setFlashMessage('danger', 'Booking not found');
            redirect('/camping-rental-apps/views/booking/status.php');
        }

        // Check if user owns the booking or is admin
        if ($booking['user_id'] != $user['id'] && !Auth::isAdmin()) {
            setFlashMessage('danger', 'Unauthorized access');
            redirect('/camping-rental-apps/views/booking/status.php');
        }

        return $booking;
    }

    /**
     * Get booking detail with full information for receipt
     * Including payment, user, and item information
     * 
     * @param int $bookingId
     * @return array
     * @throws Exception
     */
    public function getBookingDetail($bookingId)
    {
        Auth::requireLogin();

        $user = Auth::user();
        
        // Get booking with all related data
        $db = Database::getInstance()->getConnection();
        
        $query = "SELECT 
                    b.id,
                    b.user_id,
                    b.item_id,
                    b.start_date,
                    b.end_date,
                    b.quantity,
                    b.total_price,
                    b.status,
                    b.notes,
                    b.created_at,
                    
                    i.name as item_name,
                    i.description,
                    i.price_per_day,
                    i.category,
                    i.image_url,
                    
                    u.full_name as user_name,
                    u.email,
                    u.phone,
                    
                    p.id as payment_id,
                    p.transaction_id,
                    p.payment_method,
                    p.payment_date,
                    p.amount as payment_amount,
                    p.status as payment_status
                    
                FROM bookings b
                LEFT JOIN items i ON b.item_id = i.id
                LEFT JOIN users u ON b.user_id = u.id
                LEFT JOIN payments p ON b.id = p.booking_id
                WHERE b.id = ?";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$booking) {
            throw new Exception('Booking not found');
        }
        
        // Check authorization (user owns booking or is admin)
        if ($booking['user_id'] != $user['id'] && !Auth::isAdmin()) {
            throw new Exception('Unauthorized access');
        }
        
        return $booking;
    }

    public function cancel($id)
    {
        Auth::requireLogin();

        $user = Auth::user();
        $booking = $this->bookingModel->getById($id);

        if (!$booking) {
            setFlashMessage('danger', 'Booking not found');
            redirect('/camping-rental-apps/views/booking/status.php');
        }

        // Check if user owns the booking
        if ($booking['user_id'] != $user['id'] && !Auth::isAdmin()) {
            setFlashMessage('danger', 'Unauthorized access');
            redirect('/camping-rental-apps/views/booking/status.php');
        }

        // Cancel booking
        if ($this->bookingModel->cancel($id, $user['id'])) {
            setFlashMessage('success', 'Booking cancelled successfully');
        } else {
            setFlashMessage('danger', 'Failed to cancel booking. Only pending or confirmed bookings can be cancelled.');
        }

        redirect('/camping-rental-apps/views/booking/status.php');
    }

    public function updateStatus($id, $status)
    {
        Auth::requireAdmin();

        $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];

        if (!in_array($status, $validStatuses)) {
            setFlashMessage('danger', 'Invalid status');
            redirect($_SERVER['HTTP_REFERER'] ?? '/camping-rental-apps/admin/bookings.php');
        }

        if ($this->bookingModel->updateStatus($id, $status)) {
            setFlashMessage('success', 'Booking status updated successfully');
        } else {
            setFlashMessage('danger', 'Failed to update booking status');
        }

        redirect($_SERVER['HTTP_REFERER'] ?? '/camping-rental-apps/admin/bookings.php');
    }

    /**
     * Create manual booking by admin for walk-in customers
     * Supports multiple items booking
     * 
     * @param array $data Booking data with items array
     * @return int|false Booking ID or false on failure
     */
    public function createManualBooking($data)
    {
        Auth::requireAdmin();

        try {
            $userId = $data['user_id'];
            $startDate = $data['start_date'];
            $endDate = $data['end_date'];
            $notes = $data['notes'] ?? null;
            $items = $data['items'] ?? []; // Array of item_id => quantity

            // Validation
            if (!$userId || !$startDate || !$endDate || empty($items)) {
                throw new Exception('Please fill all required fields');
            }

            // Validate dates
            if (strtotime($endDate) < strtotime($startDate)) {
                throw new Exception('End date must be after start date');
            }

            // Calculate duration
            $duration = calculateDays($startDate, $endDate);

            // Get database connection
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();

            try {
                // Calculate total price and prepare items data
                $totalPrice = 0;
                $bookingItemsData = [];

                foreach ($items as $itemId => $quantity) {
                    // Get item details
                    $item = $this->itemModel->getById($itemId);
                    if (!$item) {
                        throw new Exception("Item with ID $itemId not found");
                    }

                    // Check availability
                    if ($item['quantity_available'] < $quantity) {
                        throw new Exception("Only {$item['quantity_available']} units of {$item['name']} available");
                    }

                    // Calculate subtotal
                    $subtotal = $item['price_per_day'] * $quantity * $duration;
                    $totalPrice += $subtotal;

                    // Store item data for booking_items table
                    $bookingItemsData[] = [
                        'item_id' => $itemId,
                        'quantity' => $quantity,
                        'price_per_day' => $item['price_per_day'],
                        'subtotal' => $subtotal,
                        'item_name' => $item['name']
                    ];

                    // Update item stock
                    $newAvailable = $item['quantity_available'] - $quantity;
                    $updateStmt = $db->prepare("UPDATE items SET quantity_available = ? WHERE id = ?");
                    $updateStmt->execute([$newAvailable, $itemId]);
                }

                // Create main booking record (use first item for backward compatibility)
                $firstItem = $bookingItemsData[0];
                $bookingData = [
                    'user_id' => $userId,
                    'item_id' => $firstItem['item_id'], // For backward compatibility
                    'booking_date' => date('Y-m-d'),
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'quantity' => $firstItem['quantity'], // For backward compatibility
                    'total_price' => $totalPrice,
                    'status' => 'confirmed', // Manual bookings are immediately confirmed
                    'notes' => $notes
                ];

                // Insert booking
                $columns = implode(', ', array_keys($bookingData));
                $placeholders = implode(', ', array_fill(0, count($bookingData), '?'));
                $stmt = $db->prepare("INSERT INTO bookings ($columns) VALUES ($placeholders)");
                $stmt->execute(array_values($bookingData));
                $bookingId = $db->lastInsertId();

                // Insert booking items
                $itemStmt = $db->prepare("
                    INSERT INTO booking_items 
                    (booking_id, item_id, quantity, price_per_day, start_date, end_date, subtotal) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                foreach ($bookingItemsData as $itemData) {
                    $itemStmt->execute([
                        $bookingId,
                        $itemData['item_id'],
                        $itemData['quantity'],
                        $itemData['price_per_day'],
                        $startDate,
                        $endDate,
                        $itemData['subtotal']
                    ]);
                }

                // Create payment record (marked as completed for walk-in customers)
                $paymentData = [
                    'booking_id' => $bookingId,
                    'transaction_id' => 'MANUAL-' . strtoupper(uniqid()),
                    'amount' => $totalPrice,
                    'payment_method' => 'cash', // Default for walk-in
                    'status' => 'completed',
                    'payment_date' => date('Y-m-d H:i:s')
                ];

                $paymentColumns = implode(', ', array_keys($paymentData));
                $paymentPlaceholders = implode(', ', array_fill(0, count($paymentData), '?'));
                $paymentStmt = $db->prepare("INSERT INTO payments ($paymentColumns) VALUES ($paymentPlaceholders)");
                $paymentStmt->execute(array_values($paymentData));

                $db->commit();
                return $bookingId;

            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Manual booking error: " . $e->getMessage());
            throw $e;
        }
    }
}