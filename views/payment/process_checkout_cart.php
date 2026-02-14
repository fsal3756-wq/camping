<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/controllers/CartController.php';
require_once __DIR__ . '/lib/auth.php';

Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('danger', 'Invalid request method');
    redirect('/views/cart/index.php');
}

$user = Auth::user();
$paymentMethod = $_POST['payment_method'] ?? null;

// Validasi payment method
$allowedMethods = ['bank_transfer', 'e_wallet', 'cash', 'credit_card'];
if (!$paymentMethod || !in_array($paymentMethod, $allowedMethods)) {
    setFlashMessage('danger', 'Please select a valid payment method');
    redirect('/views/cart/checkout.php');
}

// Get cart items
$controller = new CartController();
$cartData = $controller->index();
$cartItems = $cartData['items'] ?? [];

if (empty($cartItems)) {
    setFlashMessage('warning', 'Your cart is empty');
    redirect('/views/cart/index.php');
}

// Validate availability sebelum checkout
require_once __DIR__ . '/models/Cart.php';
$cartModel = new Cart();
$unavailable = $cartModel->validateAvailability($user['id']);

if (!empty($unavailable)) {
    $items = array_map(fn($item) => $item['item_name'], $unavailable);
    setFlashMessage('danger', 'Some items are no longer available: ' . implode(', ', $items));
    redirect('/views/cart/index.php');
}

// Create bookings for each cart item
require_once __DIR__ . '/models/Booking.php';
require_once __DIR__ . '/models/Payment.php';

$bookingModel = new Booking();
$paymentModel = new Payment();

$bookingIds = [];
$failedItems = [];
$totalAmount = 0;

// Start transaction
$db = Database::getInstance()->getConnection();
$db->beginTransaction();

try {
    foreach ($cartItems as $cartItem) {
        // Create booking
        $bookingData = [
            'user_id' => $user['id'],
            'item_id' => $cartItem['item_id'],
            'start_date' => $cartItem['start_date'],
            'end_date' => $cartItem['end_date'],
            'quantity' => $cartItem['quantity'],
            'notes' => $cartItem['notes']
        ];

        $bookingId = $bookingModel->create($bookingData);
        
        if ($bookingId) {
            $bookingIds[] = $bookingId;
            
            // Calculate amount
            $days = calculateDays($cartItem['start_date'], $cartItem['end_date']);
            $amount = $cartItem['price_per_day'] * $cartItem['quantity'] * $days;
            $totalAmount += $amount;
            
            // Create payment record
            $paymentData = [
                'booking_id' => $bookingId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payment_status' => ($paymentMethod === 'cash') ? 'pending' : 'pending'
            ];
            
            $paymentModel->create($paymentData);
        } else {
            $failedItems[] = $cartItem['item_name'];
        }
    }

    if (!empty($failedItems)) {
        throw new Exception('Failed to create bookings for: ' . implode(', ', $failedItems));
    }

    // Clear cart setelah semua booking berhasil
    $cartModel->clear($user['id']);

    // Commit transaction
    $db->commit();

    // Set success message based on payment method
    $message = count($bookingIds) . ' booking(s) created successfully! ';
    
    switch ($paymentMethod) {
        case 'bank_transfer':
            $message .= 'Please complete your bank transfer and upload proof of payment in My Bookings page.';
            break;
        case 'e_wallet':
            $message .= 'Please complete your e-wallet payment and upload screenshot in My Bookings page.';
            break;
        case 'cash':
            $message .= 'Please bring exact amount when picking up your items.';
            break;
        case 'credit_card':
            $message .= 'Your credit card payment is being processed.';
            break;
    }

    setFlashMessage('success', $message);
    
    // Redirect to booking status page
    redirect('/views/booking/status.php');

} catch (Exception $e) {
    // Rollback transaction
    $db->rollBack();
    
    setFlashMessage('danger', 'Checkout failed: ' . $e->getMessage());
    redirect('/views/cart/checkout.php');
}