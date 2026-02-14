<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../models/User.php';

Auth::requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $userModel = new User();
    
    // Validate required fields
    $requiredFields = ['full_name', 'email', 'phone', 'username', 'password'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field $field is required");
        }
    }
    
    // Check if username or email already exists
    $existingUser = $userModel->findByUsername($_POST['username']);
    if ($existingUser) {
        throw new Exception('Username already exists');
    }
    
    $existingEmail = $userModel->findByEmail($_POST['email']);
    if ($existingEmail) {
        throw new Exception('Email already exists');
    }
    
    // Create user
    $userData = [
        'username' => $_POST['username'],
        'email' => $_POST['email'],
        'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        'full_name' => $_POST['full_name'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'] ?? '',
        'role' => 'user'
    ];
    
    $userId = $userModel->create($userData);
    
    if ($userId) {
        echo json_encode([
            'success' => true,
            'message' => 'Customer created successfully',
            'user_id' => $userId
        ]);
    } else {
        throw new Exception('Failed to create customer');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
