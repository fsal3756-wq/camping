<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/functions.php';
require_once __DIR__ . '/../models/Item.php';
require_once __DIR__ . '/../models/Payment.php';
class Booking
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll($filters = [])
    {
        $sql = "
            SELECT b.*, 
                u.username, u.email, u.full_name,
                i.name as item_name, i.category,
                p.status as payment_status
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN items i ON b.item_id = i.id
            LEFT JOIN payments p ON b.id = p.booking_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND b.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['item_id'])) {
            $sql .= " AND b.item_id = ?";
            $params[] = $filters['item_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND b.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND b.start_date >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND b.end_date <= ?";
            $params[] = $filters['end_date'];
        }

        $sql .= " ORDER BY b.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function getById($id)
    {
        return $this->db->fetchOne("
            SELECT b.*, 
                u.username, u.email, u.full_name, u.phone, u.address,
                i.name as item_name, i.description, i.category, i.price_per_day, i.image_url,
                p.status as payment_status, p.payment_method, p.transaction_id
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN items i ON b.item_id = i.id
            LEFT JOIN payments p ON b.id = p.booking_id
            WHERE b.id = ?
        ", [$id]);
    }
    public function create($data)
    {
        $itemModel = new Item(); // pastikan sudah require Item.php di atas

        try {
            $this->db->beginTransaction();

            // Ambil data item
            $item = $this->db->fetchOne("SELECT * FROM items WHERE id = ?", [$data['item_id']]);
            if (!$item) {
                throw new Exception("Item not found");
            }

            // Hitung durasi & total harga
            $days = calculateDays($data['start_date'], $data['end_date']);
            $totalPrice = $item['price_per_day'] * $days * $data['quantity'];

            // 🔒 KURANGI STOK LANGSUNG SAAT BOOKING
            $newAvailable = $item['quantity_available'] - $data['quantity'];
            if ($newAvailable < 0) {
                throw new Exception("Hanya {$item['quantity_available']} unit {$item['name']} tersedia.");
            }

            // Update stok item
            $itemUpdated = $itemModel->update($item['id'], [
                'quantity_available' => $newAvailable
            ]);

            if (!$itemUpdated) {
                throw new Exception("Gagal memperbarui stok.");
            }

            // Buat booking (status = 'pending')
            $bookingData = [
                'user_id' => $data['user_id'],
                'item_id' => $data['item_id'],
                'booking_date' => date('Y-m-d'),
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'quantity' => $data['quantity'],
                'total_price' => $totalPrice,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null
            ];

            $bookingId = $this->db->insert('bookings', $bookingData);

            if (!$bookingId) {
                throw new Exception("Gagal membuat booking.");
            }

            $this->db->commit();
            return $bookingId;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Booking error: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data)
    {
        return $this->db->update('bookings', $data, 'id = :id', ['id' => $id]); // ✅
    }

    public function updateStatus($id, $status)
    {
        // Tambahkan dependency Item
        $itemModel = new Item();

        try {
            $this->db->beginTransaction();

            $booking = $this->getById($id);
            if (!$booking) {
                throw new Exception("Booking not found");
            }

            $oldStatus = $booking['status'];
            $itemId = $booking['item_id'];
            $quantity = (int)$booking['quantity'];

            // ✅ Update status booking
            $updated = $this->db->update(
                'bookings',
                ['status' => $status],
                'id = :id',
                ['id' => $id]
            );

            if (!$updated) {
                throw new Exception("Failed to update booking status");
            }

            // ✅ HANYA proses stok jika status BERUBAH
            if ($oldStatus !== $status) {
                $item = $itemModel->getById($itemId);
                if (!$item) {
                    throw new Exception("Item not found for booking");
                }

                $newAvailable = (int)$item['quantity_available'];

                // 📉 Kurangi stok: hanya jika berubah dari 'pending' ke 'confirmed' atau 'completed'
                if (in_array($status, ['confirmed', 'completed']) && $oldStatus === 'pending') {
                    $newAvailable -= $quantity;
                    if ($newAvailable < 0) {
                        throw new Exception("Insufficient stock: only {$item['quantity_available']} available");
                    }

                    // 📈 Kembalikan stok: jika dibatalkan dari status aktif
                } elseif ($status === 'cancelled' && in_array($oldStatus, ['pending', 'confirmed', 'completed'])) {
                    $newAvailable += $quantity;
                    if ($newAvailable > $item['quantity_total']) {
                        $newAvailable = $item['quantity_total'];
                    }
                }

                // ✅ Simpan perubahan stok jika berubah
                if ($newAvailable !== (int)$item['quantity_available']) {
                    $itemUpdated = $itemModel->update($itemId, [
                        'quantity_available' => $newAvailable
                    ]);

                    if (!$itemUpdated) {
                        throw new Exception("Failed to update item stock");
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Booking status update error (ID: $id): " . $e->getMessage());
            return false;
        }
    }

    // ✨ TAMBAHAN METHOD BARU: Update Payment Status
    public function updatePaymentStatus($id, $paymentStatus)
    {
        try {
            $updated = $this->db->update(
                'bookings',
                ['payment_status' => $paymentStatus],
                'id = :id',
                ['id' => $id]
            );

            return $updated;
        } catch (Exception $e) {
            error_log("Payment status update error (ID: $id): " . $e->getMessage());
            return false;
        }
    }

    public function cancel($id, $userId = null)
    {
        $itemModel = new Item();

        try {
            $booking = $this->getById($id);
            if (!$booking) return false;

            if ($userId && $booking['user_id'] != $userId) {
                return false;
            }

            if (!in_array($booking['status'], ['pending', 'confirmed'])) {
                return false;
            }

            // 🔁 KEMBALIKAN STOK SAAT DIBATALKAN
            $item = $itemModel->getById($booking['item_id']);
            if ($item) {
                $newAvailable = min(
                    $item['quantity_available'] + $booking['quantity'],
                    $item['quantity_total']
                );
                $itemModel->update($booking['item_id'], [
                    'quantity_available' => $newAvailable
                ]);
            }

            return $this->updateStatus($id, 'cancelled');
        } catch (Exception $e) {
            error_log("Cancel error: " . $e->getMessage());
            return false;
        }
    }

    public function getUserBookings($userId, $status = null)
    {
        $sql = "
            SELECT b.*, 
                i.name as item_name, i.category, i.image_url,
                p.status as payment_status,
                (SELECT COUNT(*) FROM booking_items bi WHERE bi.booking_id = b.id) as items_count
            FROM bookings b
            JOIN items i ON b.item_id = i.id
            LEFT JOIN payments p ON b.id = p.booking_id
            WHERE b.user_id = ?
        ";
        $params = [$userId];

        if ($status) {
            $sql .= " AND b.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY b.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function getUpcomingBookings($itemId)
    {
        return $this->db->fetchAll("
            SELECT b.*, u.username, u.full_name
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            WHERE b.item_id = ?
            AND b.status IN ('pending', 'confirmed')
            AND b.start_date >= CURDATE()
            ORDER BY b.start_date ASC
        ", [$itemId]);
    }

    public function getBookingStats($userId = null)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_bookings,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled,
                COALESCE(SUM(total_price), 0) as total_revenue
            FROM bookings
        ";
        $params = [];

        if ($userId) {
            $sql .= " WHERE user_id = ?";
            $params[] = $userId;
        }

        return $this->db->fetchOne($sql, $params);
    }
}