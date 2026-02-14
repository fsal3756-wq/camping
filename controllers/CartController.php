<?php
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Item.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/functions.php';

class CartController
{
    private $cartModel;
    private $bookingModel;
    private $itemModel;

    public function __construct()
    {
        $this->cartModel = new Cart();
        $this->bookingModel = new Booking();
        $this->itemModel = new Item();
    }

    public function index()
    {
        Auth::requireLogin();
        $user = Auth::user();

        $items = $this->cartModel->getUserCart($user['id']);
        $count = $this->cartModel->getCartCount($user['id']);
        $total = $this->cartModel->getCartTotal($user['id']);

        return [
            'items' => $items,
            'count' => $count,
            'total' => $total
        ];
    }

    public function add()
    {
        Auth::requireLogin();
        $user = Auth::user();

        $itemId = $_POST['item_id'] ?? null;
        $startDate = $_POST['start_date'] ?? null;
        $endDate = $_POST['end_date'] ?? null;
        $quantity = (int)($_POST['quantity'] ?? 1);
        $notes = $_POST['notes'] ?? null;

        // Validation
        if (!$itemId || !$startDate || !$endDate || $quantity < 1) {
            return [
                'success' => false,
                'message' => 'Please fill all required fields'
            ];
        }

        // Validate dates
        if (strtotime($startDate) < strtotime(date('Y-m-d'))) {
            return [
                'success' => false,
                'message' => 'Start date cannot be in the past'
            ];
        }

        if (strtotime($endDate) < strtotime($startDate)) {
            return [
                'success' => false,
                'message' => 'End date must be after start date'
            ];
        }

        // Check item exists
        $item = $this->itemModel->getById($itemId);
        if (!$item) {
            return [
                'success' => false,
                'message' => 'Item not found'
            ];
        }

        // Check availability
        if (!isItemAvailable($itemId, $startDate, $endDate, $quantity)) {
            return [
                'success' => false,
                'message' => 'Item is not available for selected dates'
            ];
        }

        // Add to cart
        $cartData = [
            'user_id' => $user['id'],
            'item_id' => $itemId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'quantity' => $quantity,
            'notes' => $notes
        ];

        if ($this->cartModel->add($cartData)) {
            return [
                'success' => true,
                'message' => 'Item added to cart',
                'cart_count' => $this->cartModel->getCartCount($user['id'])['total_items']
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to add item to cart'
            ];
        }
    }

    public function updateQuantity()
    {
        Auth::requireLogin();
        $user = Auth::user();

        $cartId = $_POST['cart_id'] ?? null;
        $quantity = (int)($_POST['quantity'] ?? 1);

        if (!$cartId || $quantity < 1) {
            return [
                'success' => false,
                'message' => 'Invalid data'
            ];
        }

        // Update quantity
        if ($this->cartModel->updateQuantity($cartId, $quantity, $user['id'])) {
            $cartItem = $this->cartModel->getById($cartId);
            $cartTotal = $this->cartModel->getCartTotal($user['id']);

            return [
                'success' => true,
                'message' => 'Quantity updated',
                'subtotal' => $cartItem['subtotal'],
                'cart_total' => $cartTotal
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to update quantity'
            ];
        }
    }

    public function updateDates()
    {
        Auth::requireLogin();
        $user = Auth::user();

        $cartId = $_POST['cart_id'] ?? null;
        $startDate = $_POST['start_date'] ?? null;
        $endDate = $_POST['end_date'] ?? null;

        if (!$cartId || !$startDate || !$endDate) {
            return [
                'success' => false,
                'message' => 'Invalid data'
            ];
        }

        // Update dates
        if ($this->cartModel->updateDates($cartId, $startDate, $endDate, $user['id'])) {
            $cartItem = $this->cartModel->getById($cartId);
            $cartTotal = $this->cartModel->getCartTotal($user['id']);

            return [
                'success' => true,
                'message' => 'Dates updated',
                'subtotal' => $cartItem['subtotal'],
                'cart_total' => $cartTotal
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to update dates'
            ];
        }
    }

    public function remove()
    {
        Auth::requireLogin();
        $user = Auth::user();

        $cartId = $_POST['cart_id'] ?? null;

        if (!$cartId) {
            return [
                'success' => false,
                'message' => 'Invalid cart ID'
            ];
        }

        if ($this->cartModel->remove($cartId, $user['id'])) {
            return [
                'success' => true,
                'message' => 'Item removed from cart',
                'cart_total' => $this->cartModel->getCartTotal($user['id']),
                'cart_count' => $this->cartModel->getCartCount($user['id'])['total_items']
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to remove item'
            ];
        }
    }

    public function clear()
    {
        Auth::requireLogin();
        $user = Auth::user();

        if ($this->cartModel->clear($user['id'])) {
            return [
                'success' => true,
                'message' => 'Cart cleared'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to clear cart'
            ];
        }
    }

    /**
     * Checkout cart - Create ONE booking with multiple items
     * FIXED: Membuat 1 transaksi booking untuk multiple items
     */
    public function checkout()
    {
        Auth::requireLogin();
        $user = Auth::user();

        // Get cart items
        $cartItems = $this->cartModel->getUserCart($user['id']);

        if (empty($cartItems)) {
            setFlashMessage('danger', 'Your cart is empty');
            redirect('/camping-rental-apps/views/cart/index.php');
            exit;
        }

        try {
            // Start transaction
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();

            // Create ONE parent booking
            // Gunakan item pertama sebagai referensi untuk tanggal
            $firstItem = $cartItems[0];
            
            // Calculate total price dari semua items
            $totalPrice = 0;
            foreach ($cartItems as $item) {
                $totalPrice += $item['subtotal'];
            }

            // Insert parent booking
            $bookingQuery = "INSERT INTO bookings 
                            (user_id, item_id, start_date, end_date, quantity, total_price, status, notes, booking_date, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW(), NOW())";
            
            $stmt = $db->prepare($bookingQuery);
            $stmt->execute([
                $user['id'],
                $firstItem['item_id'], // Item pertama sebagai main item
                $firstItem['start_date'],
                $firstItem['end_date'],
                $firstItem['quantity'],
                $totalPrice,
                'Multiple items - see booking_items for details'
            ]);
            
            $bookingId = $db->lastInsertId();

            // Insert semua items ke booking_items table
            $itemsQuery = "INSERT INTO booking_items 
                          (booking_id, item_id, quantity, price_per_day, start_date, end_date, subtotal, notes) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $itemsStmt = $db->prepare($itemsQuery);
            
            foreach ($cartItems as $item) {
                $itemsStmt->execute([
                    $bookingId,
                    $item['item_id'],
                    $item['quantity'],
                    $item['price_per_day'],
                    $item['start_date'],
                    $item['end_date'],
                    $item['subtotal'],
                    $item['notes']
                ]);
            }

            // Clear cart after successful booking
            $this->cartModel->clear($user['id']);

            // Commit transaction
            $db->commit();

            // Redirect to checkout payment
            setFlashMessage('success', 'Booking created successfully');
            redirect("/camping-rental-apps/views/payment/checkout.php?booking_id=$bookingId");

        } catch (Exception $e) {
            // Rollback on error
            $db->rollBack();
            
            setFlashMessage('danger', 'Failed to create booking: ' . $e->getMessage());
            redirect('/camping-rental-apps/views/cart/index.php');
        }
    }

    public function getCount()
    {
        Auth::requireLogin();
        $user = Auth::user();

        $count = $this->cartModel->getCartCount($user['id']);

        return [
            'success' => true,
            'count' => $count['total_items']
        ];
    }
}