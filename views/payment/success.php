<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/PaymentController.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../config/database.php';

Auth::requireLogin();

$paymentId = $_GET['payment_id'] ?? null;

if (!$paymentId) {
    setFlashMessage('danger', 'Invalid payment ID');
    header('Location: ' . APP_URL . '/views/booking/my_bookings.php');
    exit;
}

$controller = new PaymentController();
$payment = $controller->detail($paymentId);

// Decode payment details JSON jika ada
$paymentDetails = null;
if (!empty($payment['payment_details'])) {
    $paymentDetails = json_decode($payment['payment_details'], true);
}

// Get booking items dari booking_items table
$db = Database::getInstance()->getConnection();
$itemsQuery = "SELECT bi.*, i.name as item_name, i.image_url, i.category
               FROM booking_items bi
               LEFT JOIN items i ON bi.item_id = i.id
               WHERE bi.booking_id = ?";
$stmt = $db->prepare($itemsQuery);
$stmt->execute([$payment['booking_id']]);
$bookingItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If no items in booking_items, use main booking (backward compatibility)
if (empty($bookingItems)) {
    $bookingItems = [[
        'item_name' => $payment['item_name'],
        'image_url' => $payment['image_url'],
        'category' => $payment['category'] ?? '',
        'quantity' => $payment['quantity'],
        'price_per_day' => $payment['price_per_day'] ?? 0,
        'start_date' => $payment['start_date'],
        'end_date' => $payment['end_date'],
        'subtotal' => $payment['amount']
    ]];
}

$pageTitle = 'Payment Success - ' . APP_NAME;

