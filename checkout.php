<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Redirect to cart if empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

$error = '';
$success = '';

// Get user details
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'Terjadi kesalahan saat memuat data pengguna';
}

// Calculate total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['harga'] * $item['quantity'];
}

// Process checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs using htmlspecialchars instead of deprecated FILTER_SANITIZE_STRING
    $alamat = isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat'], ENT_QUOTES, 'UTF-8') : '';
    $tanggal_kirim = isset($_POST['tanggal_kirim']) ? htmlspecialchars($_POST['tanggal_kirim'], ENT_QUOTES, 'UTF-8') : '';
    $metode_pembayaran = isset($_POST['metode_pembayaran']) ? htmlspecialchars($_POST['metode_pembayaran'], ENT_QUOTES, 'UTF-8') : '';

    if (empty($alamat) || empty($tanggal_kirim) || empty($metode_pembayaran)) {
        $error = 'Semua field harus diisi';
    } else {
        try {
            $pdo->beginTransaction();

            // Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, total, status, alamat, metode_pembayaran, tanggal_kirim) 
                VALUES (?, ?, 'menunggu', ?, ?, ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $total, $alamat, $metode_pembayaran, $tanggal_kirim]);
            $order_id = $pdo->lastInsertId();

            // Create order items
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, jumlah, harga) 
                VALUES (?, ?, ?, ?)
            ");

            foreach ($_SESSION['cart'] as $product_id => $item) {
                $stmt->execute([$order_id, $product_id, $item['quantity'], $item['harga']]);

                // Update stock
                $update_stock = $pdo->prepare("
                    UPDATE products 
                    SET stok = stok - ? 
                    WHERE id = ?
                ");
                $update_stock->execute([$item['quantity'], $product_id]);
            }

            // Create payment record
            $stmt = $pdo->prepare("
                INSERT INTO payments (order_id, metode, status) 
                VALUES (?, ?, 'menunggu')
            ");
            $stmt->execute([$order_id, $metode_pembayaran]);

            $pdo->commit();

            // Clear cart and store success message in session
            unset($_SESSION['cart']);
            $_SESSION['success_message'] = 'Pesanan berhasil dibuat!';
            
            // Use JavaScript for redirection instead of header()
            echo "<script>window.location.href = 'payment.php?order_id=" . $order_id . "';</script>";
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log($e->getMessage());
            $error = 'Terjadi kesalahan saat memproses pesanan';
        }
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-center text-gray-900 mb-8">Checkout</h1>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Checkout Form -->
        <div class="lg:col-span-2">
            <form method="POST" action="" class="space-y-8">
                <!-- Shipping Information -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-shipping-fast text-sm"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900">Informasi Pengiriman</h2>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Penerima
                            </label>
                            <input type="text" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-md bg-gray-50 text-gray-500 cursor-not-allowed" 
                                   id="nama" 
                                   value="<?php echo htmlspecialchars($user['nama']); ?>" 
                                   readonly>
                        </div>

                        <div>
                            <label for="alamat" class="block text-sm font-medium text-gray-700 mb-2">
                                Alamat Pengiriman
                            </label>
                            <textarea class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" 
                                    id="alamat" 
                                    name="alamat" 
                                    rows="3" 
                                    placeholder="Masukkan alamat lengkap pengiriman"
                                    required><?php echo htmlspecialchars($user['alamat']); ?></textarea>
                        </div>

                        <div>
                            <label for="tanggal_kirim" class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Pengiriman
                            </label>
                            <input type="date" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" 
                                   id="tanggal_kirim" 
                                   name="tanggal_kirim" 
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                   required>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-credit-card text-sm"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900">Metode Pembayaran</h2>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-primary hover:bg-primary/5 transition-colors cursor-pointer">
                            <label class="flex items-center cursor-pointer">
                                <input class="w-4 h-4 text-primary border-gray-300 focus:ring-primary" 
                                       type="radio" 
                                       name="metode_pembayaran" 
                                       id="transfer" 
                                       value="transfer" 
                                       required>
                                <div class="ml-3 flex items-center">
                                    <i class="fas fa-university text-primary mr-3"></i>
                                    <div>
                                        <div class="font-medium text-gray-900">Transfer Bank</div>
                                        <div class="text-sm text-gray-500">BCA, BNI, BRI, Mandiri</div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div class="border border-gray-200 rounded-lg p-4 hover:border-primary hover:bg-primary/5 transition-colors cursor-pointer">
                            <label class="flex items-center cursor-pointer">
                                <input class="w-4 h-4 text-primary border-gray-300 focus:ring-primary" 
                                       type="radio" 
                                       name="metode_pembayaran" 
                                       id="qris" 
                                       value="qris">
                                <div class="ml-3 flex items-center">
                                    <i class="fas fa-qrcode text-primary mr-3"></i>
                                    <div>
                                        <div class="font-medium text-gray-900">QRIS</div>
                                        <div class="text-sm text-gray-500">Scan QR untuk pembayaran</div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div class="border border-gray-200 rounded-lg p-4 hover:border-primary hover:bg-primary/5 transition-colors cursor-pointer">
                            <label class="flex items-center cursor-pointer">
                                <input class="w-4 h-4 text-primary border-gray-300 focus:ring-primary" 
                                       type="radio" 
                                       name="metode_pembayaran" 
                                       id="ewallet" 
                                       value="ewallet">
                                <div class="ml-3 flex items-center">
                                    <i class="fas fa-mobile-alt text-primary mr-3"></i>
                                    <div>
                                        <div class="font-medium text-gray-900">E-Wallet</div>
                                        <div class="text-sm text-gray-500">Dana, OVO, GoPay, ShopeePay</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full bg-primary hover:bg-primary/90 text-white font-medium py-4 px-6 rounded-md transition-colors duration-200 flex items-center justify-center space-x-2 text-lg">
                    <i class="fas fa-check-circle"></i>
                    <span>Buat Pesanan</span>
                </button>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg p-6 sticky top-24">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Ringkasan Pesanan</h2>
                
                <div class="space-y-4 mb-6">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="flex justify-between items-start pb-4 border-b border-gray-100 last:border-b-0">
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900 mb-1">
                                    <?php echo htmlspecialchars($item['nama']); ?>
                                </h3>
                                <p class="text-sm text-gray-500">
                                    <?php echo $item['quantity']; ?> x Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">
                                    Rp <?php echo number_format($item['harga'] * $item['quantity'], 0, ',', '.'); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="border-t pt-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-600">Subtotal:</span>
                        <span class="font-medium text-gray-900">
                            Rp <?php echo number_format($total, 0, ',', '.'); ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-gray-600">Ongkos Kirim:</span>
                        <span class="font-medium text-gray-900">Gratis</span>
                    </div>
                    <div class="flex justify-between items-center text-lg font-bold">
                        <span class="text-gray-900">Total:</span>
                        <span class="text-primary">
                            Rp <?php echo number_format($total, 0, ',', '.'); ?>
                        </span>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-2"></i>
                        <div class="text-sm text-blue-700">
                            <p class="font-medium mb-1">Informasi Penting:</p>
                            <ul class="space-y-1 text-xs">
                                <li>• Pesanan akan diproses setelah pembayaran dikonfirmasi</li>
                                <li>• Pengiriman gratis untuk wilayah Jabodetabek</li>
                                <li>• Estimasi pengiriman 1-3 hari kerja</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
