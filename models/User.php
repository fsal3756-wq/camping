<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserStats($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                COALESCE(SUM(CASE WHEN status = 'completed' THEN total_price ELSE 0 END), 0) as total_spent
            FROM bookings 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        try {
            // SELECT semua kolom dengan SELECT *
            $stmt = $this->db->query("SELECT * FROM users ORDER BY created_at DESC");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users;
        } catch (PDOException $e) {
            error_log("Error in User::getAll(): " . $e->getMessage());
            return [];
        }
    }

    public function update($id, $data) {
        try {
            $fields = [];
            $values = [];
            
            $allowedFields = [
                'full_name', 
                'phone', 
                'address', 
                'password', 
                'ktp_image',
                'role',
                'status'
            ];
            
            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $fields[] = "$key = ?";
                    $values[] = $value;
                }
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $fields[] = "updated_at = NOW()";
            $values[] = $id;
            
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($values);
            
        } catch (PDOException $e) {
            error_log("Error in User::update(): " . $e->getMessage());
            return false;
        }
    }

    public function create($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password, full_name, phone, address, role) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            return $stmt->execute([
                $data['username'],
                $data['email'],
                $data['password'],
                $data['full_name'],
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $data['role'] ?? 'user'
            ]);
        } catch (PDOException $e) {
            error_log("Error in User::create(): " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error in User::delete(): " . $e->getMessage());
            return false;
        }
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}