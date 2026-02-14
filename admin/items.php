<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/functions.php';
require_once __DIR__ . '/../models/Item.php';

Auth::requireAdmin();

$pageTitle = 'Items - Admin';
$activePage = 'items';

$itemModel = new Item();
$items = $itemModel->getAll();

include __DIR__ . '/../views/header.php';
?>

<style>
    .upload-area {
        transition: all 0.3s ease;
    }
    .upload-area:hover {
        border-color: #667eea !important;
        background-color: #f8f9ff !important;
    }
    .modal-content {
        border-radius: 15px;
        overflow: hidden;
    }
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }
    .input-group-text {
        background-color: #f8f9fa;
        border-right: 0;
    }
    .section-divider {
        height: 2px;
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, rgba(102, 126, 234, 0.5) 50%, rgba(102, 126, 234, 0.1) 100%);
        margin: 1.5rem 0;
    }
    .preview-image {
        border-radius: 10px;
        border: 3px solid #e9ecef;
        transition: all 0.3s ease;
    }
    .preview-image:hover {
        border-color: #667eea;
        transform: scale(1.02);
    }
</style>

<div class="d-flex">
    <?php include __DIR__ . '/../views/sidebar.php'; ?>

    <div class="flex-grow-1 p-4">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-box-seam me-2"></i>Manage Items</h2>
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addItemModal"
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                    <i class="bi bi-plus-circle me-2"></i>Add New Item
                </button>
            </div>

            <!-- Items Table -->
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="itemsTable">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price/Day</th>
                                    <th>Available</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <?php if ($item['image_url']): ?>
                                                <img src="<?= APP_URL . '/' . htmlspecialchars($item['image_url']) ?>"
                                                    alt="<?= htmlspecialchars($item['name']) ?>"
                                                    style="width: 50px; height: 50px; object-fit: cover;" class="rounded">
                                            <?php else: ?>
                                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center"
                                                    style="width: 50px; height: 50px;">
                                                    <i class="bi bi-image text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($item['name']) ?></td>
                                        <td><span class="badge bg-info"><?= htmlspecialchars($item['category']) ?></span>
                                        </td>
                                        <td><?= formatRupiah($item['price_per_day']) ?></td>
                                        <td><?= $item['quantity_available'] ?></td>
                                        <td><?= $item['quantity_total'] ?></td>
                                        <td>
                                            <span
                                                class="badge bg-<?= $item['status'] === 'available' ? 'success' : 'danger' ?>">
                                                <?= ucfirst($item['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning"
                                                onclick='editItem(<?= json_encode($item) ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <button class="btn btn-sm btn-danger"
                                                onclick="deleteItem(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name']) ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div>
                    <h4 class="modal-title text-white mb-1 fw-bold">
                        <i class="bi bi-plus-circle-fill me-2"></i>Add New Item
                    </h4>
                    <p class="text-white-50 mb-0 small">Fill in the details to add a new rental item</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= APP_URL ?>/admin/process_items.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                <div class="modal-body p-4">
                    <!-- Basic Information Section -->
                    <div class="mb-4">
                        <h6 class="text-muted text-uppercase fw-semibold mb-3" style="font-size: 0.75rem; letter-spacing: 1px;">
                            <i class="bi bi-info-circle me-1"></i> Basic Information
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-tag text-muted"></i>
                                    </span>
                                    <input type="text" name="name" class="form-control border-start-0 ps-0" 
                                           placeholder="e.g., Canon EOS 5D Mark IV" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-folder text-muted"></i>
                                    </span>
                                    <input type="text" name="category" class="form-control border-start-0 ps-0" 
                                           placeholder="e.g., Camera, Lens, Lighting" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="3" 
                                          placeholder="Add detailed description about the item, its features, and condition..."
                                          style="resize: none;"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <!-- Pricing & Inventory Section -->
                    <div class="mb-4">
                        <h6 class="text-muted text-uppercase fw-semibold mb-3" style="font-size: 0.75rem; letter-spacing: 1px;">
                            <i class="bi bi-calculator me-1"></i> Pricing & Inventory
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Price per Day <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" name="price_per_day" class="form-control" 
                                           placeholder="0" required min="0" step="1000">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Total Quantity <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-boxes text-muted"></i>
                                    </span>
                                    <input type="number" name="quantity_total" class="form-control border-start-0 ps-0" 
                                           required min="1" value="1">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="available" selected>✓ Available</option>
                                    <option value="unavailable">✗ Unavailable</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <!-- Image Upload Section -->
                    <div class="mb-3">
                        <h6 class="text-muted text-uppercase fw-semibold mb-3" style="font-size: 0.75rem; letter-spacing: 1px;">
                            <i class="bi bi-image me-1"></i> Item Image
                        </h6>
                        <div class="upload-area border-2 border-dashed rounded-3 p-4 text-center bg-light" style="border-color: #dee2e6;">
                            <input type="file" name="image" class="form-control d-none" accept="image/*" id="addImageInput">
                            <label for="addImageInput" class="cursor-pointer w-100 m-0" style="cursor: pointer;">
                                <div id="addImagePreview">
                                    <i class="bi bi-cloud-upload text-primary mb-3" style="font-size: 3rem;"></i>
                                    <h6 class="mb-2 fw-semibold">Click to upload or drag and drop</h6>
                                    <p class="text-muted small mb-0">PNG, JPG, JPEG up to 5MB</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light px-4 py-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm" 
                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                        <i class="bi bi-check-lg me-2"></i>Create Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-4" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div>
                    <h4 class="modal-title text-white mb-1 fw-bold">
                        <i class="bi bi-pencil-square me-2"></i>Edit Item
                    </h4>
                    <p class="text-white-50 mb-0 small">Update the item details below</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= APP_URL ?>/admin/process_items.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body p-4">
                    <!-- Basic Information Section -->
                    <div class="mb-4">
                        <h6 class="text-muted text-uppercase fw-semibold mb-3" style="font-size: 0.75rem; letter-spacing: 1px;">
                            <i class="bi bi-info-circle me-1"></i> Basic Information
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-tag text-muted"></i>
                                    </span>
                                    <input type="text" name="name" id="edit_name" class="form-control border-start-0 ps-0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-folder text-muted"></i>
                                    </span>
                                    <input type="text" name="category" id="edit_category" class="form-control border-start-0 ps-0" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" id="edit_description" class="form-control" rows="3" 
                                          style="resize: none;"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <!-- Pricing & Inventory Section -->
                    <div class="mb-4">
                        <h6 class="text-muted text-uppercase fw-semibold mb-3" style="font-size: 0.75rem; letter-spacing: 1px;">
                            <i class="bi bi-calculator me-1"></i> Pricing & Inventory
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Price per Day <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" name="price_per_day" id="edit_price" class="form-control" required min="0" step="1000">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Available Quantity <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-check-circle text-success"></i>
                                    </span>
                                    <input type="number" name="quantity_available" id="edit_qty_available" class="form-control border-start-0 ps-0" required min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Total Quantity <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-boxes text-muted"></i>
                                    </span>
                                    <input type="number" name="quantity_total" id="edit_qty_total" class="form-control border-start-0 ps-0" required min="1">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Section -->
                    <div class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" id="edit_status" class="form-select" required>
                                    <option value="available">✓ Available</option>
                                    <option value="unavailable">✗ Unavailable</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <!-- Image Section -->
                    <div class="mb-3">
                        <h6 class="text-muted text-uppercase fw-semibold mb-3" style="font-size: 0.75rem; letter-spacing: 1px;">
                            <i class="bi bi-image me-1"></i> Item Image
                        </h6>
                        
                        <!-- Current Image Preview -->
                        <div id="current_image_preview" class="mb-3"></div>

                        <!-- Upload New Image -->
                        <div class="upload-area border-2 border-dashed rounded-3 p-4 text-center bg-light" style="border-color: #dee2e6;">
                            <input type="file" name="image" class="form-control d-none" accept="image/*" id="edit_image_input">
                            <label for="edit_image_input" class="cursor-pointer w-100 m-0" style="cursor: pointer;">
                                <div id="editImagePreview">
                                    <i class="bi bi-arrow-repeat text-warning mb-2" style="font-size: 2.5rem;"></i>
                                    <h6 class="mb-2 fw-semibold">Click to change image</h6>
                                    <p class="text-muted small mb-0">Leave empty to keep current image</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light px-4 py-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm" 
                            style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border: none;">
                        <i class="bi bi-save me-2"></i>Update Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Add Item Image Preview
    document.getElementById('addImageInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('addImagePreview').innerHTML = `
                    <img src="${e.target.result}" 
                         alt="Preview" 
                         class="preview-image shadow-sm" 
                         style="max-width: 100%; max-height: 250px; object-fit: contain;">
                    <p class="text-success small mt-3 mb-0">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        Image ready to upload: <strong>${file.name}</strong>
                    </p>
                `;
            };
            reader.readAsDataURL(file);
        }
    });

    function editItem(item) {
        document.getElementById('edit_id').value = item.id;
        document.getElementById('edit_name').value = item.name;
        document.getElementById('edit_category').value = item.category;
        document.getElementById('edit_description').value = item.description || '';
        document.getElementById('edit_price').value = item.price_per_day ?? 0;
        document.getElementById('edit_qty_available').value = item.quantity_available ?? 1;
        document.getElementById('edit_qty_total').value = item.quantity_total ?? 1;
        document.getElementById('edit_status').value = item.status;

        // Display current image
        const imagePreview = document.getElementById('current_image_preview');
        if (item.image_url) {
            imagePreview.innerHTML = `
                <div class="text-center p-3 bg-light rounded-3 border">
                    <p class="text-muted small mb-2 fw-semibold">CURRENT IMAGE</p>
                    <img src="<?= APP_URL ?>/${item.image_url}" 
                         alt="${item.name}" 
                         class="preview-image shadow-sm" 
                         style="max-width: 100%; max-height: 250px; object-fit: contain;">
                </div>
            `;
        } else {
            imagePreview.innerHTML = `
                <div class="alert alert-light border text-center">
                    <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                    <p class="mb-0 mt-2 text-muted small">No image currently uploaded</p>
                </div>
            `;
        }

        // Reset file input and preview
        document.getElementById('edit_image_input').value = '';
        document.getElementById('editImagePreview').innerHTML = `
            <i class="bi bi-arrow-repeat text-warning mb-2" style="font-size: 2.5rem;"></i>
            <h6 class="mb-2 fw-semibold">Click to change image</h6>
            <p class="text-muted small mb-0">Leave empty to keep current image</p>
        `;

        new bootstrap.Modal(document.getElementById('editItemModal')).show();
    }

    // Edit Item Image Preview
    document.getElementById('edit_image_input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('editImagePreview').innerHTML = `
                    <img src="${e.target.result}" 
                         alt="New preview" 
                         class="preview-image shadow-sm" 
                         style="max-width: 100%; max-height: 200px; object-fit: contain;">
                    <p class="text-success small mt-3 mb-0">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        New image ready: <strong>${file.name}</strong>
                    </p>
                `;
            };
            reader.readAsDataURL(file);
        }
    });

    function deleteItem(id, name) {
        if (confirm(`Are you sure you want to delete "${name}"?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= APP_URL ?>/admin/process_items.php';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete';

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;

            form.appendChild(actionInput);
            form.appendChild(idInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<?php include __DIR__ . '/../views/footer.php'; ?>