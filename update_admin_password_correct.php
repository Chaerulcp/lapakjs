<?php
require_once 'includes/db.php';

try {
    // Update the password for the existing admin user
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@sambalmamaana.com'");
    $result = $stmt->execute([$password]);
    
    if ($result) {
        echo "✅ Password updated successfully for admin@sambalmamaana.com\n";
        echo "New password: admin123\n";
        
        // Test the login
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute(['admin@sambalmamaana.com']);
        $user = $stmt->fetch();
        
        if ($user && password_verify('admin123', $user['password'])) {
            echo "✅ Password verification test successful!\n";
        } else {
            echo "❌ Password verification test failed!\n";
        }
    } else {
        echo "❌ Failed to update password\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?>
