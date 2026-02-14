<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/functions.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Item.php';

Auth::requireAdmin();

$pageTitle = 'Bookings - Admin';
$activePage = 'bookings';

$bookingModel = new Booking();

// Filter
$status = $_GET['status'] ?? null;
$bookings = $bookingModel->getAll(['status' => $status]);

include __DIR__ . '/../views/header.php';
?>

<div class="d-flex">
    <?php include __DIR__ . '/../views/sidebar.php'; ?>

    <div class="flex-grow-1 p-4">
        <div class="container-fluid">

        <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Success!</strong> <?= $_SESSION['success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Error!</strong> <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-calendar-check me-2"></i>Manage Bookings</h2>
            </div>

            <!-- Filter Tabs -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link <?= empty($status) ? 'active' : '' ?>" href="?">
                        All (<?= count($bookingModel->getAll()) ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $status === 'pending' ? 'active' : '' ?>" href="?status=pending">
                        Pending (<?= count($bookingModel->getAll(['status' => 'pending'])) ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $status === 'confirmed' ? 'active' : '' ?>" href="?status=confirmed">
                        Confirmed (<?= count($bookingModel->getAll(['status' => 'confirmed'])) ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $status === 'completed' ? 'active' : '' ?>" href="?status=completed">
                        Completed (<?= count($bookingModel->getAll(['status' => 'completed'])) ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $status === 'cancelled' ? 'active' : '' ?>" href="?status=cancelled">
                        Cancelled (<?= count($bookingModel->getAll(['status' => 'cancelled'])) ?>)
                    </a>
                </li>
            </ul>

            <!-- Bookings Table -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <!-- <th>ID</th> -->
                                    <th>Customer</th>
                                    <th>Item</th>
                                    <th>Dates</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($bookings)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No bookings found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <!-- <td>#<?= $booking['id'] ?></td> -->
                                            <td>
                                                <strong><?= htmlspecialchars($booking['full_name']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($booking['email']) ?></small>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($booking['item_name']) ?><br>
                                                <span class="badge bg-info"><?= htmlspecialchars($booking['category']) ?></span>
                                            </td>
                                            <td>
                                                <?= formatDate($booking['start_date'], 'd M Y') ?><br>
                                                <small class="text-muted">to
                                                    <?= formatDate($booking['end_date'], 'd M Y') ?></small>
                                            </td>
                                            <td><?= $booking['quantity'] ?></td>
                                            <td><?= formatRupiah($booking['total_price']) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'pending' => 'warning',
                                                    'confirmed' => 'info',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger'
                                                ];
                                                ?>
                                                <span class="badge bg-<?= $statusClass[$booking['status']] ?>">
                                                    <?= ucfirst($booking['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($booking['payment_status']): ?>
                                                    <span
                                                        class="badge bg-<?= $booking['payment_status'] === 'completed' ? 'success' : 'warning' ?>">
                                                        <?= ucfirst($booking['payment_status']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No Payment</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-info" onclick="viewBooking(<?= $booking['id'] ?>)">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button class="btn btn-warning"
                                                        onclick="updateStatus(<?= $booking['id'] ?>, '<?= $booking['status'] ?>')">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Booking Modal -->
<div class="modal fade" id="viewBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bookingDetails">
                <div class="text-center">
                    <div class="spinner-border" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Booking Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= APP_URL ?>/admin/process_booking.php" method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="booking_id" id="status_booking_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="status_select" class="form-select" required>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function viewBooking(id) {
        const modal = new bootstrap.Modal(document.getElementById('viewBookingModal'));
        modal.show();

        fetch(`<?= APP_URL ?>/admin/get_booking.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const booking = data.booking;
                    document.getElementById('bookingDetails').innerHTML = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Booking ID</label>
                            <p class="fw-bold">#${booking.id}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Booking Date</label>
                            <p class="fw-bold">${booking.booking_date}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Customer</label>
                            <p class="fw-bold">${booking.full_name}<br>
                            <small>${booking.email}</small><br>
                            <small>${booking.phone || '-'}</small></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Item</label>
                            <p class="fw-bold">${booking.item_name}<br>
                            <span class="badge bg-info">${booking.category}</span></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Rental Period</label>
                            <p class="fw-bold">${booking.start_date} - ${booking.end_date}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Quantity</label>
                            <p class="fw-bold">${booking.quantity}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Status</label>
                            <p><span class="badge bg-warning">${booking.status}</span></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Total Price</label>
                            <p class="fw-bold text-primary">${booking.total_price}</p>
                        </div>
                        ${booking.notes ? `
                        <div class="col-md-12 mb-3">
                            <label class="text-muted">Notes</label>
                            <p>${booking.notes}</p>
                        </div>
                        ` : ''}
                    </div>
                `;
                }
            });
    }

    function updateStatus(id, currentStatus) {
        document.getElementById('status_booking_id').value = id;
        document.getElementById('status_select').value = currentStatus;
        new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
    }
</script>

<?php include __DIR__ . '/../views/footer.php'; ?>