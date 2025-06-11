<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

try {
    // Fetch all educational content with pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 6;
    $offset = ($page - 1) * $limit;

    // Get total content count
    $stmt = $pdo->query("SELECT COUNT(*) FROM contents");
    $total_contents = $stmt->fetchColumn();
    $total_pages = ceil($total_contents / $limit);

    // Fetch contents for current page
    $stmt = $pdo->prepare("SELECT * FROM contents ORDER BY tanggal DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    $contents = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'Terjadi kesalahan saat memuat konten.';
}
?>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Edukasi Kuliner</h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Pelajari lebih lanjut tentang dunia kuliner dan bisnis sambal melalui artikel-artikel edukatif kami
        </p>
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

    <!-- Content Grid -->
    <?php if (empty($contents)): ?>
        <div class="text-center py-12">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-book-open text-3xl text-gray-400"></i>
            </div>
            <h3 class="text-xl font-medium text-gray-900 mb-2">Belum ada konten tersedia</h3>
            <p class="text-gray-500">Konten edukasi akan segera hadir</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            <?php foreach ($contents as $content): ?>
                <article class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:-translate-y-1 transition-all duration-300 hover:shadow-xl">
                    <?php if ($content['gambar']): ?>
                        <div class="relative h-48 overflow-hidden">
                            <img 
                                src="<?php echo htmlspecialchars($content['gambar']); ?>" 
                                alt="<?php echo htmlspecialchars($content['judul']); ?>"
                                class="w-full h-full object-cover"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                        </div>
                    <?php else: ?>
                        <div class="h-48 bg-gradient-to-br from-primary/10 to-primary/20 flex items-center justify-center">
                            <i class="fas fa-book-open text-4xl text-primary/60"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-3 line-clamp-2">
                            <?php echo htmlspecialchars($content['judul']); ?>
                        </h2>
                        
                        <div class="flex items-center justify-between text-sm text-gray-500 mb-3">
                            <div class="flex items-center">
                                <i class="fas fa-user mr-2"></i>
                                <span><?php echo htmlspecialchars($content['penulis']); ?></span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-calendar mr-2"></i>
                                <span><?php echo date('d M Y', strtotime($content['tanggal'])); ?></span>
                            </div>
                        </div>
                        
                        <p class="text-gray-600 mb-4 line-clamp-3">
                            <?php echo htmlspecialchars(substr(strip_tags($content['isi']), 0, 150)) . '...'; ?>
                        </p>
                        
                        <a 
                            href="content_detail.php?id=<?php echo $content['id']; ?>" 
                            class="inline-flex items-center text-primary hover:text-primary/80 font-medium transition-colors"
                        >
                            Baca Selengkapnya
                            <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="flex justify-center">
            <nav class="flex items-center space-x-2">
                <?php if ($page > 1): ?>
                    <a 
                        href="?page=<?php echo $page - 1; ?>" 
                        class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                    >
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a 
                        href="?page=<?php echo $i; ?>" 
                        class="px-3 py-2 text-sm font-medium <?php echo $i === $page ? 'text-white bg-primary border-primary' : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50'; ?> border rounded-md"
                    >
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a 
                        href="?page=<?php echo $page + 1; ?>" 
                        class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                    >
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>

    <!-- Call to Action -->
    <div class="mt-16 bg-gradient-to-r from-primary to-primary/80 rounded-lg p-8 text-center text-white">
        <h3 class="text-2xl font-bold mb-4">Ingin Berbagi Pengetahuan?</h3>
        <p class="text-lg mb-6 opacity-90">
            Jika Anda memiliki tips atau resep menarik seputar kuliner, jangan ragu untuk berbagi dengan kami
        </p>
        <a 
            href="mailto:info@sambalmamaana.com" 
            class="inline-flex items-center px-6 py-3 bg-white text-primary font-medium rounded-md hover:bg-gray-100 transition-colors"
        >
            <i class="fas fa-envelope mr-2"></i>
            Hubungi Kami
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
