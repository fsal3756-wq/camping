<?php
/**
 * ============================================
 * ADMIN - KTP Verification List
 * ============================================
 * File: admin_verify_ktp_list.php
 * Location: /camping-rental-apps/admin/verify_ktp_list.php
 * 
 * Purpose:
 * - Display list of all KTP submissions
 * - Filter by verification status
 * - Allow admin to view, approve, or reject KTP
 * - Show user information
 * ============================================
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/functions.php';
require_once __DIR__ . '/../models/User.php';

// Require admin access
Auth::requireAdmin();

$userModel = new User();

// Get filter status
$filterStatus = isset($_GET['status']) ? $_GET['status'] : 'all';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get KTP list with filters
$ktpList = $userModel->getKTPList($filterStatus, $searchQuery);

// Count by status
$statusCounts = $userModel->getKTPStatusCounts();

$pageTitle = 'KTP Verification - ' . APP_NAME;
$activePage = 'verify_ktp';

include __DIR__ . '/header.php';
include __DIR__ . '/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-credit-card-2-front me-2"></i>KTP Verification</h2>
                <p class="text-muted mb-0">Manage and verify user KTP submissions</p>
            </div>
            <div>
                <a href="<?= APP_URL ?>/admin/index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-left-warning shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Pending
                                </div>
                                <div class="h5 mb-0 font-weight-bold">
                                    <?= $statusCounts['pending'] ?? 0 ?>
                                </div>
                            </div>
                            <div>
                                <i class="bi bi-clock-history text-warning" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-left-success shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Approved
                                </div>
                                <div class="h5 mb-0 font-weight-bold">
                                    <?= $statusCounts['approved'] ?? 0 ?>
                                </div>
                            </div>
                            <div>
                                <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-left-danger shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Rejected
                                </div>
                                <div class="h5 mb-0 font-weight-bold">
                                    <?= $statusCounts['rejected'] ?? 0 ?>
                                </div>
                            </div>
                            <div>
                                <i class="bi bi-x-circle text-danger" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-left-info shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Uploads
                                </div>
                                <div class="h5 mb-0 font-weight-bold">
                                    <?= $statusCounts['total'] ?? 0 ?>
                                </div>
                            </div>
                            <div>
                                <i class="bi bi-file-earmark-image text-info" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Filter by Status</label>
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>All Status</option>
                            <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="approved" <?= $filterStatus === 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="rejected" <?= $filterStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                            <option value="no_ktp" <?= $filterStatus === 'no_ktp' ? 'selected' : '' ?>>No KTP</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Search User</label>
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Search by name, username, or email..."
                               value="<?= htmlspecialchars($searchQuery) ?>">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- KTP List Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>KTP Submissions
                    <?php if ($filterStatus !== 'all'): ?>
                        <span class="badge bg-primary"><?= ucfirst($filterStatus) ?></span>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($ktpList)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                        <p class="text-muted mt-3">No KTP submissions found.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>KTP Status</th>
                                    <th>Uploaded At</th>
                                    <th>Verified At</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ktpList as $item): ?>
                                    <tr>
                                        <td>#<?= $item['id'] ?></td>
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars($item['full_name']) ?></strong>
                                                <br>
                                                <small class="text-muted">@<?= htmlspecialchars($item['username']) ?></small>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($item['email']) ?></td>
                                        <td><?= htmlspecialchars($item['phone'] ?? '-') ?></td>
                                        <td>
                                            <?php if (empty($item['ktp_image'])): ?>
                                                <span class="badge bg-secondary">No KTP</span>
                                            <?php else: ?>
                                                <?php
                                                $statusBadge = [
                                                    'pending' => 'warning',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger'
                                                ];
                                                $badgeColor = $statusBadge[$item['ktp_verified']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $badgeColor ?>">
                                                    <?= ucfirst($item['ktp_verified'] ?? 'pending') ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($item['ktp_image'])): ?>
                                                <?= formatDate($item['ktp_uploaded_at'], 'd M Y H:i') ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($item['ktp_verified_at'])): ?>
                                                <?= formatDate($item['ktp_verified_at'], 'd M Y H:i') ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($item['ktp_image'])): ?>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= APP_URL ?>/view_ktp.php?user_id=<?= $item['id'] ?>" 
                                                       target="_blank"
                                                       class="btn btn-outline-primary"
                                                       title="View KTP">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    
                                                    <?php if ($item['ktp_verified'] !== 'approved'): ?>
                                                        <button type="button"
                                                                class="btn btn-outline-success"
                                                                onclick="verifyKTP(<?= $item['id'] ?>, 'approved')"
                                                                title="Approve">
                                                            <i class="bi bi-check-circle"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($item['ktp_verified'] !== 'rejected'): ?>
                                                        <button type="button"
                                                                class="btn btn-outline-danger"
                                                                onclick="verifyKTP(<?= $item['id'] ?>, 'rejected')"
                                                                title="Reject">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">No KTP</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Verification Modal -->
<div class="modal fade" id="verifyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="verifyMessage"></p>
                <div class="mb-3">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea id="verifyNotes" class="form-control" rows="3" 
                              placeholder="Enter verification notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmVerifyBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentUserId = null;
let currentAction = null;

function verifyKTP(userId, action) {
    currentUserId = userId;
    currentAction = action;
    
    const messages = {
        'approved': 'Are you sure you want to <strong>APPROVE</strong> this KTP?',
        'rejected': 'Are you sure you want to <strong>REJECT</strong> this KTP?'
    };
    
    document.getElementById('verifyMessage').innerHTML = messages[action];
    document.getElementById('verifyNotes').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('verifyModal'));
    modal.show();
}

document.getElementById('confirmVerifyBtn').addEventListener('click', function() {
    const notes = document.getElementById('verifyNotes').value;
    
    // Disable button
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    
    // Send AJAX request
    fetch('<?= APP_URL ?>/admin/process_verify_ktp.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            user_id: currentUserId,
            action: currentAction,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert(data.message);
            // Reload page
            location.reload();
        } else {
            alert('Error: ' + data.message);
            this.disabled = false;
            this.innerHTML = 'Confirm';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        this.disabled = false;
        this.innerHTML = 'Confirm';
    });
});
</script>

<style>
.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}
.border-left-success {
    border-left: 4px solid #28a745 !important;
}
.border-left-danger {
    border-left: 4px solid #dc3545 !important;
}
.border-left-info {
    border-left: 4px solid #17a2b8 !important;
}
.text-xs {
    font-size: 0.7rem;
}
</style>

<?php include __DIR__ . '/footer.php'; ?>