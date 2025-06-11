<?php
require_once 'includes/db.php';

try {
    $pdo->beginTransaction();
    
    // Get admin and customer user IDs
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'testadmin@example.com'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'testcustomer@example.com'");
    $stmt->execute();
    $customer = $stmt->fetch();
    
    if (!$admin || !$customer) {
        throw new Exception("Test users not found");
    }
    
    // Create test orders
    $orders = [
        ['user_id' => $customer['id'], 'total' => 75000, 'status' => 'menunggu', 'alamat' => 'Jl. Test 1', 'metode_pembayaran' => 'transfer'],
        ['user_id' => $customer['id'], 'total' => 50000, 'status' => 'menunggu', 'alamat' => 'Jl. Test 2', 'metode_pembayaran' => 'transfer'],
        ['user_id' => $customer['id'], 'total' => 100000, 'status' => 'diproses', 'alamat' => 'Jl. Test 3', 'metode_pembayaran' => 'transfer']
    ];
    
    $order_ids = [];
    foreach ($orders as $order) {
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, total, status, alamat, metode_pembayaran, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$order['user_id'], $order['total'], $order['status'], $order['alamat'], $order['metode_pembayaran']]);
        $order_ids[] = $pdo->lastInsertId();
    }
    
    // Create test products for order items
    $stmt = $pdo->prepare("SELECT id FROM products LIMIT 2");
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    if (count($products) < 2) {
        // Create test products if they don't exist
        $stmt = $pdo->prepare("
            INSERT INTO products (nama, deskripsi, harga, harga_reseller, stok, foto, kategori, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute(['Test Sambal A', 'Test Description A', 25000, 20000, 100, 'test-a.jpg', 'Sambal']);
        $product1_id = $pdo->lastInsertId();
        
        $stmt->execute(['Test Sambal B', 'Test Description B', 30000, 25000, 50, 'test-b.jpg', 'Sambal']);
        $product2_id = $pdo->lastInsertId();
        
        $products = [['id' => $product1_id], ['id' => $product2_id]];
    }
    
    // Create order items for each order
    foreach ($order_ids as $i => $order_id) {
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, jumlah, harga) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$order_id, $products[0]['id'], 2, 25000]);
        if ($i < 2) { // Only for first two orders
            $stmt->execute([$order_id, $products[1]['id'], 1, 30000]);
        }
    }
    
    // Create test payments
    $payments = [
        ['order_id' => $order_ids[0], 'metode' => 'transfer', 'status' => 'menunggu', 'bukti_transfer' => 'test_payment_1.jpg'],
        ['order_id' => $order_ids[1], 'metode' => 'transfer', 'status' => 'menunggu', 'bukti_transfer' => 'test_payment_2.jpg'],
        ['order_id' => $order_ids[2], 'metode' => 'transfer', 'status' => 'dikonfirmasi', 'bukti_transfer' => 'test_payment_3.jpg']
    ];
    
    foreach ($payments as $payment) {
        $stmt = $pdo->prepare("
            INSERT INTO payments (order_id, metode, status, bukti_transfer, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$payment['order_id'], $payment['metode'], $payment['status'], $payment['bukti_transfer']]);
    }
    
    $pdo->commit();
    echo "✅ Test payment data created successfully!\n";
    echo "Created " . count($order_ids) . " orders and " . count($payments) . " payments\n";
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
