<?php
require_once '../includes/db.php';
require_once 'includes/admin_header.php';

try {
    // Handle order status update
    if (isset($_POST['update_status'])) {
        $order_id = intval($_POST['order_id']);
        $new_status = $_POST['status'];
        
        // Validate status
        $valid_statuses = ['menunggu', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];
        if (!in_array($new_status, $valid_statuses)) {
            throw new Exception("Status tidak valid");
        }
        
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        
        // Log activity
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity_type, description) VALUES (?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'], 
            'order_status_update', 
            "Status pesanan #$order_id diubah menjadi $new_status"
        ]);
        
        $success_message = "Status pesanan berhasil diperbarui.";
    }

    // Fetch orders with filters
    $where_conditions = [];
    $params = [];

    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $where_conditions[] = "o.status = ?";
        $params[] = $_GET['status'];
    }

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $where_conditions[] = "(u.nama LIKE ? OR u.no_hp LIKE ? OR o.id LIKE ?)";
        $search_term = "%" . $_GET['search'] . "%";
        $params = array_merge($params, [$search_term, $search_term, $search_term]);
    }

    if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
        $where_conditions[] = "DATE(o.created_at) >= ?";
        $params[] = $_GET['date_from'];
    }

    if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
        $where_conditions[] = "DATE(o.created_at) <= ?";
        $params[] = $_GET['date_to'];
    }

    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Fetch orders with customer details and order items
    $query = "
        SELECT o.*, u.nama as customer_name, u.no_hp, u.email,
        GROUP_CONCAT(CONCAT(p.nama, ' (', oi.jumlah, ')') SEPARATOR ', ') as items
        FROM orders o
        JOIN users u ON o.user_id = u.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        $where_clause
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();

    // Get order statistics
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_orders,
            SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as pending_orders,
            SUM(CASE WHEN status = 'diproses' THEN 1 ELSE 0 END) as processing_orders,
            SUM(CASE WHEN status = 'dikirim' THEN 1 ELSE 0 END) as shipped_orders,
            SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as completed_orders,
            COALESCE(SUM(total), 0) as total_revenue
        FROM orders
    ");
    $stats = $stmt->fetch();

} catch (PDOException $e) {
    error_log("Database error in manage_orders.php: " . $e->getMessage());
    $error_message = "Terjadi kesalahan saat memuat data pesanan.";
} catch (Exception $e) {
    error_log("Error in manage_orders.php: " . $e->getMessage());
    $error_message = $e->getMessage();
}
?>

<!-- Page header -->
<div class="bg-white shadow rounded-lg mb-6">
    <div class="px-6 py-4">
        <h1 class="text-2xl font-semibold text-gray-900">Manajemen Pesanan</h1>
    </div>
</div>

