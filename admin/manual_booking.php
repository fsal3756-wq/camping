<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/functions.php';
require_once __DIR__ . '/../models/Item.php';
require_once __DIR__ . '/../models/User.php';

Auth::requireAdmin();

$pageTitle = 'Manual Booking - Admin';
$activePage = 'manual-booking';

$itemModel = new Item();
$userModel = new User();

// Get all available items
$items = $itemModel->getAll(['status' => 'available']);

// Get all customers (non-admin users) - FIXED: Use correct method
$customers = [];
try {
    // Try getAllCustomers method first (if it exists)
    if (method_exists($userModel, 'getAllCustomers')) {
        $customers = $userModel->getAllCustomers();
    } else {
        // Fallback to getAll method
        $customers = $userModel->getAll(['role' => 'user']);
    }
} catch (Exception $e) {
    error_log("Error getting customers: " . $e->getMessage());
    $customers = [];
}

include __DIR__ . '/../views/header.php';
?>

<style>
    .booking-wizard {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px 15px 0 0;
        padding: 30px;
        color: white;
    }
    
    .step-indicator {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        position: relative;
    }
    
    .step-indicator::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 2px;
        background: rgba(255,255,255,0.3);
        z-index: 0;
    }
    
    .step {
        position: relative;
        z-index: 1;
        text-align: center;
        flex: 1;
    }
    
    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255,255,255,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-weight: bold;
        transition: all 0.3s ease;
    }
    
    .step.active .step-circle {
        background: white;
        color: #667eea;
        box-shadow: 0 0 20px rgba(255,255,255,0.5);
        transform: scale(1.1);
    }
    
    .step.completed .step-circle {
        background: #28a745;
        color: white;
    }
    
    .step-label {
        font-size: 0.9rem;
        opacity: 0.8;
    }
    
    .step.active .step-label {
        opacity: 1;
        font-weight: 600;
    }
    
    .form-section {
        display: none;
        animation: fadeIn 0.5s;
    }
    
    .form-section.active {
        display: block;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .customer-card {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .customer-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        transform: translateY(-2px);
    }
    
    .customer-card.selected {
        border-color: #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
    }
    
    .item-card {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .item-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        transform: translateY(-2px);
    }
    
    .item-card.selected {
        border-color: #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
    }
    
    .quantity-control {
        display: flex;
        align-items: center;
        gap: 10px;
        justify-content: center;
        margin-top: 10px;
    }
    
    .quantity-btn {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        border: 2px solid #667eea;
        background: white;
        color: #667eea;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .quantity-btn:hover {
        background: #667eea;
        color: white;
    }
    
    .quantity-input {
        width: 60px;
        text-align: center;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 5px;
        font-weight: bold;
    }
    
    .summary-box {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 25px;
        border: 2px solid #dee2e6;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #dee2e6;
    }
    
    .summary-item:last-child {
        border-bottom: none;
        padding-top: 15px;
        font-size: 1.2rem;
        font-weight: bold;
        color: #28a745;
    }
    
    .navigation-buttons {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        gap: 15px;
    }
    
    .nav-btn {
        flex: 1;
        padding: 12px 30px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .search-box {
        position: relative;
        margin-bottom: 20px;
    }
    
    .search-box input {
        padding-left: 45px;
    }
    
    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }

    .section-divider {
        height: 2px;
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, rgba(102, 126, 234, 0.5) 50%, rgba(102, 126, 234, 0.1) 100%);
        margin: 1.5rem 0;
    }
</style>

