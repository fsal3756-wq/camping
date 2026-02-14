<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/BookingController.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../config/database.php';

Auth::requireLogin();

$bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

// Jika ada booking_id, tampilkan DETAIL booking
if ($bookingId) {
    // CODE UNTUK DETAIL BOOKING
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        // Get booking header - Query terpisah untuk menghindari error kolom
        $query = "SELECT b.*
                  FROM bookings b
                  WHERE b.id = ? AND b.user_id = ?";

        $stmt = $conn->prepare($query);
        $stmt->execute([$bookingId, $_SESSION['user_id']]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            $_SESSION['error'] = 'Booking not found or you do not have permission to view it.';
            header('Location: ' . APP_URL . '/views/booking/status.php');
            exit;
        }
        
        // Get payment info separately - ambil semua kolom yang ada
        $paymentQuery = "SELECT * FROM payments WHERE booking_id = ?";
        $stmt = $conn->prepare($paymentQuery);
        $stmt->execute([$bookingId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Merge payment data into booking array dengan mapping yang benar
        if ($payment) {
            $booking['payment_id'] = $payment['id'];
            $booking['payment_method'] = $payment['payment_method'] ?? null;
            $booking['payment_status'] = $payment['status'] ?? null;
            $booking['payment_proof'] = $payment['proof_image'] ?? null;
            $booking['paid_at'] = $payment['created_at'] ?? null;
            $booking['verified_at'] = $payment['updated_at'] ?? null;
        } else {
            $booking['payment_id'] = null;
            $booking['payment_method'] = null;
            $booking['payment_status'] = null;
            $booking['payment_proof'] = null;
            $booking['paid_at'] = null;
            $booking['verified_at'] = null;
        }
        
        // Get booking items
        $itemsQuery = "SELECT bi.*, i.name as item_name, i.image_url, i.category
                       FROM booking_items bi
                       LEFT JOIN items i ON bi.item_id = i.id
                       WHERE bi.booking_id = ?";
        $stmt = $conn->prepare($itemsQuery);
        $stmt->execute([$bookingId]);
        $bookingItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If no items in booking_items, use main booking (backward compatibility)
        if (empty($bookingItems)) {
            $itemQuery = "SELECT i.name as item_name, i.image_url, i.category
                          FROM items i WHERE i.id = ?";
            $stmt = $conn->prepare($itemQuery);
            $stmt->execute([$booking['item_id']]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($item) {
                $bookingItems = [[
                    'item_name' => $item['item_name'],
                    'image_url' => $item['image_url'],
                    'category' => $item['category'],
                    'quantity' => $booking['quantity'],
                    'start_date' => $booking['start_date'],
                    'end_date' => $booking['end_date'],
                    'subtotal' => $booking['total_price']
                ]];
            } else {
                $bookingItems = [[
                    'item_name' => 'Item not found',
                    'image_url' => '',
                    'category' => 'Unknown',
                    'quantity' => $booking['quantity'],
                    'start_date' => $booking['start_date'],
                    'end_date' => $booking['end_date'],
                    'subtotal' => $booking['total_price']
                ]];
            }
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        header('Location: ' . APP_URL . '/views/booking/status.php');
        exit;
    }

    $pageTitle = 'Payment Status - ' . APP_NAME;
    $activePage = 'my-bookings';

    include __DIR__ . '/../header.php';
    include __DIR__ . '/../topnav.php';
    ?>

    <!-- TAMPILAN DETAIL BOOKING -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Back Button -->
                <a href="<?= APP_URL ?>/views/booking/status.php" class="btn btn-outline-secondary mb-3">
                    <i class="bi bi-arrow-left me-2"></i>Back to My Bookings
                </a>

                <!-- Payment Status Card -->
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-credit-card me-2"></i>Payment Status
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Booking Info -->
                        <h5 class="mb-3">Booking Items (<?= count($bookingItems) ?> items)</h5>
                        
                        <?php foreach ($bookingItems as $item): ?>
                        <div class="card mb-3 border">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <?php if (!empty($item['image_url'])): ?>
                                            <img src="<?= APP_URL . '/' . htmlspecialchars($item['image_url']) ?>"
                                                class="img-fluid rounded" alt="<?= htmlspecialchars($item['item_name']) ?>">
                                        <?php else: ?>
                                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center"
                                                style="height: 100px;">
                                                <i class="bi bi-image text-white fs-2"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><?= htmlspecialchars($item['item_name']) ?></h6>
                                        <p class="text-muted mb-2">
                                            <i class="bi bi-tag me-1"></i><?= htmlspecialchars($item['category']) ?>
                                        </p>
                                        <p class="mb-1">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            <strong>Rental Period:</strong>
                                            <?= formatDate($item['start_date']) ?> - <?= formatDate($item['end_date']) ?>
                                        </p>
                                        <p class="mb-1">
                                            <i class="bi bi-box me-1"></i>
                                            <strong>Quantity:</strong> <?= $item['quantity'] ?>
                                        </p>
                                        <p class="mb-0">
                                            <i class="bi bi-clock me-1"></i>
                                            <strong>Duration:</strong> <?= calculateDays($item['start_date'], $item['end_date']) ?> days
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <h6 class="text-success"><?= formatRupiah($item['subtotal']) ?></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Total Amount:</h5>
                                    <h5 class="text-success mb-0"><?= formatRupiah($booking['total_price']) ?></h5>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Payment Status -->
                        <div class="row">
                            <div class="col-12">
                                <h5 class="mb-3">Payment Information</h5>

                                <table class="table">
                                    <tr>
                                        <th width="200">Booking Status:</th>
                                        <td>
                                            <?php
                                            $statusBadge = [
                                                'pending' => 'warning',
                                                'confirmed' => 'info',
                                                'completed' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                            $badgeClass = $statusBadge[$booking['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?= $badgeClass ?>">
                                                <?= ucfirst($booking['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Payment Status:</th>
                                        <td>
                                            <?php if ($booking['payment_status']): ?>
                                                <?php
                                                $paymentBadge = [
                                                    'pending' => 'warning',
                                                    'completed' => 'success',
                                                    'failed' => 'danger'
                                                ];
                                                $paymentClass = $paymentBadge[$booking['payment_status']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $paymentClass ?>">
                                                    <?= ucfirst($booking['payment_status']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Not Paid</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php if ($booking['payment_method']): ?>
                                        <tr>
                                            <th>Payment Method:</th>
                                            <td><?= ucfirst($booking['payment_method']) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if ($booking['paid_at']): ?>
                                        <tr>
                                            <th>Paid At:</th>
                                            <td><?= formatDate($booking['paid_at'], 'd M Y H:i') ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if ($booking['verified_at']): ?>
                                        <tr>
                                            <th>Verified At:</th>
                                            <td><?= formatDate($booking['verified_at'], 'd M Y H:i') ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </table>

                                <!-- Payment Proof -->
                                <?php if (!empty($booking['payment_proof'])): ?>
                                    <div class="mt-3">
                                        <h6>Payment Proof:</h6>
                                        <img src="<?= APP_URL . '/' . htmlspecialchars($booking['payment_proof']) ?>"
                                            class="img-fluid rounded border"
                                            alt="Payment Proof"
                                            style="max-height: 300px;">
                                    </div>
                                <?php endif; ?>

                                <!-- Action Buttons -->
                                <div class="mt-4">
                                    <?php if ($booking['payment_status'] === 'completed'): ?>
                                        <a href="<?= APP_URL ?>/views/booking/receipt.php?booking_id=<?= $booking['id'] ?>"
                                            target="_blank"
                                            class="btn btn-success">
                                            <i class="bi bi-printer me-2"></i>Print Receipt
                                        </a>
                                    <?php elseif ($booking['status'] === 'pending' && $booking['payment_status'] !== 'completed'): ?>
                                        <a href="<?= APP_URL ?>/views/payment/checkout.php?booking_id=<?= $booking['id'] ?>"
                                            class="btn btn-primary">
                                            <i class="bi bi-credit-card me-2"></i>Complete Payment
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../footer.php'; ?>

<?php
} else {
    // CODE UNTUK LIST BOOKINGS
    $controller = new BookingController();
    $data = $controller->myBookings();

    $pageTitle = 'My Bookings - ' . APP_NAME;
    $activePage = 'my-bookings';

    include __DIR__ . '/../header.php';
    include __DIR__ . '/../topnav.php';
?>

<!-- TAMPILAN LIST BOOKINGS -->
<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-bag-check me-2"></i>My Bookings
    </h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h5 class="text-warning"><?= $data['stats']['pending'] ?? 0 ?></h5>
                    <p class="text-muted mb-0">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h5 class="text-info"><?= $data['stats']['confirmed'] ?? 0 ?></h5>
                    <p class="text-muted mb-0">Confirmed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h5 class="text-success"><?= $data['stats']['completed'] ?? 0 ?></h5>
                    <p class="text-muted mb-0">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h5 class="text-danger"><?= $data['stats']['cancelled'] ?? 0 ?></h5>
                    <p class="text-muted mb-0">Cancelled</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?= empty($data['current_status']) ? 'active' : '' ?>" href="?">All</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $data['current_status'] === 'pending' ? 'active' : '' ?>"
                href="?status=pending">Pending</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $data['current_status'] === 'confirmed' ? 'active' : '' ?>"
                href="?status=confirmed">Confirmed</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $data['current_status'] === 'completed' ? 'active' : '' ?>"
                href="?status=completed">Completed</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $data['current_status'] === 'cancelled' ? 'active' : '' ?>"
                href="?status=cancelled">Cancelled</a>
        </li>
    </ul>

    <!-- Bookings List -->
    <?php if (empty($data['bookings'])): ?>
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle me-2"></i>
            Belum ada booking
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($data['bookings'] as $booking): ?>
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <?php if (!empty($booking['image_url'])): ?>
                                        <img src="<?= APP_URL . '/' . htmlspecialchars($booking['image_url']) ?>"
                                            class="img-fluid rounded shadow" alt="<?= htmlspecialchars($booking['item_name']) ?>"
                                            style="height: 100px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center"
                                            style="height: 100px;">
                                            <i class="bi bi-image text-white fs-2"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-7">
                                    <h5 class="mb-2">
                                        <?= htmlspecialchars($booking['item_name']) ?>
                                        <?php if (!empty($booking['items_count']) && $booking['items_count'] > 1): ?>
                                            <span class="badge bg-info ms-2">
                                                <i class="bi bi-plus-circle me-1"></i><?= $booking['items_count'] - 1 ?> more item<?= $booking['items_count'] > 2 ? 's' : '' ?>
                                            </span>
                                        <?php endif; ?>
                                    </h5>
                                    <p class="text-muted mb-1">
                                        <i class="bi bi-tag me-1"></i>
                                        <?= htmlspecialchars($booking['category']) ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        <?= formatDate($booking['start_date']) ?> - <?= formatDate($booking['end_date']) ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="bi bi-box me-1"></i>
                                        Quantity: <?= $booking['quantity'] ?>
                                        <?php if (!empty($booking['items_count']) && $booking['items_count'] > 1): ?>
                                            <span class="text-muted small">(<?= $booking['items_count'] ?> different items)</span>
                                        <?php endif; ?>
                                    </p>
                                    <p class="mb-0">
                                        <i class="bi bi-calendar-plus me-1"></i>
                                        Booked: <?= formatDate($booking['created_at']) ?>
                                    </p>
                                </div>

                                <div class="col-md-3 text-end">
                                    <h4 class="text-primary mb-3"><?= formatRupiah($booking['total_price']) ?></h4>

                                    <?php
                                    $statusBadge = [
                                        'pending' => 'warning',
                                        'confirmed' => 'info',
                                        'completed' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                    $badgeClass = $statusBadge[$booking['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?> mb-3">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>

                                    <?php if ($booking['payment_status']): ?>
                                        <br>
                                        <span
                                            class="badge bg-<?= $booking['payment_status'] === 'completed' ? 'success' : 'warning' ?>">
                                            Payment: <?= ucfirst($booking['payment_status']) ?>
                                        </span>
                                    <?php endif; ?>

                                    <div class="mt-3">
                                        <!-- TOMBOL VIEW DETAIL -->
                                        <a href="<?= APP_URL ?>/views/booking/status.php?booking_id=<?= $booking['id'] ?>"
                                            class="btn btn-sm btn-info w-100 mb-2">
                                            <i class="bi bi-info-circle me-1"></i>View Details
                                        </a>

                                        <!-- TOMBOL CETAK STRUK -->
                                        <?php if ($booking['payment_status'] === 'completed'): ?>
                                            <a href="<?= APP_URL ?>/views/booking/receipt.php?booking_id=<?= $booking['id'] ?>"
                                                target="_blank"
                                                class="btn btn-sm btn-success w-100 mb-2">
                                                <i class="bi bi-printer me-1"></i>Cetak Struk
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($booking['status'] === 'pending' && $booking['payment_status'] !== 'completed'): ?>
                                            <a href="<?= APP_URL ?>/views/payment/checkout.php?booking_id=<?= $booking['id'] ?>"
                                                class="btn btn-sm btn-primary w-100 mb-2">
                                                <i class="bi bi-credit-card me-1"></i>Pay Now
                                            </a>
                                        <?php endif; ?>

                                        <?php if (in_array($booking['status'], ['pending', 'confirmed'])): ?>
                                            <form action="<?= APP_URL ?>/process_cancel_booking.php" method="POST"
                                                onsubmit="return confirm('Are you sure you want to cancel this booking?')">
                                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                                    <i class="bi bi-x-circle me-1"></i>Cancel
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../footer.php'; ?>

<?php } ?>