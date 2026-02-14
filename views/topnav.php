<?php
// Get cart count if user is logged in
$cartCount = 0;
if ($isLoggedIn) {
    try {
        require_once __DIR__ . '/../models/Cart.php';
        $cartModel = new Cart();
        $count = $cartModel->getCartCount($user['id']);
        $cartCount = $count['total_items'] ?? 0;
    } catch (Exception $e) {
        // Silently fail if cart model not available
        $cartCount = 0;
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= APP_URL ?>/index.php">
            <i class="bi bi-tree-fill me-2"></i>
            <?= APP_NAME ?>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?= ($activePage ?? '') === 'catalog' ? 'active' : '' ?>"
                        href="<?= APP_URL ?>/index.php">
                        <i class="bi bi-grid"></i> Catalog
                    </a>
                </li>

                <?php if ($isLoggedIn): ?>
                    <!-- Cart Icon with Badge -->
                    <li class="nav-item">
                        <a class="nav-link position-relative <?= ($activePage ?? '') === 'cart' ? 'active' : '' ?>" 
                           href="<?= APP_URL ?>/views/cart/index.php"
                           title="Shopping Cart">
                            <i class="bi bi-cart3 fs-5"></i>
                            <?php if ($cartCount > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-badge">
                                    <?= $cartCount ?>
                                    <span class="visually-hidden">items in cart</span>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage ?? '') === 'my-bookings' ? 'active' : '' ?>"
                            href="<?= APP_URL ?>/views/booking/status.php">
                            <i class="bi bi-bag-check"></i> My Bookings
                        </a>
                    </li>

                    <?php if ($isAdmin): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= APP_URL ?>/admin/index.php">
                                <i class="bi bi-speedometer2"></i> Admin Panel
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['username']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/views/profile.php">
                                    <i class="bi bi-person"></i> Profile
                                </a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/logout.php">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>/login.php">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>/register.php">
                            <i class="bi bi-person-plus"></i> Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<style>
    /* Cart Badge Styles */
    .cart-badge {
        font-size: 0.65rem;
        padding: 0.25em 0.45em;
        min-width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: badgePop 0.3s ease;
    }

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

    /* Cart Icon Hover Effect */
    .nav-link:hover .bi-cart3 {
        transform: scale(1.1);
        transition: transform 0.2s ease;
    }

    /* Active Cart Link */
    .nav-link.active .bi-cart3 {
        color: #ffd700 !important;
    }

    /* Responsive Badge */
    @media (max-width: 768px) {
        .cart-badge {
            font-size: 0.6rem;
            min-width: 16px;
            height: 16px;
        }
    }
</style>