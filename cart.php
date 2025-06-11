<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Handle quantity updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_quantity'])) {
        $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

        if ($product_id && $quantity > 0 && isset($_SESSION['cart'][$product_id])) {
            // Check stock availability
            try {
                $stmt = $pdo->prepare("SELECT stok FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch();

                if ($quantity <= $product['stok']) {
                    $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                    $success = 'Keranjang berhasil diperbarui';
                } else {
                    $error = 'Jumlah melebihi stok yang tersedia';
                }
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'Terjadi kesalahan saat memperbarui keranjang';
            }
        }
    } elseif (isset($_POST['remove_item'])) {
        $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        if ($product_id && isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            $success = 'Produk berhasil dihapus dari keranjang';
        }
    }
}

// Calculate total
$total = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['harga'] * $item['quantity'];
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-center text-gray-900 mb-8">Keranjang Belanja</h1>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['cart'])): ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b">
                        <h2 class="text-lg font-semibold text-gray-900">Produk dalam Keranjang</h2>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                            <div class="p-6">
                                <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-4 sm:space-y-0 sm:space-x-4">
                                    <!-- Product Image -->
                                    <div class="flex-shrink-0">
                                        <img src="<?php echo htmlspecialchars($item['foto']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['nama']); ?>"
                                             class="w-20 h-20 sm:w-24 sm:h-24 object-cover rounded-lg">
                                    </div>
                                    
                                    <!-- Product Details -->
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">
                                            <?php echo htmlspecialchars($item['nama']); ?>
                                        </h3>
                                        <p class="text-xl font-bold text-primary mb-3">
                                            Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?>
                                        </p>
                                        
                                        <!-- Quantity Control -->
                                        <form method="POST" action="" class="flex items-center space-x-3 mb-3">
                                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                            <label for="quantity-<?php echo $product_id; ?>" class="text-sm font-medium text-gray-700">
                                                Jumlah:
                                            </label>
                                            <input type="number" 
                                                   id="quantity-<?php echo $product_id; ?>" 
                                                   name="quantity" 
                                                   value="<?php echo $item['quantity']; ?>" 
                                                   min="1" 
                                                   class="w-20 px-3 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <button type="submit" 
                                                    name="update_quantity" 
                                                    class="px-3 py-1 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-md transition-colors">
                                                <i class="fas fa-sync-alt mr-1"></i>
                                                Update
                                            </button>
                                        </form>
                                        
                                        <!-- Remove Button -->
                                        <form method="POST" action="" class="inline">
                                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                            <button type="submit" 
                                                    name="remove_item" 
                                                    class="text-red-600 hover:text-red-800 text-sm font-medium transition-colors"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                                <i class="fas fa-trash mr-1"></i>
                                                Hapus dari Keranjang
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <!-- Subtotal -->
                                    <div class="text-right">
                                        <p class="text-sm text-gray-500 mb-1">Subtotal:</p>
                                        <p class="text-xl font-bold text-primary">
                                            Rp <?php echo number_format($item['harga'] * $item['quantity'], 0, ',', '.'); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg p-6 sticky top-24">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Ringkasan Belanja</h2>
                    
                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between text-gray-600">
                            <span>Jumlah Produk:</span>
                            <span><?php echo count($_SESSION['cart']); ?> item</span>
                        </div>
                        <div class="border-t pt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-900">Total:</span>
                                <span class="text-2xl font-bold text-primary">
                                    Rp <?php echo number_format($total, 0, ',', '.'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <a href="checkout.php" 
                       class="w-full bg-primary hover:bg-primary/90 text-white font-medium py-3 px-6 rounded-md transition-colors duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-credit-card"></i>
                        <span>Lanjut ke Pembayaran</span>
                    </a>
                    
                    <a href="products.php" 
                       class="w-full mt-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-3 px-6 rounded-md transition-colors duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Lanjut Belanja</span>
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-16">
            <div class="bg-white rounded-lg shadow-lg p-12 max-w-md mx-auto">
                <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-6"></i>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Keranjang Kosong</h2>
                <p class="text-gray-600 mb-8">Keranjang belanja Anda masih kosong. Yuk, mulai berbelanja!</p>
                <a href="products.php" 
                   class="inline-flex items-center px-6 py-3 bg-primary hover:bg-primary/90 text-white font-medium rounded-md transition-colors duration-200 space-x-2">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Mulai Belanja</span>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