<div class="d-flex">
    <?php include __DIR__ . '/../views/sidebar.php'; ?>

    <div class="flex-grow-1 p-4">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Header Card -->
                    <div class="card border-0 shadow-lg mb-4" style="border-radius: 15px; overflow: hidden;">
                        <div class="booking-wizard">
                            <h3 class="mb-1 fw-bold">
                                <i class="bi bi-calendar-plus me-2"></i>Manual Booking
                            </h3>
                            <p class="mb-4 opacity-75">Create booking for walk-in customers</p>
                            
                            <!-- Step Indicator -->
                            <div class="step-indicator">
                                <div class="step active" id="step-1-indicator">
                                    <div class="step-circle">1</div>
                                    <div class="step-label">Customer</div>
                                </div>
                                <div class="step" id="step-2-indicator">
                                    <div class="step-circle">2</div>
                                    <div class="step-label">Items</div>
                                </div>
                                <div class="step" id="step-3-indicator">
                                    <div class="step-circle">3</div>
                                    <div class="step-label">Details</div>
                                </div>
                                <div class="step" id="step-4-indicator">
                                    <div class="step-circle">4</div>
                                    <div class="step-label">Confirm</div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <form id="manualBookingForm" action="<?= APP_URL ?>/admin/proccess_manual_booking.php" method="POST">
                                
                                <!-- Step 1: Select Customer -->
                                <div class="form-section active" id="step-1">
                                    <h5 class="mb-4 fw-bold text-center">
                                        <i class="bi bi-person-check me-2 text-primary"></i>Select or Create Customer
                                    </h5>
                                    
                                    <?php if (empty($customers)): ?>
                                        <div class="alert alert-warning">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            No customers found. Please create a new customer first.
                                        </div>
                                    <?php else: ?>
                                        <!-- Search Customer -->
                                        <div class="search-box">
                                            <i class="bi bi-search"></i>
                                            <input type="text" id="customerSearch" class="form-control form-control-lg" 
                                                   placeholder="Search customer by name, email, or phone...">
                                        </div>
                                        
                                        <!-- Customer List -->
                                        <div class="row g-3" id="customerList">
                                            <?php foreach ($customers as $customer): ?>
                                            <div class="col-md-6 customer-item">
                                                <div class="customer-card" onclick="selectCustomer(<?= $customer['id'] ?>, this)">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                                             style="width: 50px; height: 50px;">
                                                            <i class="bi bi-person-fill text-white fs-4"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1 fw-bold"><?= htmlspecialchars($customer['full_name'] ?? $customer['username']) ?></h6>
                                                            <p class="mb-0 text-muted small">
                                                                <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($customer['email']) ?>
                                                            </p>
                                                            <?php if (!empty($customer['phone'])): ?>
                                                            <p class="mb-0 text-muted small">
                                                                <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($customer['phone']) ?>
                                                            </p>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="user_id" 
                                                                   value="<?= $customer['id'] ?>" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Create New Customer Button -->
                                    <div class="text-center mt-4">
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
                                            <i class="bi bi-person-plus me-2"></i>Create New Customer
                                        </button>
                                    </div>
                                </div>

                                <!-- Step 2: Select Items -->
                                <div class="form-section" id="step-2">
                                    <h5 class="mb-4 fw-bold text-center">
                                        <i class="bi bi-box-seam me-2 text-primary"></i>Select Items to Rent
                                    </h5>
                                    
                                    <!-- Search Items -->
                                    <div class="search-box">
                                        <i class="bi bi-search"></i>
                                        <input type="text" id="itemSearch" class="form-control form-control-lg" 
                                               placeholder="Search items by name or category...">
                                    </div>
                                    
                                    <!-- Items Grid -->
                                    <div class="row g-3" id="itemsList">
                                        <?php foreach ($items as $item): ?>
                                        <div class="col-md-4 item-item">
                                            <div class="item-card" onclick="toggleItem(<?= $item['id'] ?>, this)" 
                                                 data-item-id="<?= $item['id'] ?>"
                                                 data-name="<?= strtolower($item['name']) ?>" 
                                                 data-category="<?= strtolower($item['category']) ?>">
                                                <?php if (!empty($item['image_url'])): ?>
                                                    <img src="<?= APP_URL . '/' . htmlspecialchars($item['image_url']) ?>"
                                                         class="img-fluid rounded mb-3" 
                                                         style="height: 150px; width: 100%; object-fit: cover;"
                                                         alt="<?= htmlspecialchars($item['name']) ?>">
                                                <?php else: ?>
                                                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center mb-3"
                                                         style="height: 150px;">
                                                        <i class="bi bi-image text-white fs-1"></i>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <h6 class="mb-2 fw-bold"><?= htmlspecialchars($item['name']) ?></h6>
                                                <p class="mb-2">
                                                    <span class="badge bg-info"><?= htmlspecialchars($item['category']) ?></span>
                                                </p>
                                                <p class="text-primary fw-bold mb-2"><?= formatRupiah($item['price_per_day']) ?>/day</p>
                                                <p class="text-muted small mb-2">
                                                    <i class="bi bi-box me-1"></i>Available: <?= $item['quantity_available'] ?>
                                                </p>
                                                
                                                <!-- Quantity Control (hidden initially) -->
                                                <div class="quantity-control" style="display: none;">
                                                    <button type="button" class="quantity-btn" onclick="event.stopPropagation(); changeQuantity(<?= $item['id'] ?>, -1)">-</button>
                                                    <input type="number" class="quantity-input" id="qty-<?= $item['id'] ?>" 
                                                           value="1" min="1" max="<?= $item['quantity_available'] ?>" readonly>
                                                    <button type="button" class="quantity-btn" onclick="event.stopPropagation(); changeQuantity(<?= $item['id'] ?>, 1)">+</button>
                                                </div>
                                                
                                                <input type="checkbox"
                                                    class="d-none item-checkbox"
                                                    name="items[]"
                                                    value="<?= $item['id'] ?>"
                                                    data-name="<?= htmlspecialchars($item['name']) ?>"
                                                    data-price="<?= $item['price_per_day'] ?>"
                                                    data-max="<?= $item['quantity_available'] ?>"
                                                    onchange="onItemChange(<?= $item['id'] ?>, this)">

                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div id="noItemsMessage" class="alert alert-info text-center mt-3" style="display: none;">
                                        <i class="bi bi-info-circle me-2"></i>No items found
                                    </div>
                                    
                                    <div class="alert alert-warning mt-3" id="itemSelectionWarning" style="display: none;">
                                        <i class="bi bi-exclamation-triangle me-2"></i>Please select at least one item
                                    </div>
                                </div>

                                <!-- Step 3: Booking Details -->
                                <div class="form-section" id="step-3">
                                    <h5 class="mb-4 fw-bold text-center">
                                        <i class="bi bi-calendar-range me-2 text-primary"></i>Booking Details
                                    </h5>
                                    
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                                            <input type="date" name="start_date" id="start_date" class="form-control form-control-lg" 
                                                   required min="<?= date('Y-m-d') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                                            <input type="date" name="end_date" id="end_date" class="form-control form-control-lg" 
                                                   required min="<?= date('Y-m-d') ?>">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Notes (Optional)</label>
                                            <textarea name="notes" class="form-control" rows="4" 
                                                      placeholder="Add any special notes or requirements..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 4: Confirmation -->
                                <div class="form-section" id="step-4">
                                    <h5 class="mb-4 fw-bold text-center">
                                        <i class="bi bi-check-circle me-2 text-success"></i>Confirm Booking
                                    </h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <h6 class="fw-bold mb-3">
                                                        <i class="bi bi-person-circle me-2 text-primary"></i>Customer Information
                                                    </h6>
                                                    <div id="customerSummary"></div>
                                                </div>
                                            </div>
                                            
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="fw-bold mb-3">
                                                        <i class="bi bi-calendar-event me-2 text-primary"></i>Rental Period
                                                    </h6>
                                                    <div id="dateSummary"></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="summary-box">
                                                <h6 class="fw-bold mb-3">
                                                    <i class="bi bi-receipt me-2"></i>Order Summary
                                                </h6>
                                                <div id="itemsSummary"></div>
                                                
                                                <div class="summary-item">
                                                    <span>Total Payment:</span>
                                                    <span id="totalPrice">Rp 0</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Navigation Buttons -->
                                <div class="navigation-buttons">
                                    <button type="button" class="btn btn-outline-secondary nav-btn" id="prevBtn" onclick="changeStep(-1)">
                                        <i class="bi bi-arrow-left me-2"></i>Previous
                                    </button>
                                    <button type="button" class="btn btn-primary nav-btn" id="nextBtn" onclick="changeStep(1)">
                                        Next<i class="bi bi-arrow-right ms-2"></i>
                                    </button>
                                    <button type="submit" class="btn btn-success nav-btn" id="submitBtn" style="display: none;">
                                        <i class="bi bi-check-circle me-2"></i>Create Booking
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Customer Modal -->
<div class="modal fade" id="newCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div>
                    <h5 class="modal-title text-white fw-bold">
                        <i class="bi bi-person-plus-fill me-2"></i>Create New Customer
                    </h5>
                    <p class="text-white-50 mb-0 small">Fill in customer details</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="newCustomerForm">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                        <input type="tel" name="phone" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Create Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentStep = 1;
