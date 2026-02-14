<?php
require_once __DIR__ . '/../config/database.php';

class Cart
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Add item to cart
     * 
     * @param array $data
     * @return int|bool Cart ID or false
     */
    public function add($data)
    {
        try {
            // Check if item already exists in cart with same dates
            $existing = $this->checkExisting(
                $data['user_id'],
                $data['item_id'],
                $data['start_date'],
                $data['end_date']
            );

            if ($existing) {
                // Update quantity instead of creating new
                return $this->updateQuantity(
                    $existing['id'],
                    $existing['quantity'] + $data['quantity']
                );
            }

            $query = "INSERT INTO cart (user_id, item_id, quantity, start_date, end_date, notes)
                      VALUES (:user_id, :item_id, :quantity, :start_date, :end_date, :notes)";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':user_id' => $data['user_id'],
                ':item_id' => $data['item_id'],
                ':quantity' => $data['quantity'],
                ':start_date' => $data['start_date'],
                ':end_date' => $data['end_date'],
                ':notes' => $data['notes'] ?? null
            ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Cart add error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if item already exists in cart
     */
    private function checkExisting($userId, $itemId, $startDate, $endDate)
    {
        $query = "SELECT * FROM cart 
                  WHERE user_id = ? AND item_id = ? 
                  AND start_date = ? AND end_date = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, $itemId, $startDate, $endDate]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get user's cart items with item details
     * 
     * @param int $userId
     * @return array
     */
    public function getUserCart($userId)
    {
        $query = "SELECT 
                    c.*,
                    i.name as item_name,
                    i.description,
                    i.price_per_day,
                    i.category,
                    i.image_url,
                    i.quantity_total as stock,
                    DATEDIFF(c.end_date, c.start_date) as duration,
                    (i.price_per_day * c.quantity * DATEDIFF(c.end_date, c.start_date)) as subtotal
                  FROM cart c
                  JOIN items i ON c.item_id = i.id
                  WHERE c.user_id = ?
                  ORDER BY c.created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get cart item by ID
     */
    public function getById($id, $userId = null)
    {
        $query = "SELECT c.*, i.name as item_name, i.price_per_day
                  FROM cart c
                  JOIN items i ON c.item_id = i.id
                  WHERE c.id = ?";
        
        $params = [$id];
        
        if ($userId !== null) {
            $query .= " AND c.user_id = ?";
            $params[] = $userId;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update cart item quantity
     */
    public function updateQuantity($cartId, $quantity, $userId = null)
    {
        try {
            $query = "UPDATE cart SET quantity = ?, updated_at = NOW() 
                      WHERE id = ?";
            
            $params = [$quantity, $cartId];
            
            if ($userId !== null) {
                $query .= " AND user_id = ?";
                $params[] = $userId;
            }

            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Cart update quantity error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update cart item dates
     */
    public function updateDates($cartId, $startDate, $endDate, $userId = null)
    {
        try {
            $query = "UPDATE cart 
                      SET start_date = ?, end_date = ?, updated_at = NOW() 
                      WHERE id = ?";
            
            $params = [$startDate, $endDate, $cartId];
            
            if ($userId !== null) {
                $query .= " AND user_id = ?";
                $params[] = $userId;
            }

            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Cart update dates error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove item from cart
     */
    public function remove($cartId, $userId = null)
    {
        try {
            $query = "DELETE FROM cart WHERE id = ?";
            $params = [$cartId];
            
            if ($userId !== null) {
                $query .= " AND user_id = ?";
                $params[] = $userId;
            }

            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Cart remove error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all cart items for user
     */
    public function clear($userId)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM cart WHERE user_id = ?");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Cart clear error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cart count for user
     */
    public function getCartCount($userId)
    {
        $query = "SELECT COUNT(*) as total_items, SUM(quantity) as total_quantity
                  FROM cart WHERE user_id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get cart total price
     */
    public function getCartTotal($userId)
    {
        $query = "SELECT SUM(i.price_per_day * c.quantity * DATEDIFF(c.end_date, c.start_date)) as total_price
                  FROM cart c
                  JOIN items i ON c.item_id = i.id
                  WHERE c.user_id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_price'] ?? 0;
    }

    /**
     * Validate cart items availability before checkout
     */
    public function validateAvailability($userId)
    {
        $cart = $this->getUserCart($userId);
        $unavailable = [];

        foreach ($cart as $item) {
            // Check if item still exists and has enough stock
            if (!isItemAvailable($item['item_id'], $item['start_date'], $item['end_date'], $item['quantity'])) {
                $unavailable[] = $item;
            }
        }

        return $unavailable;
    }

    /**
     * Clean up old cart items (called by cron job)
     * Remove items with past end dates
     */
    public function cleanupOldCarts()
    {
        try {
            $query = "DELETE FROM cart WHERE end_date < CURDATE()";
            $stmt = $this->db->prepare($query);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Cart cleanup error: " . $e->getMessage());
            return false;
        }
    }
}