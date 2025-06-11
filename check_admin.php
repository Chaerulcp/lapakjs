<?php
require_once 'includes/db.php';

try {
    // Check admin users
    $stmt = $pdo->query("SELECT id, nama, email, role FROM users WHERE role = 'admin'");
    $admins = $stmt->fetchAll();
    
    echo "Admin Users:\n";
    foreach ($admins as $admin) {
        echo "ID: {$admin['id']}, Name: {$admin['nama']}, Email: {$admin['email']}, Role: {$admin['role']}\n";
    }
    
    // Check if default admin exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute(['admin@admin.com']);
    $defaultAdminExists = $stmt->fetchColumn();
    
    echo "\nDefault admin (admin@admin.com) exists: " . ($defaultAdminExists ? "Yes" : "No") . "\n";
    
    // Check test admin
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute(['testadmin@example.com']);
    $testAdminExists = $stmt->fetchColumn();
    
    echo "Test admin (testadmin@example.com) exists: " . ($testAdminExists ? "Yes" : "No") . "\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