const totalSteps = 4;
let selectedItems = new Map(); // Map to store item_id -> quantity

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded. Initializing...');
    updateStepIndicators();
    updateNavigationButtons();
    
    // Date change listeners
    document.getElementById('start_date').addEventListener('change', function() {
        document.getElementById('end_date').min = this.value;
        if (currentStep === 4) updateSummary();
    });
    
    document.getElementById('end_date').addEventListener('change', function() {
        if (currentStep === 4) updateSummary();
    });
});

// Customer Search
document.getElementById('customerSearch')?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const customers = document.querySelectorAll('.customer-item');
    
    customers.forEach(customer => {
        const text = customer.textContent.toLowerCase();
        customer.style.display = text.includes(searchTerm) ? 'block' : 'none';
    });
});

// Item Search
document.getElementById('itemSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const items = document.querySelectorAll('.item-item');
    let visibleCount = 0;
    
    items.forEach(item => {
        const card = item.querySelector('.item-card');
        const name = card.dataset.name;
        const category = card.dataset.category;
        
        if (name.includes(searchTerm) || category.includes(searchTerm)) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    document.getElementById('noItemsMessage').style.display = visibleCount === 0 ? 'block' : 'none';
});

function selectCustomer(userId, element) {
    // Remove selected class from all customer cards
    document.querySelectorAll('.customer-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Add selected class to clicked card
    element.classList.add('selected');
    
    // Check the radio button
    element.querySelector('input[type="radio"]').checked = true;
    console.log('Customer selected:', userId);
}

// Manual Bookin
function onItemChange(itemId, checkbox) {
    const card = checkbox.closest('.item-card');
    const quantityControl = card.querySelector('.quantity-control');

    if (checkbox.checked) {
        card.classList.add('selected');
        quantityControl.style.display = 'flex';
        selectedItems.set(itemId, 1);
        console.log('✓ Item selected (onchange):', itemId);
    } else {
        card.classList.remove('selected');
        quantityControl.style.display = 'none';
        selectedItems.delete(itemId);
        console.log('✗ Item deselected (onchange):', itemId);
    }
}


// FIXED: Toggle item function
function toggleItem(itemId, element) {
    const checkbox = element.querySelector('.item-checkbox');
    checkbox.checked = !checkbox.checked;
    checkbox.dispatchEvent(new Event('change'));
}


function changeQuantity(itemId, change) {
    const input = document.getElementById(`qty-${itemId}`);
    const max = parseInt(input.max);
    let newValue = parseInt(input.value) + change;
    
    if (newValue >= 1 && newValue <= max) {
        input.value = newValue;
        selectedItems.set(itemId, newValue);
        console.log('Quantity updated - Item:', itemId, 'Quantity:', newValue);
    }
}

function changeStep(direction) {
    console.log('═══════════════════════════════════════');
    console.log('Step Change Request');
    console.log('Current Step:', currentStep);
    console.log('Direction:', direction > 0 ? 'Forward' : 'Backward');
    console.log('Selected Items:', selectedItems.size);
    console.log('═══════════════════════════════════════');
    
    // Validation before moving forward
    if (direction === 1) {
        // Step 1: Customer Selection
        if (currentStep === 1) {
            const selectedCustomer = document.querySelector('input[name="user_id"]:checked');
            if (!selectedCustomer) {
                alert('Please select a customer');
                console.log('❌ Validation failed: No customer selected');
                return false;
            }
            console.log('✓ Customer validated:', selectedCustomer.value);
        }
        
        // Step 2: Item Selection
        if (currentStep === 2) {
            console.log('Validating Step 2...');
            console.log('Selected items count:', selectedItems.size);
            
            const warningElement = document.getElementById('itemSelectionWarning');
            
            if (selectedItems.size === 0) {
                if (warningElement) {
                    warningElement.style.display = 'block';
                }
                alert('Please select at least one item');
                console.log('❌ Validation failed: No items selected');
                return false;
            }
            
            if (warningElement) {
                warningElement.style.display = 'none';
            }
            console.log('✓ Items validated. Count:', selectedItems.size);
        }
        
        // Step 3: Date Selection
        if (currentStep === 3) {
            const startDate = document.getElementById('start_date')?.value;
            const endDate = document.getElementById('end_date')?.value;
            
            if (!startDate || !endDate) {
                alert('Please fill in all required fields');
                console.log('❌ Validation failed: Missing dates');
                return false;
            }
            
            const startDateObj = new Date(startDate);
            const endDateObj = new Date(endDate);
            
            if (endDateObj < startDateObj) {
                alert('End date must be after start date');
                console.log('❌ Validation failed: Invalid date range');
                return false;
            }
            console.log('✓ Dates validated');
        }
    }
    
    // Prevent going back from step 1 or forward from last step
    const totalSteps = 4; // Adjust based on your total steps
    if (currentStep + direction < 1 || currentStep + direction > totalSteps) {
        console.log('❌ Cannot move beyond boundaries');
        return false;
    }
    
    // Hide current step
    const currentStepElement = document.getElementById(`step-${currentStep}`);
    if (currentStepElement) {
        currentStepElement.classList.remove('active');
    }
    
    // Update current step
    currentStep += direction;
    console.log('Moving to step:', currentStep);
    
    // Show new step
    const newStepElement = document.getElementById(`step-${currentStep}`);
    if (newStepElement) {
        newStepElement.classList.add('active');
    }
    
    // Update step indicators
    if (typeof updateStepIndicators === 'function') {
        updateStepIndicators();
    }
    
    // Update navigation buttons
    if (typeof updateNavigationButtons === 'function') {
        updateNavigationButtons();
    }
    
    // Update summary if on confirmation step
    if (currentStep === 4) {
        console.log('Reached confirmation step. Updating summary...');
        if (typeof updateSummary === 'function') {
            updateSummary();
        }
    }
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
    console.log('Step change complete ✓');
    
    return true;
}

// Optional: Helper function to update navigation buttons visibility
function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const totalSteps = 4;
    
    // Update Previous button
    if (prevBtn) {
        prevBtn.style.display = currentStep === 1 ? 'none' : 'inline-block';
    }
    
    // Update Next/Submit button
    if (currentStep === totalSteps) {
        if (nextBtn) nextBtn.style.display = 'none';
        if (submitBtn) submitBtn.style.display = 'inline-block';
    } else {
        if (nextBtn) nextBtn.style.display = 'inline-block';
        if (submitBtn) submitBtn.style.display = 'none';
    }
}

// Optional: Helper function to update step indicators
function updateStepIndicators() {
    const totalSteps = 4;
    
    for (let i = 1; i <= totalSteps; i++) {
        const indicator = document.getElementById(`step-indicator-${i}`);
        if (indicator) {
            indicator.classList.remove('active', 'completed');
            
            if (i < currentStep) {
                indicator.classList.add('completed');
            } else if (i === currentStep) {
                indicator.classList.add('active');
            }
        }
    }
}

function updateStepIndicators() {
    for (let i = 1; i <= totalSteps; i++) {
        const indicator = document.getElementById(`step-${i}-indicator`);
        indicator.classList.remove('active', 'completed');
        
        if (i < currentStep) {
            indicator.classList.add('completed');
        } else if (i === currentStep) {
            indicator.classList.add('active');
        }
    }
}

function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    
    prevBtn.style.display = currentStep === 1 ? 'none' : 'block';
    nextBtn.style.display = currentStep === totalSteps ? 'none' : 'block';
    submitBtn.style.display = currentStep === totalSteps ? 'block' : 'none';
}

