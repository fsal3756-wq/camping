<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/PaymentController.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../config/database.php';

Auth::requireLogin();

$bookingId = $_GET['booking_id'] ?? null;

if (!$bookingId) {
    setFlashMessage('danger', 'Invalid booking ID');
    header('Location: ' . APP_URL . '/views/booking/status.php');
    exit;
}

// Get booking with items
$db = Database::getInstance()->getConnection();

// Get booking header
$bookingQuery = "SELECT b.*, u.full_name, u.email, u.phone
                 FROM bookings b
                 LEFT JOIN users u ON b.user_id = u.id
                 WHERE b.id = ? AND b.user_id = ?";
$stmt = $db->prepare($bookingQuery);
$stmt->execute([$bookingId, $_SESSION['user_id']]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    setFlashMessage('danger', 'Booking not found');
    header('Location: ' . APP_URL . '/views/booking/status.php');
    exit;
}

// Get booking items
$itemsQuery = "SELECT bi.*, i.name as item_name, i.image_url, i.category
               FROM booking_items bi
               LEFT JOIN items i ON bi.item_id = i.id
               WHERE bi.booking_id = ?";
$stmt = $db->prepare($itemsQuery);
$stmt->execute([$bookingId]);
$bookingItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If no items in booking_items, use main booking (backward compatibility)
if (empty($bookingItems)) {
    $itemQuery = "SELECT i.name as item_name, i.image_url, i.price_per_day, i.category
                  FROM items i
                  WHERE i.id = ?";
    $stmt = $db->prepare($itemQuery);
    $stmt->execute([$booking['item_id']]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $bookingItems = [[
        'item_name' => $item['item_name'],
        'image_url' => $item['image_url'],
        'category' => $item['category'],
        'quantity' => $booking['quantity'],
        'price_per_day' => $item['price_per_day'],
        'start_date' => $booking['start_date'],
        'end_date' => $booking['end_date'],
        'subtotal' => $booking['total_price']
    ]];
}

$pageTitle = 'Checkout - ' . APP_NAME;
$activePage = 'my-bookings';

include __DIR__ . '/../header.php';
include __DIR__ . '/../topnav.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bi bi-credit-card me-2"></i>Checkout - Booking #<?= $bookingId ?></h4>
                </div>
                <div class="card-body">
                    <!-- Booking Items -->
                    <h5 class="mb-3">Booking Items (<?= count($bookingItems) ?> items)</h5>
                    
                    <?php foreach ($bookingItems as $item): ?>
                    <div class="card mb-3 border">
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
                                    <p class="text-muted mb-1 small">
                                        <i class="bi bi-tag me-1"></i><?= htmlspecialchars($item['category']) ?>
                                    </p>
                                    <p class="text-muted mb-1 small">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        <?= formatDate($item['start_date']) ?> - <?= formatDate($item['end_date']) ?>
                                    </p>
                                    <p class="text-muted mb-0 small">
                                        <i class="bi bi-box me-1"></i>
                                        Quantity: <?= $item['quantity'] ?> × <?= calculateDays($item['start_date'], $item['end_date']) ?> days
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <p class="mb-1 small text-muted"><?= formatRupiah($item['price_per_day']) ?>/day</p>
                                    <h6 class="text-success mb-0"><?= formatRupiah($item['subtotal']) ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <hr>

                    <!-- Price Summary -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Items:</span>
                                <strong><?= count($bookingItems) ?> items</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <h5>Total Amount:</h5>
                                <h5 class="text-success"><?= formatRupiah($booking['total_price']) ?></h5>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <h5 class="mb-3">Payment Method</h5>
                    <form action="<?= APP_URL ?>/process_payment.php" method="POST" id="paymentForm">
                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                        <input type="hidden" name="payment_method" value="bank_transfer">

                        <div class="mb-4">
                            <!-- Bank Transfer (Only Option) -->
                            <div class="card border-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-bank fs-3 text-primary me-3"></i>
                                        <div>
                                            <h5 class="mb-0">Bank Transfer</h5>
                                            <small class="text-muted">Complete your payment via bank transfer</small>
                                        </div>
                                    </div>
                                    
                                    <!-- Bank Transfer Details (Always Visible) -->
                                    <div id="bankTransferDetails">
                                        <div class="mb-3">
                                            <label class="form-label">Select Bank <span class="text-danger">*</span></label>
                                            <select class="form-select" name="bank_name" id="bankName" required>
                                                <option value="">-- Choose Bank --</option>
                                                <option value="BCA">BCA (Bank Central Asia)</option>
                                                <option value="BRI">BRI (Bank Rakyat Indonesia)</option>
                                                <option value="BNI">BNI (Bank Negara Indonesia)</option>
                                                <option value="Mandiri">Bank Mandiri</option>
                                                <option value="CIMB">CIMB Niaga</option>
                                                <option value="Permata">Bank Permata</option>
                                                <option value="BSI">BSI (Bank Syariah Indonesia)</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Account Number <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="account_number" id="accountNumber"
                                                placeholder="Enter your account number" maxlength="20" required>
                                            <small class="text-muted">Enter the account number you'll transfer from</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Account Holder Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="account_holder" id="accountHolder"
                                                placeholder="Enter account holder name" required>
                                        </div>

                                        <div class="alert alert-info">
                                            <h6 class="mb-2"><i class="bi bi-info-circle me-2"></i>Transfer to:</h6>
                                            <p class="mb-1"><strong>Bank BCA</strong></p>
                                            <p class="mb-1">Account Number: <strong>1234567890</strong></p>
                                            <p class="mb-0">Account Name: <strong>Camping Rental Indonesia</strong></p>
                                        </div>

                                        <div class="alert alert-warning">
                                            <h6 class="mb-2"><i class="bi bi-exclamation-triangle me-2"></i>Important:</h6>
                                            <ul class="mb-0 ps-3">
                                                <li>Please transfer the exact amount shown above</li>
                                                <li>Keep your transfer receipt for verification</li>
                                                <li>Your booking will be automatically confirmed after submission</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and
                                    Conditions</a>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100" id="submitBtn">
                            <i class="bi bi-check-circle me-2"></i>Confirm Payment
                        </button>
                        <a href="<?= APP_URL ?>/views/booking/status.php?booking_id=<?= $bookingId ?>" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="bi bi-arrow-left me-2"></i>Back to Booking Status
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Rental Period</h6>
                <p>The rental period is calculated based on the dates selected during booking.</p>

                <h6>2. Payment</h6>
                <p>Full payment must be completed before item pickup.</p>

                <h6>3. Cancellation</h6>
                <p>Cancellations made 24 hours before the rental start date are eligible for a full refund.</p>

                <h6>4. Item Condition</h6>
                <p>Items must be returned in the same condition as received. Damages will incur additional charges.</p>

                <h6>5. Late Returns</h6>
                <p>Late returns will be charged an additional fee per day.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Format account number (only numbers)
    const accountNumber = document.getElementById('accountNumber');
    accountNumber.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    });

    // Form submission validation
    const paymentForm = document.getElementById('paymentForm');
    const bankName = document.getElementById('bankName');
    const accountHolder = document.getElementById('accountHolder');

    paymentForm.addEventListener('submit', function(e) {
        if (!bankName.value || !accountNumber.value || !accountHolder.value) {
            e.preventDefault();
            alert('Please fill in all bank transfer details');
            return false;
        }

        // Confirm before submit
        const confirmed = confirm(
            `Please confirm your payment details:\n\n` +
            `Bank: ${bankName.options[bankName.selectedIndex].text}\n` +
            `Account Number: ${accountNumber.value}\n` +
            `Account Holder: ${accountHolder.value}\n\n` +
            `Total Amount: <?= formatRupiah($booking['total_price']) ?>\n\n` +
            `Click OK to proceed with payment.`
        );

        if (!confirmed) {
            e.preventDefault();
            return false;
        }
    });
</script>

<?php include __DIR__ . '/../footer.php'; ?>