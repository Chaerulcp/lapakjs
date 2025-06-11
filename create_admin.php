<?php
require_once 'includes/db.php';

try {
    // Check if admin exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $adminCount = $stmt->fetchColumn();

    if ($adminCount == 0) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (nama, email, password, role, created_at) 
            VALUES (?, ?, ?, 'admin', NOW())
        ");
        $stmt->execute(['Administrator', 'admin@admin.com', $password]);
        echo "✅ Admin user created successfully!\n";
        echo "Email: admin@admin.com\n";
        echo "Password: admin123\n";
    } else {
        echo "ℹ️ Admin user already exists.\n";
        
        // Update existing admin password
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE role = 'admin' LIMIT 1");
        $stmt->execute([$password]);
        echo "✅ Admin password updated to: admin123\n";
    }
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
