<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/CartController.php';
require_once __DIR__ . '/../../lib/auth.php';

Auth::requireLogin();

$controller = new CartController();
$data = $controller->index();

$pageTitle = 'Shopping Cart - ' . APP_NAME;
$activePage = 'cart';

include __DIR__ . '/../header.php';
include __DIR__ . '/../topnav.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">
                <i class="bi bi-cart3 me-2"></i>Shopping Cart
                <?php if ($data['count']['total_items'] > 0): ?>
                    <span class="badge bg-primary"><?= $data['count']['total_items'] ?> Items</span>
                <?php endif; ?>
            </h2>

            <?php if (empty($data['items'])): ?>
                <!-- Empty Cart -->
                <div class="text-center py-5">
                    <i class="bi bi-cart-x text-muted" style="font-size: 5rem;"></i>
                    <h3 class="mt-3 text-muted">Your cart is empty</h3>
                    <p class="text-muted">Start adding items to your cart!</p>
                    <a href="<?= APP_URL ?>/index.php" class="btn btn-primary btn-lg mt-3">
                        <i class="bi bi-shop me-2"></i>Browse Items
                    </a>
                </div>
            <?php else: ?>
                <!-- Cart Items -->
                <div class="row">
                    <!-- Cart Items List -->
                    <div class="col-lg-8">
                        <?php foreach ($data['items'] as $item): ?>
                            <div class="card mb-3 cart-item" data-cart-id="<?= $item['id'] ?>">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <!-- Item Image -->
                                        <div class="col-md-2">
                                            <?php if (!empty($item['image_url'])): ?>
                                                <img src="<?= APP_URL . '/' . htmlspecialchars($item['image_url']) ?>"
                                                    class="img-fluid rounded"
                                                    alt="<?= htmlspecialchars($item['item_name']) ?>"
                                                    style="height: 80px; width: 100%; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center"
                                                    style="height: 80px;">
                                                    <i class="bi bi-image text-white fs-2"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Item Details -->
                                        <div class="col-md-4">
                                            <h5 class="mb-1"><?= htmlspecialchars($item['item_name']) ?></h5>
                                            <p class="text-muted mb-1 small">
                                                <i class="bi bi-tag me-1"></i><?= htmlspecialchars($item['category']) ?>
                                            </p>
                                            <p class="text-primary mb-0">
                                                <strong><?= formatRupiah($item['price_per_day']) ?></strong>/day
                                            </p>
                                        </div>

                                        <!-- Rental Period -->
                                        <div class="col-md-3">
                                            <p class="mb-1 small">
                                                <i class="bi bi-calendar-event me-1"></i>
                                                <strong>Period:</strong>
                                            </p>
                                            <p class="mb-1 small"><?= formatDate($item['start_date'], 'd M Y') ?></p>
                                            <p class="mb-1 small"><?= formatDate($item['end_date'], 'd M Y') ?></p>
                                            <p class="mb-0 small text-muted">
                                                Duration: <?= $item['duration'] ?> days
                                            </p>
                                        </div>

                                        <!-- Quantity & Price -->
                                        <div class="col-md-2">
                                            <div class="input-group input-group-sm mb-2">
                                                <button class="btn btn-outline-secondary btn-decrease"
                                                    onclick="updateQuantity(<?= $item['id'] ?>, -1)">
                                                    <i class="bi bi-dash"></i>
                                                </button>
                                                <input type="number"
                                                    class="form-control text-center quantity-input"
                                                    value="<?= $item['quantity'] ?>"
                                                    min="1"
                                                    max="<?= $item['stock'] ?>"
                                                    readonly
                                                    id="qty-<?= $item['id'] ?>">
                                                <button class="btn btn-outline-secondary btn-increase"
                                                    onclick="updateQuantity(<?= $item['id'] ?>, 1)">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                            <p class="mb-0 text-end">
                                                <strong class="text-success subtotal" id="subtotal-<?= $item['id'] ?>">
                                                    <?= formatRupiah($item['subtotal']) ?>
                                                </strong>
                                            </p>
                                        </div>

                                        <!-- Remove Button -->
                                        <div class="col-md-1 text-end">
                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="removeItem(<?= $item['id'] ?>)"
                                                title="Remove from cart">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <?php if (!empty($item['notes'])): ?>
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <small class="text-muted">
                                                    <i class="bi bi-chat-left-text me-1"></i>
                                                    <?= htmlspecialchars($item['notes']) ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Clear Cart Button -->
                        <div class="text-end mt-3">
                            <button class="btn btn-outline-danger" onclick="clearCart()">
                                <i class="bi bi-trash me-2"></i>Clear Cart
                            </button>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="col-lg-4">
                        <div class="card sticky-top" style="top: 100px;">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-receipt me-2"></i>Order Summary
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total Items:</span>
                                    <strong id="total-items"><?= $data['count']['total_items'] ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total Quantity:</span>
                                    <strong id="total-quantity"><?= $data['count']['total_quantity'] ?></strong>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <h5>Total Price:</h5>
                                    <h5 class="text-success" id="total-price">
                                        <?= formatRupiah($data['total']) ?>
                                    </h5>
                                </div>

                                <form action="<?= APP_URL ?>/process_cart.php" method="POST">
                                    <input type="hidden" name="action" value="checkout">
                                    <button type="submit" class="btn btn-success btn-lg w-100 mb-2">
                                        <i class="bi bi-credit-card me-2"></i>Checkout
                                    </button>
                                </form>

                                <a href="<?= APP_URL ?>/index.php" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Update quantity
    function updateQuantity(cartId, change) {
        const qtyInput = document.getElementById(`qty-${cartId}`);
        let newQty = parseInt(qtyInput.value) + change;

        if (newQty < 1) return;

        fetch('<?= APP_URL ?>/process_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_quantity&cart_id=${cartId}&quantity=${newQty}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    qtyInput.value = newQty;
                    document.getElementById(`subtotal-${cartId}`).textContent =
                        formatRupiah(data.subtotal);
                    document.getElementById('total-price').textContent =
                        formatRupiah(data.cart_total);
                    updateCartBadge();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update quantity');
            });
    }

    // Remove item
    function removeItem(cartId) {
        if (!confirm('Remove this item from cart?')) return;

        fetch('<?= APP_URL ?>/process_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=remove&cart_id=${cartId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`[data-cart-id="${cartId}"]`).remove();
                    document.getElementById('total-price').textContent =
                        formatRupiah(data.cart_total);
                    updateCartBadge();

                    if (data.cart_count == 0) {
                        location.reload();
                    }
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to remove item');
            });
    }

    // Clear cart
    function clearCart() {
        if (!confirm('Are you sure you want to clear your cart?')) return;

        fetch('<?= APP_URL ?>/process_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=clear'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to clear cart');
            });
    }

    // Format rupiah
    function formatRupiah(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    // Update cart badge in navbar
    function updateCartBadge() {
        fetch('<?= APP_URL ?>/process_cart.php?action=get_count')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const badge = document.querySelector('.cart-badge');
                    if (badge) {
                        badge.textContent = data.count;
                        if (data.count == 0) {
                            badge.style.display = 'none';
                        }
                    }
                }
            });
    }
</script>

<?php include __DIR__ . '/../footer.php'; ?>