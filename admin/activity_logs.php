<?php
require_once '../includes/db.php';
require_once 'includes/admin_header.php';

// Check for admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

try {
    // Fetch activities with pagination
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $per_page = 20;
    $offset = ($page - 1) * $per_page;

    // Get total count for pagination
    $count_stmt = $pdo->query("SELECT COUNT(*) FROM activity_logs");
    $total_records = $count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $per_page);

    // Fetch activities with user details
    $stmt = $pdo->prepare("
        SELECT 
            al.*,
            u.nama as user_name,
            u.email as user_email
        FROM activity_logs al
        LEFT JOIN users u ON al.user_id = u.id
        ORDER BY al.created_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->execute([$per_page, $offset]);
    $activities = $stmt->fetchAll();

    // Get activity statistics
    $stats_stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_activities,
            COUNT(DISTINCT user_id) as unique_users,
            COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_activities
        FROM activity_logs
    ");
    $stats = $stats_stmt->fetch();

} catch (PDOException $e) {
    error_log("Database error in activity_logs.php: " . $e->getMessage());
    $error_message = "Terjadi kesalahan saat memuat data aktivitas.";
} catch (Exception $e) {
    error_log("Error in activity_logs.php: " . $e->getMessage());
    $error_message = $e->getMessage();
}
?>

<!-- Page header -->
<div class="bg-white shadow rounded-lg mb-6">
    <div class="px-6 py-4">
        <h1 class="text-2xl font-semibold text-gray-900">Log Aktivitas</h1>
    </div>
</div>

<?php if (isset($error_message)): ?>
    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
        <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<!-- Activity Statistics -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="fas fa-list text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Total Aktivitas</h3>
                <div class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total_activities'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-users text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Pengguna Aktif</h3>
                <div class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['unique_users'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <i class="fas fa-calendar-day text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Aktivitas Hari Ini</h3>
                <div class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['today_activities'] ?? 0); ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Logs Table -->
<div class="bg-white shadow rounded-lg overflow-hidden">
    <?php if (!empty($activities)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe Aktivitas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($activities as $activity): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?php 
                                    $time_diff = time() - strtotime($activity['created_at']);
                                    if ($time_diff < 60) {
                                        echo "Baru saja";
                                    } elseif ($time_diff < 3600) {
                                        echo floor($time_diff / 60) . " menit lalu";
                                    } elseif ($time_diff < 86400) {
                                        echo floor($time_diff / 3600) . " jam lalu";
                                    } else {
                                        echo floor($time_diff / 86400) . " hari lalu";
                                    }
                                    ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($activity['user_name']): ?>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($activity['user_name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($activity['user_email']); ?></div>
                                <?php else: ?>
                                    <span class="text-sm text-gray-500">Pengguna tidak ditemukan</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                $badge_colors = [
                                    'payment_verification' => 'bg-blue-100 text-blue-800',
                                    'order_status_update' => 'bg-purple-100 text-purple-800',
                                    'product_add' => 'bg-green-100 text-green-800',
                                    'product_edit' => 'bg-yellow-100 text-yellow-800',
                                    'product_delete' => 'bg-red-100 text-red-800',
                                    'user_login' => 'bg-indigo-100 text-indigo-800',
                                    'user_logout' => 'bg-gray-100 text-gray-800',
                                    'user_register' => 'bg-emerald-100 text-emerald-800'
                                ];
                                $activity_labels = [
                                    'payment_verification' => 'Verifikasi Pembayaran',
                                    'order_status_update' => 'Update Status Pesanan',
                                    'product_add' => 'Tambah Produk',
                                    'product_edit' => 'Edit Produk',
                                    'product_delete' => 'Hapus Produk',
                                    'user_login' => 'Login',
                                    'user_logout' => 'Logout',
                                    'user_register' => 'Registrasi'
                                ];
                                $badge_color = $badge_colors[$activity['activity_type']] ?? 'bg-gray-100 text-gray-800';
                                $activity_label = $activity_labels[$activity['activity_type']] ?? ucfirst(str_replace('_', ' ', $activity['activity_type']));
                                ?>
                                <span class="px-3 py-1 text-xs font-medium rounded-full <?php echo $badge_color; ?>">
                                    <?php echo $activity_label; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($activity['description']); ?></div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <nav class="flex justify-center">
                    <ul class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <li>
                                <a href="?page=<?php echo $page - 1; ?>" 
                                   class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    <span class="sr-only">Sebelumnya</span>
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li>
                                <a href="?page=<?php echo $i; ?>" 
                                   class="px-3 py-2 text-sm font-medium <?php echo $i === $page 
                                        ? 'text-white bg-primary border border-primary' 
                                        : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50'; ?> rounded-lg">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li>
                                <a href="?page=<?php echo $page + 1; ?>" 
                                   class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    <span class="sr-only">Berikutnya</span>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="p-12 text-center">
            <i class="fas fa-info-circle text-4xl text-gray-300 mb-4"></i>
            <div class="text-lg font-medium text-gray-900">Belum ada aktivitas yang tercatat</div>
            <div class="text-sm text-gray-500">Aktivitas pengguna akan muncul di sini</div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
