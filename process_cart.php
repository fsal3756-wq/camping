<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/controllers/CartController.php';
require_once __DIR__ . '/lib/auth.php';

// Check if user is logged in
if (!Auth::check()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['success' => false, 'message' => 'Please login first']);
    } else {
        redirect('/camping-rental-apps/login.php');
    }
    exit;
}

$controller = new CartController();
$action = $_REQUEST['action'] ?? '';

// Set JSON header for AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    header('Content-Type: application/json');
}

try {
    switch ($action) {
        case 'add':
            // Add item to cart
            $result = $controller->add();
            echo json_encode($result);
            break;

        case 'update_quantity':
            // Update quantity
            $result = $controller->updateQuantity();
            echo json_encode($result);
            break;

        case 'update_dates':
            // Update dates
            $result = $controller->updateDates();
            echo json_encode($result);
            break;

        case 'remove':
            // Remove item
            $result = $controller->remove();
            echo json_encode($result);
            break;

        case 'clear':
            // Clear cart
            $result = $controller->clear();
            echo json_encode($result);
            break;

        case 'checkout':
            // Checkout (redirect to payment)
            $controller->checkout();
            break;

        case 'get_count':
            // Get cart count (for badge update)
            $result = $controller->getCount();
            echo json_encode($result);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            break;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}