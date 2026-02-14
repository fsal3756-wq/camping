<!-- Admin Sidebar -->
<style>
.sidebar {
    width: 280px;
    min-height: 100vh;
    background: linear-gradient(180deg, #1e293b 0%, #334155 100%);
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.sidebar-brand {
    padding: 30px 25px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(0, 0, 0, 0.2);
}

.sidebar-brand h4 {
    color: white;
    font-weight: 700;
    margin: 0;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 12px;
}

.sidebar-brand h4 i {
    font-size: 2rem;
    color: #667eea;
}

.sidebar-brand .admin-label {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.6);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: 5px;
}

.sidebar-menu {
    padding: 20px 0;
}

.nav-item {
    margin: 0;
}

.nav-link {
    color: rgba(255, 255, 255, 0.7);
    padding: 15px 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
    text-decoration: none;
}

.nav-link i {
    font-size: 1.3rem;
    width: 25px;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.05);
    color: white;
    border-left-color: #667eea;
}

.nav-link.active {
    background: rgba(102, 126, 234, 0.2);
    color: white;
    border-left-color: #667eea;
    font-weight: 600;
}

.nav-link.active i {
    color: #667eea;
}

.sidebar-divider {
    height: 1px;
    background: rgba(255, 255, 255, 0.1);
    margin: 15px 25px;
}

.sidebar-section-title {
    color: rgba(255, 255, 255, 0.5);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 10px 25px;
    font-weight: 700;
}

.sidebar-footer {
    padding: 25px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: auto;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 15px;
}

.user-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.2rem;
}

.user-details {
    flex: 1;
}

.user-name {
    color: white;
    font-weight: 600;
    font-size: 0.95rem;
    margin: 0;
}

.user-role {
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.75rem;
}

.btn-logout {
    width: 100%;
    padding: 10px;
    background: rgba(239, 68, 68, 0.2);
    color: #fca5a5;
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-logout:hover {
    background: rgba(239, 68, 68, 0.3);
    color: #fca5a5;
    border-color: rgba(239, 68, 68, 0.5);
}

/* Badge for new feature */
.badge-new {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    font-size: 0.65rem;
    padding: 3px 8px;
    border-radius: 10px;
    font-weight: 700;
    margin-left: auto;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        min-height: auto;
        position: relative;
    }
}
</style>

<nav class="sidebar">
    <div class="sidebar-brand">
        <h4>
            <i class="bi bi-gear-fill"></i>
            Admin Panel
        </h4>
        <div class="admin-label">Management System</div>
    </div>

    <div class="sidebar-menu">
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/admin/index.php">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <div class="sidebar-divider"></div>
            <div class="sidebar-section-title">Booking Management</div>

            <!-- Manual Booking - NEW FEATURE -->
            <li class="nav-item">
                <a class="nav-link <?= $activePage === 'manual-booking' ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/admin/manual_booking.php">
                    <i class="bi bi-calendar-plus"></i>
                    <span>Manual Booking</span>
                    <span class="badge-new">NEW</span>
                </a>
            </li>

            <!-- Bookings -->
            <li class="nav-item">
                <a class="nav-link <?= $activePage === 'bookings' ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/admin/bookings.php">
                    <i class="bi bi-calendar-check"></i>
                    <span>All Bookings</span>
                </a>
            </li>

            <!-- Payments -->
            <li class="nav-item">
                <a class="nav-link <?= $activePage === 'payments' ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/admin/payments.php">
                    <i class="bi bi-cash-stack"></i>
                    <span>Payments</span>
                </a>
            </li>

            <div class="sidebar-divider"></div>
            <div class="sidebar-section-title">Inventory</div>

            <!-- Items -->
            <li class="nav-item">
                <a class="nav-link <?= $activePage === 'items' ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/admin/items.php">
                    <i class="bi bi-box-seam"></i>
                    <span>Items</span>
                </a>
            </li>

            <div class="sidebar-divider"></div>
            <div class="sidebar-section-title">User Management</div>

            <!-- Users -->
            <li class="nav-item">
                <a class="nav-link <?= $activePage === 'users' ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/admin/users.php">
                    <i class="bi bi-people"></i>
                    <span>Users</span>
                </a>
            </li>

            <div class="sidebar-divider"></div>
            <div class="sidebar-section-title">Reports</div>

            <!-- Reports -->
            <li class="nav-item">
                <a class="nav-link <?= $activePage === 'reports' ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/admin/reports.php">
                    <i class="bi bi-graph-up"></i>
                    <span>Reports</span>
                </a>
            </li>

            <div class="sidebar-divider"></div>
            <div class="sidebar-section-title">Other</div>

            <!-- Settings -->
            <li class="nav-item">
                <a class="nav-link <?= $activePage === 'settings' ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/admin/settings.php">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
            </li>

            <!-- Back to Site -->
            <li class="nav-item">
                <a class="nav-link" href="<?= APP_URL ?>/index.php">
                    <i class="bi bi-house-door"></i>
                    <span>Back to Site</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <?php $user = Auth::user(); ?>
        <div class="user-info">
            <div class="user-avatar">
                <?= strtoupper(substr($user['username'] ?? 'A', 0, 1)) ?>
            </div>
            <div class="user-details">
                <div class="user-name"><?= htmlspecialchars($user['username'] ?? 'Admin') ?></div>
                <div class="user-role"><?= ucfirst($user['role'] ?? 'Administrator') ?></div>
            </div>
        </div>
        <form action="<?= APP_URL ?>/logout.php" method="POST">
            <button type="submit" class="btn-logout">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
            </button>
        </form>
    </div>
</nav>