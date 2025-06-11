<?php
require_once '../includes/db.php';
require_once 'includes/admin_header.php';

try {
    // Handle content deletion
    if (isset($_POST['delete_content'])) {
        $content_id = $_POST['content_id'];
        
        $stmt = $pdo->prepare("DELETE FROM contents WHERE id = ?");
        $stmt->execute([$content_id]);
        
        $success_message = "Konten berhasil dihapus.";
    }

    // Fetch contents with filters
    $where_conditions = [];
    $params = [];

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $where_conditions[] = "(judul LIKE ? OR isi LIKE ? OR penulis LIKE ?)";
        $search_term = "%" . $_GET['search'] . "%";
        $params = array_merge($params, [$search_term, $search_term, $search_term]);
    }

    if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
        $where_conditions[] = "DATE(tanggal) >= ?";
        $params[] = $_GET['date_from'];
    }

    if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
        $where_conditions[] = "DATE(tanggal) <= ?";
        $params[] = $_GET['date_to'];
    }

    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Get content statistics
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_contents,
            COUNT(CASE WHEN video IS NOT NULL AND video != '' THEN 1 END) as video_contents,
            COUNT(CASE WHEN gambar IS NOT NULL AND gambar != '' THEN 1 END) as image_contents,
            MAX(tanggal) as latest_content
        FROM contents
    ");
    $stats = $stmt->fetch();

    // Fetch contents
    $query = "SELECT * FROM contents $where_clause ORDER BY tanggal DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $contents = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $error_message = "Terjadi kesalahan saat memuat data konten.";
}
?>

<!-- Page header -->
<div class="bg-white shadow rounded-lg mb-6">
    <div class="px-6 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Manajemen Konten Edukasi</h1>
        <a href="add_content.php" class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
            <i class="fas fa-plus mr-2"></i> Tambah Konten
        </a>
    </div>
</div>

<!-- Alerts -->
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

<!-- Stats grid -->
<div class="grid grid-cols-1 gap-6 mb-6 sm:grid-cols-2 lg:grid-cols-4">
    <!-- Total Konten -->
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-100 text-red-600">
                <i class="fas fa-newspaper text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Konten</p>
                <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total_contents']); ?></p>
            </div>
        </div>
    </div>

    <!-- Konten Video -->
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <i class="fas fa-video text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Konten Video</p>
                <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['video_contents']); ?></p>
            </div>
        </div>
    </div>

    <!-- Konten Gambar -->
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="fas fa-image text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Konten Gambar</p>
                <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['image_contents']); ?></p>
            </div>
        </div>
    </div>

    <!-- Update Terakhir -->
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Update Terakhir</p>
                <p class="text-2xl font-semibold text-gray-900">
                    <?php echo $stats['latest_content'] ? date('d/m/Y', strtotime($stats['latest_content'])) : '-'; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Filter section -->
<div class="bg-white shadow rounded-lg mb-6">
    <div class="p-6">
        <form action="" method="GET" class="space-y-4 sm:space-y-0 sm:flex sm:items-center sm:gap-4">
            <div class="flex-1">
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           placeholder="Cari konten..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <div class="w-full sm:w-48">
                <input type="date" 
                       name="date_from" 
                       value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>"
                       class="w-full border border-gray-300 rounded-lg py-2 px-4 focus:ring-2 focus:ring-primary focus:border-primary">
            </div>

            <div class="w-full sm:w-48">
                <input type="date" 
                       name="date_to"
                       value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>"
                       class="w-full border border-gray-300 rounded-lg py-2 px-4 focus:ring-2 focus:ring-primary focus:border-primary">
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    Filter
                </button>
                <a href="manage_contents.php" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Contents grid -->
<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    <?php foreach ($contents as $content): ?>
        <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
            <?php if ($content['gambar']): ?>
                <div class="relative h-48 overflow-hidden">
                    <img src="<?php echo htmlspecialchars($content['gambar']); ?>" 
                         alt="<?php echo htmlspecialchars($content['judul']); ?>"
                         class="w-full h-full object-cover">
                    <?php if ($content['video']): ?>
                        <div class="absolute top-4 right-4 bg-black bg-opacity-50 text-white w-8 h-8 rounded-full flex items-center justify-center">
                            <i class="fas fa-play-circle"></i>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3 line-clamp-2">
                    <?php echo htmlspecialchars($content['judul']); ?>
                </h3>
                
                <div class="flex items-center gap-4 mb-3 text-sm text-gray-600">
                    <span class="flex items-center gap-1">
                        <i class="fas fa-user"></i> 
                        <?php echo htmlspecialchars($content['penulis']); ?>
                    </span>
                    <span class="flex items-center gap-1">
                        <i class="fas fa-calendar"></i>
                        <?php echo date('d/m/Y', strtotime($content['tanggal'])); ?>
                    </span>
                </div>
                
                <p class="text-gray-600 text-sm line-clamp-3 mb-4">
                    <?php echo substr(strip_tags($content['isi']), 0, 150) . '...'; ?>
                </p>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 flex gap-2">
                <a href="edit_content.php?id=<?php echo $content['id']; ?>" 
                   class="flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 text-gray-600 hover:bg-blue-500 hover:text-white transition-colors"
                   title="Edit">
                    <i class="fas fa-edit"></i>
                </a>
                <form action="" method="POST" class="inline"
                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus konten ini?');">
                    <input type="hidden" name="content_id" value="<?php echo $content['id']; ?>">
                    <button type="submit" name="delete_content" 
                            class="flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 text-gray-600 hover:bg-red-500 hover:text-white transition-colors"
                            title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
                <a href="../content_detail.php?id=<?php echo $content['id']; ?>" 
                   class="flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 text-gray-600 hover:bg-green-500 hover:text-white transition-colors"
                   title="Lihat" target="_blank">
                    <i class="fas fa-eye"></i>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
