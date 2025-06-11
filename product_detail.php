<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$error = '';
$success = '';
$product = null;
$testimonials = [];

// Get product ID from URL
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$product_id) {
    header('Location: index.php');
    exit();
}

try {
    // Fetch product details
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: index.php');
        exit();
    }

    // Fetch testimonials for this product
    $stmt = $pdo->prepare("
        SELECT t.*, u.nama as user_nama 
        FROM testimonials t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.product_id = ? 
        ORDER BY t.tanggal DESC
    ");
    $stmt->execute([$product_id]);
    $testimonials = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'Terjadi kesalahan saat memuat produk.';
}

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    
    if ($quantity <= 0 || $quantity > $product['stok']) {
        $error = 'Jumlah pesanan tidak valid';
    } else {
        // Initialize cart session if it doesn't exist
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Add/update cart
        $cart_item = [
            'product_id' => $product_id,
            'nama' => $product['nama'],
            'harga' => ($_SESSION['user_role'] === 'reseller' ? $product['harga_reseller'] : $product['harga']),
            'quantity' => $quantity,
            'foto' => $product['foto']
        ];

        $_SESSION['cart'][$product_id] = $cart_item;
        $success = 'Produk berhasil ditambahkan ke keranjang';
    }
}
?>

<div class="container mx-auto px-4 py-12 md:py-16 lg:py-20">
    <div class="mb-10">
        <a href="products.php" class="inline-flex items-center bg-gray-200 text-gray-800 px-6 py-3 rounded-full shadow-md hover:bg-gray-300 hover:text-gray-900 transition-all duration-300 text-lg font-semibold transform hover:-translate-x-1">
            <i class="fas fa-arrow-left mr-3 text-xl"></i>
            <span>Kembali ke Daftar Produk</span>
        </a>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 animate-fade-in-up">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 animate-fade-in-up">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 mb-16 items-start">
        <!-- Product Image -->
        <div class="relative p-8 bg-white rounded-3xl shadow-2xl transform transition-all duration-500 hover:scale-[1.01] hover:shadow-3xl animate-scale-in border border-gray-100 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-100 to-white opacity-20 rounded-3xl z-0"></div>
            <img src="<?php echo htmlspecialchars($product['foto']); ?>" 
                 alt="<?php echo htmlspecialchars($product['nama']); ?>"
                 class="w-full h-auto rounded-2xl object-cover max-h-[550px] relative z-10 transform transition-transform duration-300 hover:scale-105">
        </div>

        <!-- Product Info -->
        <div class="space-y-8 p-4 lg:p-0 animate-fade-in-up delay-200">
            <h1 class="text-4xl lg:text-6xl font-extrabold text-gray-900 leading-tight">
                <?php echo htmlspecialchars($product['nama']); ?>
            </h1>
            
            <div class="flex items-baseline space-x-4">
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'reseller'): ?>
                    <div class="text-4xl lg:text-5xl font-bold text-primary-600">
                        Rp <?php echo number_format($product['harga_reseller'], 0, ',', '.'); ?>
                    </div>
                    <span class="inline-block bg-blue-600 text-white text-base px-5 py-2 rounded-full font-semibold shadow-lg">
                        Harga Reseller
                    </span>
                <?php else: ?>
                    <div class="text-4xl lg:text-5xl font-bold text-primary-600">
                        Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="prose prose-lg text-gray-700 max-w-none">
                <p class="leading-relaxed text-lg">
                    <?php echo nl2br(htmlspecialchars($product['deskripsi'])); ?>
                </p>
            </div>

            <div class="flex items-center space-x-4">
                <span class="text-gray-800 font-semibold text-xl">Stok:</span>
                <span class="bg-green-600 text-white px-5 py-2 rounded-full text-base font-semibold shadow-lg">
                    <?php echo $product['stok']; ?> tersedia
                </span>
            </div>

            <?php if ($product['stok'] > 0): ?>
                <form method="POST" action="" class="space-y-6">
                    <div>
                        <label for="quantity" class="block text-lg font-medium text-gray-800 mb-3">
                            Jumlah:
                        </label>
                        <div class="flex items-center space-x-4">
                            <input type="number" 
                                   id="quantity" 
                                   name="quantity" 
                                   class="w-36 px-5 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-3 focus:ring-primary-500 focus:border-transparent text-xl shadow-sm" 
                                   value="1" 
                                   min="1" 
                                   max="<?php echo $product['stok']; ?>" 
                                   required>
                            <span class="text-gray-600 text-base">
                                Maksimal <?php echo $product['stok']; ?>
                            </span>
                        </div>
                    </div>
                    <button type="submit" 
                            name="add_to_cart" 
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-5 px-8 rounded-xl transition-all duration-300 flex items-center justify-center space-x-3 text-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 focus:outline-none focus:ring-4 focus:ring-red-300 focus:ring-opacity-75 uppercase tracking-wider">
                        <i class="fas fa-shopping-cart text-3xl"></i>
                        <span>Tambah ke Keranjang</span>
                    </button>
                </form>
            <?php else: ?>
                <div class="bg-red-600 text-white px-8 py-5 rounded-xl shadow-lg flex items-center space-x-4">
                    <i class="fas fa-exclamation-circle text-3xl"></i>
                    <span class="font-bold text-xl">Stok Habis</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Testimonials Section -->
    <section class="mt-24 bg-gradient-to-br from-gray-100 to-gray-200 py-20 rounded-3xl shadow-inner-lg">
        <h2 class="text-5xl font-extrabold text-center text-gray-900 mb-16 leading-tight">Ulasan Pembeli</h2>
        <?php if ($testimonials): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 px-6">
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100 transform transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl flex flex-col justify-between animate-fade-in-up">
                        <div>
                            <div class="flex items-center mb-5">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star text-2xl <?php echo $i <= $testimonial['rating'] ? 'text-yellow-500' : 'text-gray-300'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-gray-700 mb-6 leading-relaxed text-lg italic">
                                "<?php echo htmlspecialchars($testimonial['isi']); ?>"
                            </p>
                        </div>
                        <div class="border-t border-gray-200 pt-6">
                            <p class="font-bold text-gray-900 text-xl">
                                <?php echo htmlspecialchars($testimonial['user_nama']); ?>
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                <?php echo date('d M Y', strtotime($testimonial['tanggal'])); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20">
                <i class="fas fa-comments text-7xl text-gray-300 mb-8"></i>
                <p class="text-gray-600 text-2xl font-semibold">Belum ada ulasan untuk produk ini</p>
                <p class="text-gray-500 text-lg mt-4">Jadilah yang pertama memberikan ulasan dan bantu pembeli lain!</p>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>
