<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: content.php');
    exit;
}

try {
    // Fetch the content
    $stmt = $pdo->prepare("SELECT * FROM contents WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $content = $stmt->fetch();

    if (!$content) {
        header('Location: content.php');
        exit;
    }

    // Fetch related content
    $stmt = $pdo->prepare("SELECT * FROM contents WHERE id != ? ORDER BY tanggal DESC LIMIT 3");
    $stmt->execute([$_GET['id']]);
    $related_contents = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'Terjadi kesalahan saat memuat konten.';
}
?>

<div class="container">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php else: ?>
        <article class="content-article">
            <?php if ($content['gambar']): ?>
                <div class="content-hero">
                    <img src="<?php echo htmlspecialchars($content['gambar']); ?>" 
                         alt="<?php echo htmlspecialchars($content['judul']); ?>">
                </div>
            <?php endif; ?>

            <div class="content-header">
                <h1><?php echo htmlspecialchars($content['judul']); ?></h1>
                <div class="content-meta">
                    <span class="author">
                        <i class="fas fa-user"></i> 
                        <?php echo htmlspecialchars($content['penulis']); ?>
                    </span>
                    <span class="date">
                        <i class="fas fa-calendar"></i>
                        <?php echo date('d F Y', strtotime($content['tanggal'])); ?>
                    </span>
                </div>
            </div>

            <div class="content-body">
                <?php 
                // Convert line breaks to paragraphs
                $paragraphs = explode("\n", $content['isi']);
                foreach ($paragraphs as $paragraph) {
                    if (trim($paragraph)) {
                        echo '<p>' . htmlspecialchars($paragraph) . '</p>';
                    }
                }
                ?>
            </div>

            <?php if ($content['video']): ?>
                <div class="content-video">
                    <h3>Video Terkait</h3>
                    <div class="video-container">
                        <?php
                        $video_url = htmlspecialchars($content['video']);
                        $embed_code = '';

                        // Check if it's a full YouTube URL
                        if (preg_match('/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=|embed\/|v\/|)([\w-]{11})(?:[?&].*)?/', $video_url, $matches)) {
                            $video_id = $matches[1];
                            $embed_code = '<iframe src="https://www.youtube.com/embed/' . $video_id . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                        } else {
                            // Assume it's already an embed code or just echo it as is
                            $embed_code = $content['video'];
                        }
                        echo $embed_code;
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </article>

        <?php if ($related_contents): ?>
            <section class="related-content">
                <h2>Artikel Terkait</h2>
                <div class="related-grid">
                    <?php foreach ($related_contents as $related): ?>
                        <div class="related-card">
                            <?php if ($related['gambar']): ?>
                                <img src="<?php echo htmlspecialchars($related['gambar']); ?>" 
                                     alt="<?php echo htmlspecialchars($related['judul']); ?>">
                            <?php endif; ?>
                            <div class="related-info">
                                <h3><?php echo htmlspecialchars($related['judul']); ?></h3>
                                <p><?php echo htmlspecialchars(substr($related['isi'], 0, 100)) . '...'; ?></p>
                                <a href="content_detail.php?id=<?php echo $related['id']; ?>" 
                                   class="btn btn-secondary">Baca Selengkapnya</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.content-article {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 3rem;
}

.content-hero {
    width: 100%;
    height: 400px;
    overflow: hidden;
}

.content-hero img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.content-header {
    padding: 2rem;
    border-bottom: 1px solid #eee;
}

.content-header h1 {
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 1rem;
}

.content-meta {
    display: flex;
    gap: 2rem;
    color: #666;
}

.content-meta span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.content-body {
    padding: 2rem;
    line-height: 1.8;
}

.content-body p {
    margin-bottom: 1.5rem;
    color: #444;
}

.content-video {
    padding: 2rem;
    border-top: 1px solid #eee;
}

.content-video h3 {
    margin-bottom: 1rem;
    color: #333;
}

.video-container {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
}

.video-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.related-content {
    margin-top: 3rem;
}

.related-content h2 {
    font-size: 2rem;
    color: #333;
    margin-bottom: 2rem;
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

.related-card {
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.related-card:hover {
    transform: translateY(-5px);
}

.related-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.related-info {
    padding: 1.5rem;
}

.related-info h3 {
    font-size: 1.2rem;
    color: #333;
    margin-bottom: 1rem;
}

.related-info p {
    color: #666;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .content-header h1 {
        font-size: 2rem;
    }

    .related-grid {
        grid-template-columns: 1fr;
    }

    .content-hero {
        height: 250px;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
