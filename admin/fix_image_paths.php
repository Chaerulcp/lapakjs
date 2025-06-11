<?php
require_once 'includes/db.php';

try {
    // Get all products
    $stmt = $pdo->query("SELECT id, foto FROM products");
    $products = $stmt->fetchAll();

    foreach ($products as $product) {
        if ($product['foto'] && strpos($product['foto'], '/') === 0) {
            // Remove leading slash from image path
            $newPath = ltrim($product['foto'], '/');
            
            // Update the path in database
            $updateStmt = $pdo->prepare("UPDATE products SET foto = ? WHERE id = ?");
            $updateStmt->execute([$newPath, $product['id']]);
        }
    }

    echo "Image paths updated successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