<?php if (isset($success_message)): ?>
    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
        <?php echo htmlspecialchars($success_message); ?>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
        <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="stat-card hover-shadow">
        <div class="stat-icon bg-red-100 text-red-600">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Total Pesanan</h3>
            <div class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total_orders'] ?? 0); ?></div>
        </div>
    </div>
    
    <div class="stat-card hover-shadow">
        <div class="stat-icon bg-yellow-100 text-yellow-600">
            <i class="fas fa-clock"></i>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Menunggu</h3>
            <div class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['pending_orders'] ?? 0); ?></div>
        </div>
    </div>
    
    <div class="stat-card hover-shadow">
        <div class="stat-icon bg-blue-100 text-blue-600">
            <i class="fas fa-box"></i>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Diproses</h3>
            <div class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['processing_orders'] ?? 0); ?></div>
        </div>
    </div>
    
    <div class="stat-card hover-shadow">
        <div class="stat-icon bg-green-100 text-green-600">
            <i class="fas fa-check-circle"></i>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Total Pendapatan</h3>
            <div class="text-2xl font-semibold text-gray-900">Rp <?php echo number_format($stats['total_revenue'] ?? 0, 0, ',', '.'); ?></div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
        <form action="" method="GET" class="space-y-4 sm:space-y-0 sm:flex sm:items-center sm:gap-4">
            <div class="flex-1">
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           placeholder="Cari pesanan..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <div class="w-full sm:w-48">
                <select name="status" 
                        class="w-full border border-gray-300 rounded-lg py-2 px-4 focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="">Semua Status</option>
                    <option value="menunggu" <?php echo (isset($_GET['status']) && $_GET['status'] === 'menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="diproses" <?php echo (isset($_GET['status']) && $_GET['status'] === 'diproses') ? 'selected' : ''; ?>>Diproses</option>
                    <option value="dikirim" <?php echo (isset($_GET['status']) && $_GET['status'] === 'dikirim') ? 'selected' : ''; ?>>Dikirim</option>
                    <option value="selesai" <?php echo (isset($_GET['status']) && $_GET['status'] === 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                </select>
            </div>

            <div class="w-full sm:w-48">
                <input type="date" 
                       name="date_from" 
                       class="w-full border border-gray-300 rounded-lg py-2 px-4 focus:ring-2 focus:ring-primary focus:border-primary"
                       value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>"
                       placeholder="Dari Tanggal">
            </div>

            <div class="w-full sm:w-48">
                <input type="date" 
                       name="date_to" 
                       class="w-full border border-gray-300 rounded-lg py-2 px-4 focus:ring-2 focus:ring-primary focus:border-primary"
                       value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>"
                       placeholder="Sampai Tanggal">
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    Filter
                </button>
                <a href="manage_orders.php" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Reset
                </a>
            </div>
        </form>
</div>

<!-- Orders Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
    <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $order): ?>
        <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow">
            <!-- Order Header -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Order #<?php echo htmlspecialchars($order['id']); ?></h3>
                        <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                            <span class="flex items-center">
                                <i class="fas fa-calendar mr-1"></i>
                                <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xl font-bold text-gray-900">Rp <?php echo number_format($order['total'], 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Customer Details -->
            <div class="p-6 border-b border-gray-200">
                <div class="space-y-2">
                    <div class="font-medium text-gray-900"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($order['no_hp']); ?></div>
                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($order['email']); ?></div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="p-6 border-b border-gray-200">
                <div class="text-sm text-gray-600">
                    <strong>Produk:</strong>
                    <div class="mt-1 text-gray-900">
                        <?php 
                        $items = $order['items'] ?? 'Tidak ada item';
                        echo htmlspecialchars($items); 
                        ?>
                    </div>
                </div>
            </div>

            <!-- Status and Actions -->
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-gray-700">Status:</span>
                    <form action="" method="POST" class="inline">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <select name="status" 
                                class="text-sm rounded-full px-3 py-1 font-medium border-0 focus:ring-2 focus:ring-primary <?php 
                                    switch($order['status']) {
                                        case 'menunggu': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'diproses': echo 'bg-blue-100 text-blue-800'; break;
                                        case 'dikirim': echo 'bg-green-100 text-green-800'; break;
                                        case 'selesai': echo 'bg-gray-100 text-gray-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                ?>"
                                onchange="this.form.submit()">
                            <option value="menunggu" <?php echo $order['status'] === 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                            <option value="diproses" <?php echo $order['status'] === 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                            <option value="dikirim" <?php echo $order['status'] === 'dikirim' ? 'selected' : ''; ?>>Dikirim</option>
                            <option value="selesai" <?php echo $order['status'] === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                        </select>
                        <input type="hidden" name="update_status" value="1">
                    </form>
                </div>

                <div class="flex gap-3">
                    <button onclick="viewOrderDetails(<?php echo $order['id']; ?>)" 
                            class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-center"
                            title="Lihat Detail">
                        <i class="fas fa-eye mr-2"></i> Detail
                    </button>
                    <a href="print_invoice.php?id=<?php echo $order['id']; ?>" 
                       class="flex-1 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-center" 
                       title="Cetak Invoice" target="_blank">
                        <i class="fas fa-print mr-2"></i> Invoice
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-span-full">
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                <div class="text-lg font-medium text-gray-900">Tidak ada pesanan ditemukan</div>
                <div class="text-sm text-gray-500">Belum ada pesanan yang masuk atau sesuai dengan filter yang dipilih.</div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function viewOrderDetails(orderId) {
    // Implement order details modal or redirect to details page
    window.location.href = `order_detail.php?id=${orderId}`;
}

// Auto-submit status form on change
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('select[name="status"]').forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