function updateSummary() {
    console.log('Updating summary...');
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    if (!startDate || !endDate) {
        console.log('Dates not set yet');
        return;
    }
    
    const days = calculateDays(startDate, endDate);
    console.log('Duration:', days, 'days');
    
    // Customer Summary
    const selectedCustomer = document.querySelector('input[name="user_id"]:checked');
    if (selectedCustomer) {
        const customerCard = selectedCustomer.closest('.customer-card');
        const customerName = customerCard.querySelector('h6').textContent;
        const customerEmail = customerCard.querySelector('.bi-envelope').parentElement.textContent.trim();
        const phoneElement = customerCard.querySelector('.bi-telephone');
        const customerPhone = phoneElement ? phoneElement.parentElement.textContent.trim() : '-';
        
        document.getElementById('customerSummary').innerHTML = `
            <p class="mb-1"><strong>Name:</strong> ${customerName}</p>
            <p class="mb-1"><strong>Email:</strong> ${customerEmail}</p>
            <p class="mb-0"><strong>Phone:</strong> ${customerPhone}</p>
        `;
    }
    
    // Date Summary
    document.getElementById('dateSummary').innerHTML = `
        <p class="mb-1"><strong>Start:</strong> ${formatDate(startDate)}</p>
        <p class="mb-1"><strong>End:</strong> ${formatDate(endDate)}</p>
        <p class="mb-0"><strong>Duration:</strong> ${days} day${days > 1 ? 's' : ''}</p>
    `;
    
    // PERBAIKAN: Hapus hidden inputs quantity yang lama terlebih dahulu
    const oldQuantityInputs = document.querySelectorAll('input[name^="item_quantities"]');
    oldQuantityInputs.forEach(input => input.remove());
    
    // Items Summary
    let itemsHtml = '';
    let totalPrice = 0;
    
    console.log('Building summary for', selectedItems.size, 'items');
    
    selectedItems.forEach((quantity, itemId) => {
        const checkbox = document.querySelector(`.item-checkbox[value="${itemId}"]`);
        if (!checkbox) {
            console.error('Checkbox not found for item:', itemId);
            return;
        }
        
        const name = checkbox.dataset.name;
        const pricePerDay = parseInt(checkbox.dataset.price);
        const subtotal = pricePerDay * quantity * days;
        totalPrice += subtotal;
        
        console.log('Item:', name, '| Qty:', quantity, '| Subtotal:', subtotal);
        
        // PERBAIKAN: Tambahkan hidden input langsung ke form, bukan ke summary HTML
        const form = document.getElementById('manualBookingForm');
        const quantityInput = document.createElement('input');
        quantityInput.type = 'hidden';
        quantityInput.name = `item_quantities[${itemId}]`;
        quantityInput.value = quantity;
        form.appendChild(quantityInput);
        
        itemsHtml += `
            <div class="summary-item">
                <div>
                    <strong>${name}</strong><br>
                    <small class="text-muted">${quantity} × ${formatRupiah(pricePerDay)} × ${days} days</small>
                </div>
                <div class="text-end">
                    <strong>${formatRupiah(subtotal)}</strong>
                </div>
            </div>
        `;
    });
    
    document.getElementById('itemsSummary').innerHTML = itemsHtml;
    document.getElementById('totalPrice').textContent = formatRupiah(totalPrice);
    console.log('Summary updated. Total:', formatRupiah(totalPrice));
    console.log('Hidden quantity inputs created for', selectedItems.size, 'items');
}

