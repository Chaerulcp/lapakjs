<?php
require_once 'includes/db.php';

try {
    $pdo->beginTransaction();
    
    // Update admin password to 'testpass123'
    $password = password_hash('testpass123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        UPDATE users 
        SET password = ? 
        WHERE email = 'testadmin@example.com' AND role = 'admin'
    ");
    
    if ($stmt->execute([$password])) {
        $pdo->commit();
        echo "✅ Admin password updated successfully!\n";
    } else {
        throw new Exception("Failed to update admin password");
    }
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ Error: " . $e->getMessage() . "\n";
}
