<?php
require_once '../includes/db.php';
require_once 'includes/admin_header.php';

$order = null;
$items = [];
$status_history = [];
$error_message = '';

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception("ID pesanan tidak ditemukan");
    }

    $order_id = intval($_GET['id']);
    if ($order_id <= 0) {
        throw new Exception("ID pesanan tidak valid");
    }

    // Fetch order details with customer info and payment info
    $query = "
        SELECT o.*, u.nama as customer_name, u.email, u.no_hp,
               p.metode as payment_method, p.status as payment_status,
               p.bukti_transfer, p.created_at as payment_date
        FROM orders o
        JOIN users u ON o.user_id = u.id
        LEFT JOIN payments p ON o.id = p.order_id
        WHERE o.id = ?
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        throw new Exception("Pesanan tidak ditemukan");
    }

    // Fetch order items
    $query = "
        SELECT oi.*, p.nama as product_name, p.harga as product_price
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();

    // Fetch order status history
    $query = "
        SELECT * FROM activity_logs 
        WHERE activity_type = 'order_status_update' 
        AND description LIKE ?
        ORDER BY created_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(["%pesanan #$order_id%"]);
    $status_history = $stmt->fetchAll();

} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>

<!-- Page header -->
<div class="bg-white shadow rounded-lg mb-6">
    <div class="px-6 py-4 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">
                Detail Pesanan #<?php echo $order ? htmlspecialchars((string)$order['id']) : 'N/A'; ?>
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                <?php if ($order && $order['created_at']): ?>
                    Dibuat pada <?php echo date('d F Y H:i', strtotime($order['created_at'])); ?>
                <?php else: ?>
                    Informasi pesanan tidak tersedia
                <?php endif; ?>
            </p>
        </div>
        <a href="manage_orders.php" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>
</div>

<?php if ($error_message): ?>
    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
        <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php elseif ($order): ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Order Summary and Status -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Order Status -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Status Pesanan</h2>
                <div class="flex items-center gap-4">
                    <span class="px-3 py-1 rounded-full text-sm font-medium <?php 
                        switch($order['status']) {
                            case 'menunggu': echo 'bg-yellow-100 text-yellow-800'; break;
                            case 'diproses': echo 'bg-blue-100 text-blue-800'; break;
                            case 'dikirim': echo 'bg-green-100 text-green-800'; break;
                            case 'selesai': echo 'bg-gray-100 text-gray-800'; break;
                            default: echo 'bg-gray-100 text-gray-800';
                        }
                    ?>">
                        <?php echo ucfirst((string)$order['status']); ?>
                    </span>
                    <form action="manage_orders.php" method="POST" class="flex-1">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <div class="flex gap-2">
                            <select name="status" class="flex-1 rounded-lg border-gray-300 focus:border-primary focus:ring-primary">
                                <option value="menunggu" <?php echo ($order['status'] == 'menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                                <option value="diproses" <?php echo ($order['status'] == 'diproses') ? 'selected' : ''; ?>>Diproses</option>
                                <option value="dikirim" <?php echo ($order['status'] == 'dikirim') ? 'selected' : ''; ?>>Dikirim</option>
                                <option value="selesai" <?php echo ($order['status'] == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                            </select>
                            <button type="submit" name="update_status" value="1" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                Update Status
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Status History -->
                <?php if (!empty($status_history)): ?>
                <div class="mt-6">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">Riwayat Status</h3>
                    <div class="space-y-3">
                        <?php foreach ($status_history as $history): ?>
                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-32 text-gray-500">
                                <?php echo date('d/m/Y H:i', strtotime($history['created_at'])); ?>
                            </div>
                            <div class="flex-1 text-gray-900">
                                <?php echo htmlspecialchars((string)$history['description']); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Items -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Detail Produk</h2>
                <?php if (!empty($items)): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left border-b">
                                <th class="pb-3 text-sm font-medium text-gray-500">Produk</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">Harga</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">Jumlah</th>
                                <th class="pb-3 text-sm font-medium text-gray-500 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars((string)$item['product_name']); ?>
                                    </div>
                                </td>
                                <td class="py-4">
                                    <div class="text-sm text-gray-900">
                                        Rp <?php echo number_format($item['product_price'], 0, ',', '.'); ?>
                                    </div>
                                </td>
                                <td class="py-4">
                                    <div class="text-sm text-gray-900">
                                        <?php echo $item['jumlah']; ?>
                                    </div>
                                </td>
                                <td class="py-4 text-right">
                                    <div class="text-sm text-gray-900">
                                        Rp <?php echo number_format($item['product_price'] * $item['jumlah'], 0, ',', '.'); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="border-t">
                                <td colspan="3" class="py-4 text-sm font-medium text-gray-900">Total</td>
                                <td class="py-4 text-right text-sm font-medium text-gray-900">
                                    Rp <?php echo number_format($order['total'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-box-open text-4xl mb-4"></i>
                    <p>Tidak ada item dalam pesanan ini</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Customer and Payment Info -->
    <div class="space-y-6">
        <!-- Customer Info -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pelanggan</h2>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm text-gray-500">Nama</label>
                        <div class="text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars((string)$order['customer_name']); ?>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Email</label>
                        <div class="text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars((string)$order['email']); ?>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">No. Telepon</label>
                        <div class="text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars((string)$order['no_hp']); ?>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Alamat Pengiriman</label>
                        <div class="text-sm font-medium text-gray-900">
                            <?php echo isset($order['alamat_pengiriman']) ? nl2br(htmlspecialchars((string)$order['alamat_pengiriman'])) : '-'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Info -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pembayaran</h2>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm text-gray-500">Status Pembayaran</label>
                        <div class="mt-1">
                            <span class="px-3 py-1 rounded-full text-sm font-medium <?php 
                                echo ($order['payment_status'] == 'dikonfirmasi') ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                            ?>">
                                <?php echo ($order['payment_status'] == 'dikonfirmasi') ? 'Terverifikasi' : 'Menunggu Verifikasi'; ?>
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Metode Pembayaran</label>
                        <div class="text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars((string)($order['payment_method'] ?? '-')); ?>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Tanggal Pembayaran</label>
                        <div class="text-sm font-medium text-gray-900">
                            <?php echo isset($order['payment_date']) ? date('d F Y H:i', strtotime($order['payment_date'])) : '-'; ?>
                        </div>
                    </div>
                    <?php if (isset($order['bukti_transfer']) && $order['bukti_transfer']): ?>
                    <div>
                        <label class="text-sm text-gray-500">Bukti Pembayaran</label>
                        <div class="mt-2">
                            <a href="../public/uploads/payments/<?php echo htmlspecialchars((string)$order['bukti_transfer']); ?>" 
                               target="_blank"
                               class="inline-block">
                                <img src="../public/uploads/payments/<?php echo htmlspecialchars((string)$order['bukti_transfer']); ?>" 
                                     alt="Bukti Pembayaran" 
                                     class="max-w-full h-auto rounded-lg border border-gray-200">
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Aksi</h2>
                <div class="space-y-3">
                    <a href="print_invoice.php?id=<?php echo $order['id']; ?>" 
                       target="_blank"
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-print mr-2"></i> Cetak Invoice
                    </a>
                    <a href="manage_orders.php" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-list mr-2"></i> Kembali ke Daftar Pesanan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<?php require_once 'includes/admin_footer.php'; ?>
