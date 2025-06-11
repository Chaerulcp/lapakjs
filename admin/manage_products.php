<?php
require_once '../includes/db.php';
require_once 'includes/admin_header.php';

// Initialize products array
$products = [];

try {
    // Handle product deletion
    if (isset($_POST['delete_product'])) {
        $delete_stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        if ($delete_stmt->execute([$_POST['product_id']])) {
            $success_message = "Produk berhasil dihapus.";
        } else {
            $error_message = "Gagal menghapus produk.";
        }
    }

    // Fetch all products with search and filter
    $where_conditions = [];
    $params = [];

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $where_conditions[] = "(nama LIKE ? OR deskripsi LIKE ?)";
        $search_term = "%" . $_GET['search'] . "%";
        $params[] = $search_term;
        $params[] = $search_term;
    }

    if (isset($_GET['kategori']) && !empty($_GET['kategori'])) {
        $where_conditions[] = "kategori = ?";
        $params[] = $_GET['kategori'];
    }

    if (isset($_GET['stok']) && $_GET['stok'] !== '') {
        switch ($_GET['stok']) {
            case 'habis':
                $where_conditions[] = "stok = 0";
                break;
            case 'menipis':
                $where_conditions[] = "stok > 0 AND stok <= 10";
                break;
            case 'tersedia':
                $where_conditions[] = "stok > 10";
                break;
        }
    }

    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

    $query = "SELECT * FROM products $where_clause ORDER BY created_at DESC";
    // Fetch products
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Get unique categories for filter
    $cat_stmt = $pdo->query("SELECT DISTINCT kategori FROM products ORDER BY kategori");
    $categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error_message = "Terjadi kesalahan saat memuat data produk: " . $e->getMessage();
}

// Ensure $products is always defined
if (!isset($products)) {
    $products = [];
}
?>

<!-- Page header -->
<div class="bg-white shadow rounded-lg mb-6">
    <div class="px-6 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Manajemen Produk</h1>
        <a href="add_product.php" class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
            <i class="fas fa-plus mr-2"></i> Tambah Produk
        </a>
    </div>
</div>

<?php if (isset($success_message)): ?>
    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
        <?php echo $success_message; ?>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
        <?php echo $error_message; ?>
    </div>
<?php endif; ?>

<!-- Filter section -->
<div class="bg-white shadow rounded-lg mb-6">
    <div class="p-6">
        <form action="" method="GET" class="space-y-4 sm:space-y-0 sm:flex sm:items-center sm:gap-4">
            <div class="flex-1">
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           placeholder="Cari produk..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <div class="w-full sm:w-48">
                <select name="kategori" 
                        class="w-full border border-gray-300 rounded-lg py-2 px-4 focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>"
                            <?php echo (isset($_GET['kategori']) && $_GET['kategori'] === $category) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="w-full sm:w-48">
                <select name="stok" 
                        class="w-full border border-gray-300 rounded-lg py-2 px-4 focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="">Status Stok</option>
                    <option value="tersedia" <?php echo (isset($_GET['stok']) && $_GET['stok'] === 'tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                    <option value="menipis" <?php echo (isset($_GET['stok']) && $_GET['stok'] === 'menipis') ? 'selected' : ''; ?>>Menipis</option>
                    <option value="habis" <?php echo (isset($_GET['stok']) && $_GET['stok'] === 'habis') ? 'selected' : ''; ?>>Habis</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    Filter
                </button>
                <a href="manage_products.php" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Products grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <?php foreach ($products as $product): ?>
        <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow group">
            <div class="relative h-48">
                <img src="<?php
                            $foto = $product['foto'];
                            if (!empty($foto) && file_exists("../" . $foto)) {
                                echo "../" . htmlspecialchars($foto);
                            }
                            ?>"
                    alt="<?php echo htmlspecialchars($product['nama']); ?>"
                    class="w-full h-full object-cover">
                
                <!-- Actions overlay -->
                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 flex items-center justify-center gap-2 transition-opacity">
                    <a href="edit_product.php?id=<?php echo $product['id']; ?>"
                       class="p-2 bg-white rounded-full text-gray-700 hover:text-primary transition-colors">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" class="inline">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" 
                                name="delete_product" 
                                class="p-2 bg-white rounded-full text-gray-700 hover:text-red-500 transition-colors"
                                onclick="return confirm('Apakah Anda yakin ingin menghapus <?php echo htmlspecialchars($product['nama']); ?>?');">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="p-4">
                <h3 class="font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($product['nama']); ?></h3>
                
                <div class="flex items-center justify-between mb-3">
                    <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">
                        <?php echo htmlspecialchars($product['kategori']); ?>
                    </span>
                    <?php
                    $stockClass = $product['stok'] > 10 ? 'bg-green-100 text-green-800' : 
                                ($product['stok'] > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                    $stockText = $product['stok'] > 10 ? 'Tersedia' : 
                               ($product['stok'] > 0 ? 'Stok Menipis' : 'Habis');
                    ?>
                    <span class="px-2 py-1 rounded-full text-xs <?php echo $stockClass; ?>">
                        <?php echo $stockText; ?>
                    </span>
                </div>

                <div class="space-y-2 border-t pt-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Harga Normal:</span>
                        <span class="font-semibold text-gray-900">
                            Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Harga Reseller:</span>
                        <span class="font-semibold text-primary">
                            Rp <?php echo number_format($product['harga_reseller'], 0, ',', '.'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
