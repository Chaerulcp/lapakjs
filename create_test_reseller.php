<?php
require_once 'includes/db.php';

try {
    $stmt = $pdo->prepare('INSERT INTO users (nama, email, password, alamat, no_hp, role) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute(['Test Reseller', 'testreseller@example.com', password_hash('password123', PASSWORD_DEFAULT), 'Test Address 3', '081234567892', 'reseller']);
    echo 'Test reseller account created successfully.';
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
