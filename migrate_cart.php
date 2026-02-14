<?php
/**
 * Migration Script: Create Cart Table
 * File: migrate_cart.php
 * 
 * Cara pakai:
 * 1. Upload file ini ke root folder project
 * 2. Akses via browser: http://localhost/camping-rental-apps/migrate_cart.php
 * 3. Atau jalankan via CLI: php migrate_cart.php
 */

require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<h2>🚀 Cart Table Migration</h2>";
    echo "<hr>";
    
    // 1. Check if table already exists
    echo "<p>📋 Checking if cart table exists...</p>";
    $checkTable = $db->query("SHOW TABLES LIKE 'cart'")->rowCount();
    
    if ($checkTable > 0) {
        echo "<p style='color: orange;'>⚠️ Table 'cart' already exists!</p>";
        echo "<p>Do you want to DROP and recreate? <a href='?force=1'>Yes, recreate</a></p>";
        
        if (!isset($_GET['force'])) {
            exit;
        }
        
        echo "<p>🗑️ Dropping existing table...</p>";
        $db->exec("DROP TABLE IF EXISTS cart");
        echo "<p style='color: green;'>✅ Old table dropped</p>";
    }
    
    // 2. Create table
    echo "<p>📦 Creating cart table...</p>";
    
    $sql = "CREATE TABLE IF NOT EXISTS cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        item_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
        
        INDEX idx_user_id (user_id),
        INDEX idx_item_id (item_id),
        INDEX idx_created_at (created_at),
        INDEX idx_user_cart (user_id, created_at DESC),
        INDEX idx_item_availability (item_id, start_date, end_date),
        
        UNIQUE KEY unique_user_item_date (user_id, item_id, start_date, end_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->exec($sql);
    
    echo "<p style='color: green;'>✅ Table 'cart' created successfully!</p>";
    
    // 3. Verify table structure
    echo "<hr>";
    echo "<h3>📊 Table Structure:</h3>";
    echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $columns = $db->query("DESCRIBE cart")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 4. Show indexes
    echo "<hr>";
    echo "<h3>🔑 Indexes:</h3>";
    echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Key Name</th><th>Column</th><th>Unique</th></tr>";
    
    $indexes = $db->query("SHOW INDEXES FROM cart")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($indexes as $idx) {
        echo "<tr>";
        echo "<td><strong>{$idx['Key_name']}</strong></td>";
        echo "<td>{$idx['Column_name']}</td>";
        echo "<td>" . ($idx['Non_unique'] == 0 ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 5. Success message
    echo "<hr>";
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 5px; margin-top: 20px;'>";
    echo "<h3 style='color: #155724; margin-top: 0;'>🎉 Migration Completed Successfully!</h3>";
    echo "<p style='color: #155724;'>Table 'cart' is ready to use.</p>";
    echo "<p style='color: #155724;'><strong>Next Steps:</strong></p>";
    echo "<ol style='color: #155724;'>";
    echo "<li>Create <code>models/Cart.php</code></li>";
    echo "<li>Create <code>controllers/CartController.php</code></li>";
    echo "<li>Create <code>views/cart/index.php</code></li>";
    echo "<li>Update navbar with cart badge</li>";
    echo "</ol>";
    echo "<p style='color: #155724;'><a href='index.php'>← Back to Home</a></p>";
    echo "</div>";
    
    // 6. Optional: Insert sample data
    echo "<hr>";
    echo "<h3>🧪 Want to insert sample data?</h3>";
    echo "<p><a href='?sample=1' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Insert Sample Data</a></p>";
    
    if (isset($_GET['sample'])) {
        echo "<p>📝 Inserting sample data...</p>";
        
        // Check if we have users and items first
        $userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $itemCount = $db->query("SELECT COUNT(*) FROM items")->fetchColumn();
        
        if ($userCount > 0 && $itemCount > 0) {
            $sampleData = "INSERT INTO cart (user_id, item_id, quantity, start_date, end_date, notes) VALUES
                (1, 1, 2, '2026-02-01', '2026-02-05', 'Untuk camping ke Gunung Bromo'),
                (1, 2, 1, '2026-02-01', '2026-02-05', 'Butuh sleeping bag hangat')";
            
            try {
                $db->exec($sampleData);
                echo "<p style='color: green;'>✅ Sample data inserted!</p>";
            } catch (PDOException $e) {
                echo "<p style='color: orange;'>⚠️ Sample data already exists or error: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ No users or items found. Please add users and items first.</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; border-radius: 5px;'>";
    echo "<h3 style='color: #721c24;'>❌ Migration Failed!</h3>";
    echo "<p style='color: #721c24;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p style='color: #721c24;'><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p style='color: #721c24;'><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}
?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        max-width: 1000px;
        margin: 50px auto;
        padding: 20px;
        background: #f8f9fa;
    }
    h2 {
        color: #333;
        border-bottom: 3px solid #007bff;
        padding-bottom: 10px;
    }
    table {
        width: 100%;
        background: white;
        margin: 20px 0;
    }
    th {
        text-align: left;
        font-weight: 600;
    }
    code {
        background: #f4f4f4;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
    }
</style>