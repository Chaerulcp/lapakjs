<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

try {
    // Fetch all products with pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 12;
    $offset = ($page - 1) * $limit;

    // Filter by category if provided
    $kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
    $search_query = isset($_GET['search_query']) ? $_GET['search_query'] : '';
    $where_clauses = [];
    $params = [];

    if (!empty($kategori)) {
        $where_clauses[] = 'kategori = ?';
        $params[] = $kategori;
    }

    if (!empty($search_query)) {
        $where_clauses[] = '(nama LIKE ? OR deskripsi LIKE ?)';
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
    }

    $where_clause = '';
    if (!empty($where_clauses)) {
        $where_clause = 'WHERE ' . implode(' AND ', $where_clauses);
    }

    // Get total products count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products $where_clause");
    $stmt->execute($params);
    $total_products = $stmt->fetchColumn();
    $total_pages = ceil($total_products / $limit);

    // Fetch products for current page
    // Re-prepare params for the main query, as LIMIT and OFFSET need to be at the end
    $main_query_params = $params;
    $main_query_params[] = $limit;
    $main_query_params[] = $offset;
    $stmt = $pdo->prepare("SELECT * FROM products $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute($main_query_params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'Terjadi kesalahan saat memuat produk.';
}
?>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Produk Sambal Mama Ana</h1>
        <p class="text-lg text-gray-600">Temukan berbagai varian sambal dan bumbu berkualitas</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="mb-6 p-4 rounded-md bg-red-50 border border-red-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Filters and Search -->
    <div class="mb-12">
        <div class="bg-white rounded-2xl shadow-xl p-8 md:p-10 border border-gray-100">
            <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 items-end">
                <div class="col-span-1 md:col-span-2 lg:col-span-2">
                    <label for="search_query" class="block text-lg font-semibold text-gray-800 mb-2">Cari Produk:</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            name="search_query" 
                            id="search_query" 
                            placeholder="Cari produk berdasarkan nama atau deskripsi..."
                            value="<?php echo htmlspecialchars($search_query); ?>"
                            class="w-full pl-12 pr-5 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-3 focus:ring-primary-500 focus:border-primary-500 text-lg shadow-sm"
                        >
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-xl"></i>
                    </div>
                </div>

                <div>
                    <label for="kategori" class="block text-lg font-semibold text-gray-800 mb-2">Filter Kategori:</label>
                    <div class="relative">
                        <select 
                            name="kategori" 
                            id="kategori"
                            class="w-full pl-5 pr-12 py-3 border border-gray-300 rounded-xl appearance-none focus:outline-none focus:ring-3 focus:ring-primary-500 focus:border-primary-500 text-lg shadow-sm bg-white"
                        >
                            <option value="">Semua Kategori</option>
                            <option value="Sambal" <?php echo $kategori === 'Sambal' ? 'selected' : ''; ?>>Sambal</option>
                            <option value="Bumbu" <?php echo $kategori === 'Bumbu' ? 'selected' : ''; ?>>Bumbu</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none"></i>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 md:col-span-3 lg:col-span-1">
                    <button 
                        type="submit" 
                        class="w-full sm:w-auto flex-grow bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-xl transition-all duration-300 font-bold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-red-300 focus:ring-opacity-75"
                    >
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                    <?php if (!empty($kategori) || !empty($search_query)): ?>
                        <a 
                            href="products.php" 
                            class="w-full sm:w-auto flex-grow bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-xl transition-colors duration-300 font-bold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-gray-300 focus:ring-opacity-75 text-center"
                        >
                            <i class="fas fa-times mr-2"></i>Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Grid -->
    <?php if (empty($products)): ?>
        <div class="text-center py-12">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-box-open text-3xl text-gray-400"></i>
            </div>
            <h3 class="text-xl font-medium text-gray-900 mb-2">Tidak ada produk ditemukan</h3>
            <p class="text-gray-500">Coba ubah filter atau kembali lagi nanti</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 mb-16">
            <?php foreach ($products as $product): ?>
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden transform hover:scale-[1.02] transition-all duration-300 hover:shadow-2xl flex flex-col border border-gray-100">
                    <div class="relative">
                        <img 
                            src="<?php echo htmlspecialchars($product['foto']); ?>" 
                            alt="<?php echo htmlspecialchars($product['nama']); ?>"
                            class="w-full h-56 object-cover"
                        >
                        <?php if ($product['stok'] <= 0): ?>
                            <div class="absolute inset-0 bg-black/70 flex items-center justify-center">
                                <span class="bg-red-600 text-white px-5 py-2 rounded-full text-base font-semibold shadow-md">
                                    Stok Habis
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-6 flex flex-col flex-grow">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2 line-clamp-2">
                            <?php echo htmlspecialchars($product['nama']); ?>
                        </h3>
                        <p class="text-gray-700 text-base mb-4 line-clamp-3 flex-grow">
                            <?php echo htmlspecialchars(substr($product['deskripsi'], 0, 120)) . '...'; ?>
                        </p>
                        
                        <div class="flex justify-between items-center mb-5">
                            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'reseller'): ?>
                                <div>
                            <span class="text-3xl font-extrabold text-red-600">
                                Rp <?php echo number_format($product['harga_reseller'], 0, ',', '.'); ?>
                            </span>
                                    <span class="text-sm bg-blue-100 text-blue-800 px-3 py-1 rounded-full ml-2 font-medium">
                                        Harga Reseller
                                    </span>
                                </div>
                            <?php else: ?>
                                <span class="text-3xl font-extrabold text-red-600">
                                    Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?>
                                </span>
                            <?php endif; ?>
                            <span class="text-base text-gray-600 font-medium">
                                Stok: <?php echo $product['stok']; ?>
                            </span>
                        </div>
                        
                        <div class="space-y-4 mt-auto">
                            <a 
                                href="product_detail.php?id=<?php echo $product['id']; ?>" 
                                class="block w-full text-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-colors duration-300 font-bold text-lg shadow-md hover:shadow-lg"
                            >
                                <i class="fas fa-eye mr-2"></i>Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="flex justify-center mt-16">
            <nav class="flex items-center space-x-3">
                <?php if ($page > 1): ?>
                    <a 
                        href="?page=<?php echo $page - 1; ?><?php echo !empty($kategori) ? '&kategori=' . urlencode($kategori) : ''; ?><?php echo !empty($search_query) ? '&search_query=' . urlencode($search_query) : ''; ?>" 
                        class="px-5 py-3 text-lg font-medium text-gray-700 bg-white border border-gray-300 rounded-xl shadow-sm hover:bg-gray-100 transition-colors duration-200"
                    >
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a 
                        href="?page=<?php echo $i; ?><?php echo !empty($kategori) ? '&kategori=' . urlencode($kategori) : ''; ?><?php echo !empty($search_query) ? '&search_query=' . urlencode($search_query) : ''; ?>" 
                        class="px-5 py-3 text-lg font-medium <?php echo $i === $page ? 'text-white bg-red-600 border-red-600 shadow-md' : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-100'; ?> border rounded-xl transition-colors duration-200"
                    >
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a 
                        href="?page=<?php echo $page + 1; ?><?php echo !empty($kategori) ? '&kategori=' . urlencode($kategori) : ''; ?><?php echo !empty($search_query) ? '&search_query=' . urlencode($search_query) : ''; ?>" 
                        class="px-5 py-3 text-lg font-medium text-gray-700 bg-white border border-gray-300 rounded-xl shadow-sm hover:bg-gray-100 transition-colors duration-200"
                    >
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
