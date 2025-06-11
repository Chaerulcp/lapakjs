<?php
session_start();
require_once '../includes/db.php';

// Check for admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Validate input
    $payment_id = filter_input(INPUT_POST, 'payment_id', FILTER_VALIDATE_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $notes = trim(filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    
    if (!$payment_id || !in_array($status, ['dikonfirmasi', 'gagal'])) {
        throw new Exception('Data verifikasi tidak valid');
    }

    // Verify payment exists and can be updated
    $check_stmt = $pdo->prepare("
        SELECT p.status, o.total as amount 
        FROM payments p
        JOIN orders o ON p.order_id = o.id
        WHERE p.id = ?
    ");
    $check_stmt->execute([$payment_id]);
    $payment = $check_stmt->fetch();

    if (!$payment) {
        throw new Exception('Pembayaran tidak ditemukan');
    }

    if ($payment['status'] !== 'menunggu') {
        throw new Exception('Pembayaran ini sudah diverifikasi sebelumnya');
    }

    // Update payment status
    $stmt = $pdo->prepare("
        UPDATE payments 
        SET status = ?, notes = ?, verified_at = NOW(), verified_by = ?
        WHERE id = ? AND status = 'menunggu'
    ");
    
    if (!$stmt->execute([$status, $notes, $_SESSION['user_id'], $payment_id])) {
        throw new Exception('Gagal memperbarui status pembayaran');
    }
    
    // Log the payment verification activity
    $activity_stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, activity_type, description, created_at)
        VALUES (?, 'payment_verification', ?, NOW())
    ");
    
    $activity_description = sprintf(
        'Pembayaran #%d %s. %s',
        $payment_id,
        $status === 'dikonfirmasi' ? 'diverifikasi' : 'ditolak',
        $notes ? "Catatan: $notes" : ''
    );
    
    if (!$activity_stmt->execute([$_SESSION['user_id'], $activity_description])) {
        throw new Exception('Gagal mencatat aktivitas verifikasi');
    }
    
    // If payment is verified, update order status
    if ($status === 'dikonfirmasi') {
        $stmt = $pdo->prepare("
            UPDATE orders o
            JOIN payments p ON o.id = p.order_id
            SET o.status = 'diproses'
            WHERE p.id = ? AND o.status = 'menunggu'
        ");
        
        if (!$stmt->execute([$payment_id])) {
            throw new Exception('Gagal memperbarui status pesanan');
        }
    }
    
    $pdo->commit();

    // Get updated payment data
    $stmt = $pdo->prepare("
        SELECT 
            p.*, 
            o.total as amount,
            u.nama as verifier_name
        FROM payments p
        JOIN orders o ON p.order_id = o.id
        LEFT JOIN users u ON p.verified_by = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$payment_id]);
    $updated_payment = $stmt->fetch(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Status pembayaran berhasil diperbarui',
        'payment_id' => $payment_id,
        'payment' => [
            'status' => $updated_payment['status'],
            'verified_at' => $updated_payment['verified_at'],
            'verifier_name' => $updated_payment['verifier_name'],
            'notes' => $updated_payment['notes']
        ]
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Payment verification error: " . $e->getMessage());
    
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
