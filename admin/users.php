<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/functions.php';

require_once __DIR__ . '/../models/User.php';

Auth::requireAdmin();

$pageTitle = 'Users - Admin';
$activePage = 'users';

$userModel = new User();
$users = $userModel->getAll();

include __DIR__ . '/../views/header.php';
?>

<div class="d-flex">
    <?php include __DIR__ . '/../views/sidebar.php'; ?>

    <div class="flex-grow-1 p-4">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-people me-2"></i>Manage Users</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-plus-circle me-2"></i>Add New User
                </button>
            </div>

            <!-- Users Table -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>KTP</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= htmlspecialchars($user['phone'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'primary' ?>">
                                                <?= ucfirst($user['role']) ?>
                                            </span>
                                        </td>
                                        <td>
    <?php if (!empty($user['ktp_image'])): ?>
        <button class="btn btn-sm btn-info" 
                onclick="viewKTP('<?= htmlspecialchars($user['username']) ?>', '<?= APP_URL ?>/<?= htmlspecialchars($user['ktp_image'] ?? '') ?>')">
            <i class="bi bi-eye"></i> View KTP
        </button>
    <?php else: ?>
        <span class="text-muted">No KTP</span>
    <?php endif; ?>
</td>
                                        <td>
                                            <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                <?= ucfirst($user['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= formatDate($user['created_at'], 'd M Y') ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-warning"
                                                    onclick="editUser(<?= htmlspecialchars(json_encode($user)) ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <?php if ($user['id'] != Auth::user()['id']): ?>
                                                    <button class="btn btn-danger"
                                                        onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= APP_URL ?>/admin/process_users.php" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= APP_URL ?>/admin/process_users.php" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" id="edit_username" class="form-control" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" id="edit_email" class="form-control" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" id="edit_phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" id="edit_role" class="form-select" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="edit_status" class="form-select" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" minlength="6">
                        <small class="text-muted">Leave empty to keep current password</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- KTP View Modal -->
<div class="modal fade" id="ktpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ktpModalTitle">View KTP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="ktpImage" src="" alt="KTP" class="img-fluid rounded border">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a id="ktpDownload" href="" download class="btn btn-primary">
                    <i class="bi bi-download me-2"></i>Download
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function editUser(user) {
        document.getElementById('edit_user_id').value = user.id;
        document.getElementById('edit_username').value = user.username;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_full_name').value = user.full_name;
        document.getElementById('edit_phone').value = user.phone || '';
        document.getElementById('edit_role').value = user.role;
        document.getElementById('edit_status').value = user.status;

        new bootstrap.Modal(document.getElementById('editUserModal')).show();
    }

    function deleteUser(id, username) {
        if (confirm(`Are you sure you want to delete user "${username}"?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= APP_URL ?>/admin/process_users.php';

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

    function viewKTP(username, imageUrl) {
        document.getElementById('ktpModalTitle').textContent = 'KTP - ' + username;
        document.getElementById('ktpImage').src = imageUrl;
        document.getElementById('ktpDownload').href = imageUrl;
        
        new bootstrap.Modal(document.getElementById('ktpModal')).show();
    }
</script>

<?php include __DIR__ . '/../views/footer.php'; ?>