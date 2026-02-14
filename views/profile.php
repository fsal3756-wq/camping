<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/functions.php';
require_once __DIR__ . '/../models/User.php';

Auth::requireLogin();

$user = Auth::user();
$userModel = new User();
$userDetails = $userModel->getById($user['id']);
$userStats = $userModel->getUserStats($user['id']);

$pageTitle = 'Profile - ' . APP_NAME;
$activePage = 'profile';

// ============================================
// PROSES FORM SUBMIT
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // ============================================
    // HANDLE KTP UPLOAD
    // ============================================
    $ktpPath = null;
    
    if (isset($_FILES['ktp_image']) && $_FILES['ktp_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['ktp_image'];
        
        // Buat folder upload jika belum ada
        $uploadDir = __DIR__ . '/../assets/uploads/ktp';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Hapus KTP lama jika ada
        if (!empty($userDetails['ktp_image'])) {
            $oldFile = __DIR__ . '/../' . $userDetails['ktp_image'];
            if (file_exists($oldFile)) {
                @unlink($oldFile);
            }
        }
        
        // Generate nama file baru
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'ktp_user_' . $user['id'] . '_' . time() . '.' . $ext;
        $targetPath = $uploadDir . '/' . $filename;
        
        // Upload file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $ktpPath = 'assets/uploads/ktp/' . $filename;
        } else {
            setFlashMessage('danger', 'Failed to upload KTP image');
            header('Location: ' . APP_URL . '/views/profile.php');
            exit;
        }
    }

    // ============================================
    // VALIDASI PASSWORD (jika diisi)
    // ============================================
    $passwordHash = null;
    if (!empty($currentPassword) && !empty($newPassword)) {
        if ($newPassword !== $confirmPassword) {
            setFlashMessage('danger', 'New passwords do not match');
            header('Location: ' . APP_URL . '/views/profile.php');
            exit;
        } elseif (strlen($newPassword) < 6) {
            setFlashMessage('danger', 'Password must be at least 6 characters');
            header('Location: ' . APP_URL . '/views/profile.php');
            exit;
        } elseif (!password_verify($currentPassword, $userDetails['password'])) {
            setFlashMessage('danger', 'Current password is incorrect');
            header('Location: ' . APP_URL . '/views/profile.php');
            exit;
        } else {
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        }
    }

    // ============================================
    // UPDATE DATABASE
    // ============================================
    try {
        $db = Database::getInstance()->getConnection();
        
        // Build query dinamis
        if ($ktpPath && $passwordHash) {
            // Update semua termasuk KTP dan password
            $sql = "UPDATE users SET full_name = ?, phone = ?, address = ?, ktp_image = ?, password = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$fullName, $phone, $address, $ktpPath, $passwordHash, $user['id']]);
        } elseif ($ktpPath) {
            // Update termasuk KTP
            $sql = "UPDATE users SET full_name = ?, phone = ?, address = ?, ktp_image = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$fullName, $phone, $address, $ktpPath, $user['id']]);
        } elseif ($passwordHash) {
            // Update termasuk password
            $sql = "UPDATE users SET full_name = ?, phone = ?, address = ?, password = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$fullName, $phone, $address, $passwordHash, $user['id']]);
        } else {
            // Update data biasa saja
            $sql = "UPDATE users SET full_name = ?, phone = ?, address = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$fullName, $phone, $address, $user['id']]);
        }
        
        $_SESSION['full_name'] = $fullName;
        
        // Success message
        $messages = [];
        if ($ktpPath) {
            $messages[] = 'KTP uploaded successfully';
        }
        if ($passwordHash) {
            $messages[] = 'Password changed successfully';
        }
        if (empty($messages)) {
            $messages[] = 'Profile updated successfully';
        }
        
        setFlashMessage('success', implode('. ', $messages));
        
    } catch (Exception $e) {
        error_log("Profile update error: " . $e->getMessage());
        setFlashMessage('danger', 'Error: ' . $e->getMessage());
    }

    header('Location: ' . APP_URL . '/views/profile.php');
    exit;
}

include __DIR__ . '/header.php';
include __DIR__ . '/topnav.php';
?>

