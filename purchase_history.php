<?php
include 'includes/config.php'; // Must be first to handle session ini settings
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch purchase history for the logged-in user
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY tanggal DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$purchases = $stmt->fetchAll();
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-center text-gray-900 mb-8">Riwayat Pembelian</h1>

    <?php if (count($purchases) > 0): ?>
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pesanan</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pesanan</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($purchases as $purchase): ?>
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($purchase['id']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($purchase['tanggal']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp <?php echo number_format($purchase['total'], 0, ',', '.'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        <?php
                                        switch($purchase['status']) {
                                            case 'menunggu':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'diproses':
                                                echo 'bg-blue-100 text-blue-800';
                                                break;
                                            case 'dikirim':
                                                echo 'bg-indigo-100 text-indigo-800';
                                                break;
                                            case 'selesai':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'dibatalkan':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst($purchase['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                    <a href="payment.php?order_id=<?php echo htmlspecialchars($purchase['id']); ?>" class="inline-flex items-center justify-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-primary hover:bg-primary/90 transition-colors">Lihat Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <p class="text-gray-600 text-lg">Anda belum memiliki riwayat pembelian.</p>
            <p class="text-gray-500 mt-2">Mulai belanja sekarang untuk melihat riwayat pesanan Anda di sini!</p>
            <a href="products.php" class="mt-6 inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary hover:bg-primary/90 transition-colors">
                Jelajahi Produk
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
