<?php
session_start();
require_once '../includes/db.php';

// Check for admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    // Get payment statistics with COALESCE to ensure non-null values
    $stats_stmt = $pdo->prepare("
        SELECT 
            COALESCE(COUNT(*), 0) as total_payments,
            COALESCE(SUM(CASE WHEN p.status = 'menunggu' THEN 1 ELSE 0 END), 0) as pending_payments,
            COALESCE(SUM(CASE WHEN p.status = 'dikonfirmasi' THEN 1 ELSE 0 END), 0) as verified_payments,
            COALESCE(SUM(CASE WHEN p.status = 'gagal' THEN 1 ELSE 0 END), 0) as rejected_payments,
            COALESCE(SUM(CASE WHEN p.status = 'dikonfirmasi' THEN o.total ELSE 0 END), 0) as total_verified
        FROM payments p
        LEFT JOIN orders o ON p.order_id = o.id
    ");
    
    $stats_stmt->execute();
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

    // Ensure all values are numeric
    foreach ($stats as $key => $value) {
        $stats[$key] = is_numeric($value) ? (int)$value : 0;
    }

    header('Content-Type: application/json');
    echo json_encode($stats);

} catch (Exception $e) {
    error_log("Error fetching payment statistics: " . $e->getMessage());
    
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => 'Terjadi kesalahan saat memuat statistik pembayaran'
    ]);
}
?>
