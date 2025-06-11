<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/db.php';

session_start();
// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Content-Type: application/json'); // Set header before echoing JSON
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Anda harus login sebagai admin.']);
    exit();
}

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    if (!$user_id) {
        $response['message'] = 'ID pengguna tidak valid.';
        echo json_encode($response);
        exit();
    }

    // Get user details before deletion for logging and role check
    try {
        $stmt = $pdo->prepare("SELECT nama, role FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_details = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user_details) {
            $response['message'] = 'Pengguna tidak ditemukan.';
            echo json_encode($response);
            exit();
        }

        $user_name_to_delete = $user_details['nama'];
        $user_role = $user_details['role'];

        if ($user_role === 'admin') {
            $response['message'] = 'Tidak dapat menghapus akun admin.';
            echo json_encode($response);
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error checking user details: " . $e->getMessage());
        $response['message'] = 'Terjadi kesalahan saat memeriksa detail pengguna.';
        echo json_encode($response);
        exit();
    }

    try {
        $pdo->beginTransaction();

        // 1. Delete from reseller_commissions
        $stmt = $pdo->prepare("DELETE FROM reseller_commissions WHERE reseller_id = ?");
        $stmt->execute([$user_id]);

        // 2. Delete from testimonials
        $stmt = $pdo->prepare("DELETE FROM testimonials WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // 3. Delete from activity_logs
        $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // 4. Delete from password_resets
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Get all order IDs associated with the user
        $stmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $order_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($order_ids)) {
            $placeholders = implode(',', array_fill(0, count($order_ids), '?'));

            // 5. Delete from payments (linked to user's orders)
            $stmt = $pdo->prepare("DELETE FROM payments WHERE order_id IN ($placeholders)");
            $stmt->execute($order_ids);

            // 6. Delete from order_items (linked to user's orders)
            $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id IN ($placeholders)");
            $stmt->execute($order_ids);
        }

        // 7. Delete from orders
        $stmt = $pdo->prepare("DELETE FROM orders WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // 8. Delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);

        // Log the activity
        $admin_user_id = $_SESSION['user_id'];
        $activity_type = 'user_deletion';
        $description = "Akun pengguna dengan ID: {$user_id} dan nama: {$user_name_to_delete} berhasil dihapus.";
        
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity_type, description) VALUES (?, ?, ?)");
        $log_stmt->execute([$admin_user_id, $activity_type, $description]);

        $pdo->commit();
        $response['success'] = true;
        $response['message'] = 'Akun pengguna dan semua data terkait berhasil dihapus.';

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error deleting user: " . $e->getMessage());
        $response['message'] = 'Gagal menghapus akun pengguna dan data terkait: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
?>