<div class="container my-5">
    <div class="row">
        <!-- SIDEBAR KIRI -->
        <div class="col-md-4">
            <!-- PROFILE CARD -->
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-person-circle text-primary" style="font-size: 5rem;"></i>
                    </div>
                    <h4><?= htmlspecialchars($userDetails['full_name']) ?></h4>
                    <p class="text-muted mb-1">@<?= htmlspecialchars($userDetails['username']) ?></p>
                    <span class="badge bg-<?= $userDetails['role'] === 'admin' ? 'danger' : 'primary' ?>">
                        <?= ucfirst($userDetails['role']) ?>
                    </span>

                    <hr>

                    <div class="text-start">
                        <p class="mb-2">
                            <i class="bi bi-envelope me-2"></i>
                            <?= htmlspecialchars($userDetails['email']) ?>
                        </p>
                        <p class="mb-2">
                            <i class="bi bi-telephone me-2"></i>
                            <?= htmlspecialchars($userDetails['phone'] ?? 'Not set') ?>
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-calendar me-2"></i>
                            Member since <?= formatDate($userDetails['created_at'], 'M Y') ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- STATISTICS CARD -->
            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <h5 class="mb-3">Statistics</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Bookings:</span>
                        <strong><?= $userStats['total_bookings'] ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Completed:</span>
                        <strong class="text-success"><?= $userStats['completed_bookings'] ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Total Spent:</span>
                        <strong class="text-primary"><?= formatRupiah($userStats['total_spent']) ?></strong>
                    </div>
                </div>
            </div>

            <!-- KTP CARD -->
            <?php if (!empty($userDetails['ktp_image'])): ?>
            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <h5 class="mb-3">
                        <i class="bi bi-credit-card-2-front me-2"></i>KTP
                    </h5>
                    <div class="text-center">
                        <img src="<?= APP_URL ?>/<?= htmlspecialchars($userDetails['ktp_image']) ?>" 
                             alt="KTP" 
                             class="img-fluid rounded border"
                             style="max-width: 100%; cursor: pointer;"
                             onclick="window.open('<?= APP_URL ?>/<?= htmlspecialchars($userDetails['ktp_image']) ?>', '_blank')">
                        <small class="text-muted d-block mt-2">Click to view full size</small>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- FORM EDIT PROFILE -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Profile</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <!-- PERSONAL INFORMATION -->
                        <h6 class="mb-3">Personal Information</h6>

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control"
                                value="<?= htmlspecialchars($userDetails['full_name']) ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control"
                                    value="<?= htmlspecialchars($userDetails['username']) ?>" disabled>
                                <small class="text-muted">Cannot be changed</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control"
                                    value="<?= htmlspecialchars($userDetails['email']) ?>" disabled>
                                <small class="text-muted">Cannot be changed</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control"
                                value="<?= htmlspecialchars($userDetails['phone'] ?? '') ?>"
                                placeholder="081234567890">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control"
                                rows="3" placeholder="Enter your address"><?= htmlspecialchars($userDetails['address'] ?? '') ?></textarea>
                        </div>

                        <!-- KTP UPLOAD SECTION -->
                        <hr class="my-4">
                        
                        <h6 class="mb-3">
                            <i class="bi bi-credit-card-2-front me-2"></i>Upload KTP
                        </h6>

                        <div class="alert alert-info">
                            <small>
                                <i class="bi bi-info-circle me-1"></i>
                                Upload your KTP (ID Card) image. Accepted formats: JPG, PNG
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                KTP Image
                                <?php if (!empty($userDetails['ktp_image'])): ?>
                                    <span class="badge bg-success">Uploaded</span>
                                <?php endif; ?>
                            </label>
                            <input type="file" 
                                   name="ktp_image" 
                                   class="form-control" 
                                   accept="image/*"
                                   id="ktpInput">
                            <small class="text-muted">
                                <?php if (!empty($userDetails['ktp_image'])): ?>
                                    Upload new file to replace existing KTP
                                <?php else: ?>
                                    Select your KTP image file
                                <?php endif; ?>
                            </small>
                        </div>

                        <!-- Image Preview -->
                        <div class="mb-3" id="imagePreview" style="display: none;">
                            <label class="form-label">Preview:</label>
                            <div class="border rounded p-2 text-center bg-light">
                                <img id="previewImg" src="" alt="Preview" style="max-width: 100%; max-height: 300px;">
                            </div>
                        </div>

                        <!-- CHANGE PASSWORD SECTION -->
                        <hr class="my-4">

                        <h6 class="mb-3">Change Password</h6>
                        <p class="text-muted small">Leave blank if you don't want to change password</p>

                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control"
                                   placeholder="Enter current password">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" 
                                       minlength="6" placeholder="Enter new password">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" 
                                       minlength="6" placeholder="Confirm new password">
                            </div>
                        </div>

                        <!-- SUBMIT BUTTON -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Image Preview -->
<script>
document.getElementById('ktpInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    
    if (file) {
        // Validasi tipe file (optional - untuk UX)
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            alert('Please select a valid image file (JPG, PNG, GIF)');
            e.target.value = '';
            document.getElementById('imagePreview').style.display = 'none';
            return;
        }
        
        // Validasi ukuran file (optional - max 5MB untuk UX)
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            alert('File size must not exceed 5MB');
            e.target.value = '';
            document.getElementById('imagePreview').style.display = 'none';
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('previewImg').src = event.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('imagePreview').style.display = 'none';
    }
});
</script>

<?php include __DIR__ . '/footer.php'; ?>