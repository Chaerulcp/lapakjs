<?php
session_start();
require_once '../includes/db.php';

// Check for admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$order = null;
$items = [];
$error_message = '';

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception("ID pesanan tidak ditemukan");
    }

    $order_id = intval($_GET['id']);
    if ($order_id <= 0) {
        throw new Exception("ID pesanan tidak valid");
    }

    // Fetch order details with customer info
    $query = "
        SELECT o.*, u.nama as customer_name, u.email, u.no_hp,
               p.metode as payment_method, p.status as payment_status,
               p.created_at as payment_date
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

} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $order ? $order['id'] : 'Error'; ?> - Sambal Mama Ana</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page {
                margin: 0;
                size: A4;
            }
            body {
                margin: 1.6cm;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php if ($error_message): ?>
    <div class="max-w-4xl mx-auto my-8">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <div class="mt-4">
            <a href="manage_orders.php" class="text-blue-600 hover:underline">
                &larr; Kembali ke Daftar Pesanan
            </a>
        </div>
    </div>
    <?php else: ?>

    <!-- Print Button -->
    <div class="max-w-4xl mx-auto my-8 no-print">
        <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Cetak Invoice
        </button>
        <a href="manage_orders.php" class="ml-4 text-blue-600 hover:underline">
            Kembali ke Daftar Pesanan
        </a>
    </div>

    <!-- Invoice -->
    <div class="max-w-4xl mx-auto bg-white shadow-sm print:shadow-none">
        <!-- Header -->
        <div class="p-8 border-b">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">INVOICE</h1>
                    <p class="text-sm text-gray-600 mt-1">
                        #<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?>
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-xl font-bold text-gray-900">Sambal Mama Ana</div>
                    <div class="text-sm text-gray-600 mt-1">
                        Jl. Contoh No. 123<br>
                        Kota Contoh, 12345<br>
                        Indonesia<br>
                        Telp: (021) 123-4567
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer & Order Info -->
        <div class="p-8 border-b">
            <div class="grid grid-cols-2 gap-8">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 uppercase mb-2">Ditagihkan Kepada:</h2>
                    <div class="text-sm">
                        <div class="font-medium"><?php echo htmlspecialchars((string)$order['customer_name']); ?></div>
                        <div class="text-gray-600 mt-1">
                            <?php echo htmlspecialchars((string)$order['email']); ?><br>
                            <?php echo htmlspecialchars((string)$order['no_hp']); ?><br>
                            <?php echo isset($order['alamat_pengiriman']) ? nl2br(htmlspecialchars((string)$order['alamat_pengiriman'])) : ''; ?>
                        </div>
                    </div>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 uppercase mb-2">Informasi Pesanan:</h2>
                    <div class="text-sm">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-gray-600">Tanggal Pesanan:</div>
                            <div><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></div>
                            
                            <div class="text-gray-600">Status Pesanan:</div>
                            <div><?php echo ucfirst($order['status']); ?></div>
                            
                            <div class="text-gray-600">Metode Pembayaran:</div>
                            <div><?php echo htmlspecialchars((string)($order['payment_method'] ?? '-')); ?></div>
                            
                            <div class="text-gray-600">Status Pembayaran:</div>
                            <div><?php echo $order['payment_status'] == 'dikonfirmasi' ? 'Lunas' : 'Belum Lunas'; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items -->
        <div class="p-8">
            <table class="w-full">
                <thead>
                    <tr class="text-sm font-semibold uppercase text-gray-900">
                        <th class="text-left pb-4">Produk</th>
                        <th class="text-right pb-4">Harga</th>
                        <th class="text-right pb-4">Jumlah</th>
                        <th class="text-right pb-4">Total</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php foreach ($items as $item): ?>
                    <tr class="border-t">
                        <td class="py-4">
                            <?php echo htmlspecialchars((string)$item['product_name']); ?>
                        </td>
                        <td class="py-4 text-right">
                            Rp <?php echo number_format($item['product_price'], 0, ',', '.'); ?>
                        </td>
                        <td class="py-4 text-right">
                            <?php echo $item['jumlah']; ?>
                        </td>
                        <td class="py-4 text-right">
                            Rp <?php echo number_format($item['product_price'] * $item['jumlah'], 0, ',', '.'); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-900">
                        <td colspan="3" class="py-4 text-sm font-semibold text-gray-900">Total</td>
                        <td class="py-4 text-right text-sm font-semibold text-gray-900">
                            Rp <?php echo number_format($order['total'], 0, ',', '.'); ?>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <!-- Notes -->
            <div class="mt-8 pt-8 border-t text-sm">
                <h3 class="font-semibold text-gray-900 mb-2">Catatan:</h3>
                <p class="text-gray-600">
                    Terima kasih telah berbelanja di Sambal Mama Ana. Untuk pertanyaan atau informasi lebih lanjut, 
                    silakan hubungi kami di nomor telepon yang tertera.
                </p>
            </div>
        </div>
    </div>

    <?php endif; ?>
</body>
</html>
