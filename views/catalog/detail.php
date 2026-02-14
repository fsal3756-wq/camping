<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/CatalogController.php';
require_once __DIR__ . '/../../lib/auth.php';

$itemId = $_GET['id'] ?? null;

if (!$itemId) {
    header('Location: ' . APP_URL . '/index.php');
    exit;
}

$controller = new CatalogController();
$item = $controller->detail($itemId);

$pageTitle = $item['name'] . ' - ' . APP_NAME;
$activePage = 'catalog';

include __DIR__ . '/../header.php';
include __DIR__ . '/../topnav.php';
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>/index.php">Catalog</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($item['name']) ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Image -->
        <div class="col-md-6">
            <?php if ($item['image_url']): ?>
                <img src="<?= APP_URL . '/' . htmlspecialchars($item['image_url']) ?>" 
                     class="img-fluid rounded shadow"
                     alt="<?= htmlspecialchars($item['name']) ?>">
            <?php else: ?>
                <div class="bg-secondary d-flex align-items-center justify-content-center rounded" style="height: 400px;">
                    <i class="bi bi-image text-white" style="font-size: 5rem;"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Details -->
        <div class="col-md-6">
            <span class="badge bg-info mb-2"><?= htmlspecialchars($item['category']) ?></span>
            <h2 class="mb-3"><?= htmlspecialchars($item['name']) ?></h2>

            <div class="mb-3">
                <?php if ($item['total_reviews'] > 0): ?>
                    <div class="d-flex align-items-center">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star-fill <?= $i <= round($item['avg_rating']) ? 'text-warning' : 'text-muted' ?>"></i>
                        <?php endfor; ?>
                        <span class="ms-2">(<?= $item['total_reviews'] ?> reviews)</span>
                    </div>
                <?php else: ?>
                    <span class="text-muted">Belum ada review</span>
                <?php endif; ?>
            </div>

            <h3 class="text-primary mb-3">
                <?= formatRupiah($item['price_per_day']) ?> 
                <small class="text-muted">/hari</small>
            </h3>

            <div class="mb-3">
                <span class="badge bg-<?= $item['quantity_available'] > 0 ? 'success' : 'danger' ?> fs-6">
                    <?= $item['quantity_available'] > 0 ? 'Tersedia: ' . $item['quantity_available'] : 'Tidak Tersedia' ?>
                </span>
            </div>

            <p class="lead"><?= nl2br(htmlspecialchars($item['description'])) ?></p>

            <hr>

            <!-- Booking Form -->
            <?php if (Auth::check()): ?>
                <form id="bookingForm">
                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" 
                                   name="start_date" 
                                   class="form-control" 
                                   required 
                                   min="<?= date('Y-m-d') ?>"
                                   id="startDate">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" 
                                   name="end_date" 
                                   class="form-control" 
                                   required 
                                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                   id="endDate">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Jumlah</label>
                            <input type="number" 
                                   name="quantity" 
                                   class="form-control" 
                                   value="1" 
                                   min="1"
                                   max="<?= $item['quantity_available'] ?>" 
                                   required
                                   id="quantity">
                            <small class="text-muted">Tersedia: <?= $item['quantity_available'] ?> unit</small>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Catatan (Opsional)</label>
                            <textarea name="notes" 
                                      class="form-control" 
                                      rows="3"
                                      placeholder="Catatan khusus untuk pesanan Anda..."
                                      id="notes"></textarea>
                        </div>

                        <!-- ACTION BUTTONS -->
                        <div class="col-md-12">
                            <div class="d-grid">
                                <!-- Add to Cart Button -->
                                <button type="button" 
                                        class="btn btn-primary btn-lg" 
                                        onclick="addToCart()"
                                        id="addToCartBtn"
                                        <?= $item['quantity_available'] <= 0 ? 'disabled' : '' ?>>
                                    <i class="bi bi-cart-plus me-2"></i>Tambah ke Keranjang
                                </button>
                            </div>
                            
                            <?php if ($item['quantity_available'] <= 0): ?>
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Maaf, item ini sedang tidak tersedia
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Silakan <a href="<?= APP_URL ?>/login.php">login</a> untuk melakukan booking
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reviews -->
    <?php if (!empty($item['reviews'])): ?>
        <div class="row mt-5">
            <div class="col-md-12">
                <h4 class="mb-4">Customer Reviews</h4>
                <?php foreach ($item['reviews'] as $review): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($review['full_name'] ?? $review['username']) ?></h6>
                                    <div class="mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star-fill <?= $i <= $review['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <small class="text-muted"><?= formatDate($review['created_at']) ?></small>
                            </div>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="cartToast" class="toast hide" role="alert">
        <div class="toast-header bg-success text-white">
            <i class="bi bi-check-circle me-2"></i>
            <strong class="me-auto">Berhasil!</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            Item berhasil ditambahkan ke keranjang!
            <div class="mt-2">
                <a href="<?= APP_URL ?>/views/cart/index.php" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-cart3 me-1"></i>Lihat Keranjang
                </a>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