// PERBAIKAN: Form Submit - tambahkan logging lebih detail
document.getElementById('manualBookingForm').addEventListener('submit', function(e) {
    console.log('═══════════════════════════════════════');
    console.log('FORM SUBMIT');
    console.log('Selected items:', selectedItems.size);
    console.log('Current step:', currentStep);
    
    // Validasi hanya jika di step 4
    if (currentStep !== 4) {
        e.preventDefault();
        console.log('❌ Form submission blocked: Not on confirmation step');
        alert('Please complete all steps before submitting');
        return false;
    }
    
    if (selectedItems.size === 0) {
        e.preventDefault();
        alert('Please select at least one item');
        console.log('❌ Form submission blocked: No items selected');
        return false;
    }
    
    // Pastikan quantity inputs ada
    const quantityInputs = document.querySelectorAll('input[name^="item_quantities"]');
    if (quantityInputs.length === 0) {
        e.preventDefault();
        console.log('❌ Form submission blocked: No quantity inputs found');
        alert('Error: Item quantities not set. Please go back and try again.');
        return false;
    }
    
    // Log form data
    const formData = new FormData(this);
    console.log('Form Data:');
    for (let [key, value] of formData.entries()) {
        console.log('  ', key, ':', value);
    }
    
    console.log('✓ Form validation passed');
    console.log('✓ Submitting to:', this.action);
    console.log('═══════════════════════════════════════');
    
    // Biarkan form submit secara normal
    return true;
});

function calculateDays(startDate, endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    const diffTime = Math.abs(end - start);
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}

function formatRupiah(amount) {
    return 'Rp ' + parseInt(amount).toLocaleString('id-ID');
}

// New Customer Form
document.getElementById('newCustomerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= APP_URL ?>/admin/create_customer_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Customer created successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error creating customer');
        console.error(error);
    });
});

// Form Submit
document.getElementById('manualBookingForm').addEventListener('submit', function(e) {
    console.log('═══════════════════════════════════════');
    console.log('FORM SUBMIT');
    console.log('Selected items:', selectedItems.size);
    
    if (selectedItems.size === 0) {
        e.preventDefault();
        alert('Please select at least one item');
        console.log('❌ Form submission blocked: No items selected');
        return false;
    }
    
    // Log form data
    const formData = new FormData(this);
    console.log('Form Data:');
    for (let [key, value] of formData.entries()) {
        console.log('  ', key, ':', value);
    }
    console.log('✓ Form submitting...');
});
</script>

<?php include __DIR__ . '/../views/footer.php'; ?>