include __DIR__ . '/../header.php';
include __DIR__ . '/../topnav.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <?php if ($payment['status'] === 'completed'): ?>
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="text-success mb-3">Payment Successful!</h2>
                        <p class="lead text-muted">Your booking has been confirmed</p>
                    <?php else: ?>
                        <div class="mb-4">
                            <i class="bi bi-clock-fill text-warning" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="text-warning mb-3">Payment Pending</h2>
                        <p class="lead text-muted">Waiting for payment confirmation</p>
                    <?php endif; ?>

                    <hr class="my-4">

                    <!-- Payment Details -->
                    <div class="row text-start">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Transaction ID</p>
                            <h5><?= htmlspecialchars($payment['transaction_id']) ?></h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Payment Date</p>
                            <h5><?= formatDate($payment['payment_date'], 'd M Y H:i') ?></h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Payment Method</p>
                            <h5>
                                <?php if ($payment['payment_method'] === 'bank_transfer'): ?>
                                    <i class="bi bi-bank text-primary me-2"></i>
                                <?php elseif ($payment['payment_method'] === 'e_wallet'): ?>
                                    <i class="bi bi-wallet2 text-success me-2"></i>
                                <?php else: ?>
                                    <i class="bi bi-cash text-warning me-2"></i>
                                <?php endif; ?>
                                <?= ucwords(str_replace('_', ' ', $payment['payment_method'])) ?>
                            </h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Total Amount</p>
                            <h5 class="text-success"><?= formatRupiah($payment['amount']) ?></h5>
                        </div>
                    </div>

                    <!-- Payment Method Details -->
                    <?php if ($paymentDetails): ?>
                        <hr class="my-3">
                        <div class="text-start">
                            <h6 class="text-muted mb-3">Payment Method Details</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <?php if ($payment['payment_method'] === 'bank_transfer'): ?>
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <small class="text-muted">Bank Name</small>
                                                <p class="mb-0 fw-bold"><?= htmlspecialchars($paymentDetails['bank_name'] ?? '-') ?></p>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <small class="text-muted">Account Number</small>
                                                <p class="mb-0 fw-bold"><?= htmlspecialchars($paymentDetails['account_number'] ?? '-') ?></p>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <small class="text-muted">Account Holder</small>
                                                <p class="mb-0 fw-bold"><?= htmlspecialchars($paymentDetails['account_holder'] ?? '-') ?></p>
                                            </div>
                                        </div>
                                    <?php elseif ($payment['payment_method'] === 'e_wallet'): ?>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <small class="text-muted">E-Wallet Provider</small>
                                                <p class="mb-0 fw-bold"><?= htmlspecialchars($paymentDetails['ewallet_type'] ?? '-') ?></p>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <small class="text-muted">Phone Number</small>
                                                <p class="mb-0 fw-bold"><?= htmlspecialchars($paymentDetails['ewallet_phone'] ?? '-') ?></p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <hr class="my-4">

                    <!-- Booking Items - MULTIPLE ITEMS -->
                    <div class="text-start">
                        <h5 class="mb-3">Booking Items (<?= count($bookingItems) ?> items)</h5>
                        
                        <?php foreach ($bookingItems as $item): ?>
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <?php if (!empty($item['image_url'])): ?>
                                            <img src="<?= APP_URL . '/' . htmlspecialchars($item['image_url']) ?>"
                                                class="img-fluid rounded"
                                                alt="<?= htmlspecialchars($item['item_name']) ?>"
                                                style="height: 80px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center"
                                                style="height: 80px;">
                                                <i class="bi bi-image text-white fs-2"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="mb-1"><?= htmlspecialchars($item['item_name']) ?></h6>
                                        <?php if (!empty($item['category'])): ?>
                                        <p class="text-muted mb-1 small">
                                            <i class="bi bi-tag me-1"></i><?= htmlspecialchars($item['category']) ?>
                                        </p>
                                        <?php endif; ?>
                                        <p class="mb-1 small">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            <?= formatDate($item['start_date']) ?> - <?= formatDate($item['end_date']) ?>
                                        </p>
                                        <p class="mb-0 small">
                                            <i class="bi bi-box me-1"></i>
                                            Quantity: <?= $item['quantity'] ?> × <?= calculateDays($item['start_date'], $item['end_date']) ?> days
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <p class="mb-1 small text-muted"><?= formatRupiah($item['price_per_day'] ?? 0) ?>/day</p>
                                        <h6 class="text-success mb-0"><?= formatRupiah($item['subtotal']) ?></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Instructions based on status and payment method -->
                    <?php if ($payment['status'] === 'pending'): ?>
                        <div class="alert alert-info mt-4 text-start">
                            <h6 class="alert-heading">
                                <i class="bi bi-info-circle me-2"></i>Next Steps
                            </h6>
                            
                            <?php if ($payment['payment_method'] === 'bank_transfer'): ?>
                                <p class="mb-2">Please complete your bank transfer to the following account:</p>
                                <div class="card bg-white mb-2">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Bank Name</small>
                                                <p class="mb-0 fw-bold">BCA</p>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Account Number</small>
                                                <p class="mb-0 fw-bold">1234567890</p>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <small class="text-muted">Account Holder</small>
                                                <p class="mb-0 fw-bold">Camping Rental Indonesia</p>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <small class="text-muted">Amount</small>
                                                <p class="mb-0 fw-bold text-success"><?= formatRupiah($payment['amount']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-0"><strong>Important:</strong> Admin will confirm your payment within 1x24 hours after transfer.</p>
                                
                            <?php elseif ($payment['payment_method'] === 'e_wallet'): ?>
                                <p class="mb-2">You will receive a payment link via SMS/notification to your registered phone number:</p>
                                <p class="mb-2 fw-bold"><?= htmlspecialchars($paymentDetails['ewallet_phone'] ?? '-') ?></p>
                                <p class="mb-0"><strong>Important:</strong> Please complete the payment through the link. Admin will confirm within 1x24 hours.</p>
                                
                            <?php elseif ($payment['payment_method'] === 'cash'): ?>
                                <p class="mb-2">Your booking is confirmed with Cash on Pickup method.</p>
                                <p class="mb-0"><strong>Important:</strong> Please prepare the exact amount (<?= formatRupiah($payment['amount']) ?>) when picking up your items.</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success mt-4 text-start">
                            <h6 class="alert-heading">
                                <i class="bi bi-check-circle me-2"></i>Payment Confirmed
                            </h6>
                            <p class="mb-0">Your payment has been verified. You can now proceed to pick up your items on the scheduled date.</p>
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="<?= APP_URL ?>/views/booking/my_bookings.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-bag-check me-2"></i>View My Bookings
                        </a>
                        <a href="<?= APP_URL ?>/index.php" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-house me-2"></i>Back to Home
                        </a>
                        <?php if ($payment['status'] === 'completed'): ?>
                            <a href="<?= APP_URL ?>/views/booking/receipt.php?booking_id=<?= $payment['booking_id'] ?>" 
                               target="_blank" class="btn btn-success btn-lg">
                                <i class="bi bi-printer me-2"></i>Print Receipt
                            </a>
                        <?php else: ?>
                            <button class="btn btn-outline-info btn-lg" onclick="window.print()">
                                <i class="bi bi-printer me-2"></i>Print Receipt
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tips Card -->
            <?php if ($payment['status'] === 'pending'): ?>
                <div class="card shadow mt-3">
                    <div class="card-body">
                        <h6 class="card-title"><i class="bi bi-lightbulb text-warning me-2"></i>Tips</h6>
                        <ul class="mb-0 small">
                            <li>Save this page or take a screenshot for your reference</li>
                            <li>Include the Transaction ID (<?= htmlspecialchars($payment['transaction_id']) ?>) when contacting support</li>
                            <?php if ($payment['payment_method'] === 'bank_transfer'): ?>
                                <li>Transfer must be done from the account you registered (<?= htmlspecialchars($paymentDetails['bank_name'] ?? '-') ?> - <?= htmlspecialchars($paymentDetails['account_number'] ?? '-') ?>)</li>
                            <?php endif; ?>
                            <li>You'll receive email notification once payment is confirmed</li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style>
    @media print {
        .btn, .alert-info, .card:last-child {
            display: none !important;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
    }
</style>

<?php include __DIR__ . '/../footer.php'; ?>