// Set minimum end date based on start date
document.getElementById('startDate').addEventListener('change', function() {
    const startDate = new Date(this.value);
    const endDateInput = document.getElementById('endDate');
    const minEndDate = new Date(startDate);
    minEndDate.setDate(minEndDate.getDate() + 1);
    endDateInput.min = minEndDate.toISOString().split('T')[0];
    
    // Reset end date if it's before new minimum
    if (endDateInput.value && new Date(endDateInput.value) < minEndDate) {
        endDateInput.value = '';
    }
});

// Add to Cart Function
function addToCart() {
    const form = document.getElementById('bookingForm');
    const addToCartBtn = document.getElementById('addToCartBtn');
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Get form data
    const formData = new FormData(form);
    formData.append('action', 'add');

    // Disable button and show loading
    addToCartBtn.disabled = true;
    const originalHTML = addToCartBtn.innerHTML;
    addToCartBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menambahkan...';

    // Send AJAX request
    fetch('<?= APP_URL ?>/process_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success toast
            showToast();
            
            // Update cart badge
            updateCartBadge(data.cart_count);
            
            // Reset form
            form.reset();
            document.getElementById('startDate').value = '';
            document.getElementById('endDate').value = '';
            document.getElementById('quantity').value = '1';
            document.getElementById('notes').value = '';
            
            // Show success alert (alternative to toast)
            // alert('✅ ' + data.message);
        } else {
            // Show error
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan. Silakan coba lagi.');
    })
    .finally(() => {
        // Re-enable button
        addToCartBtn.disabled = false;
        addToCartBtn.innerHTML = originalHTML;
    });
}

// Show Toast Notification
function showToast() {
    const toastElement = document.getElementById('cartToast');
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
}

// Update Cart Badge
function updateCartBadge(count) {
    const badge = document.querySelector('.cart-badge');
    
    if (badge) {
        badge.textContent = count;
        if (count > 0) {
            badge.style.display = 'flex';
            // Animate badge
            badge.style.animation = 'none';
            setTimeout(() => {
                badge.style.animation = 'badgePop 0.3s ease';
            }, 10);
        }
    } else if (count > 0) {
        // Create badge if doesn't exist
        const cartLink = document.querySelector('a[href*="cart/index.php"]');
        if (cartLink) {
            const newBadge = document.createElement('span');
            newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-badge';
            newBadge.textContent = count;
            newBadge.innerHTML += '<span class="visually-hidden">items in cart</span>';
            cartLink.appendChild(newBadge);
        }
    }
}
</script>

<style>
/* Button Hover Effects */
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(13, 110, 253, 0.3);
    transition: all 0.3s ease;
}

/* Toast Styles */
.toast {
    min-width: 300px;
}

.toast-header {
    border-bottom: none;
}

.toast-body {
    padding: 1rem;
}

/* Loading State */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Form Validation */
.form-control:invalid {
    border-color: #dc3545;
}

.form-control:valid {
    border-color: #198754;
}

/* Disabled Button */
button:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

/* Badge Animation */
@keyframes badgePop {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
    }
}
</style>

<?php include __DIR__ . '/../footer.php'; ?>