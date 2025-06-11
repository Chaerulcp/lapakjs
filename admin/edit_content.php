<?php
require_once '../includes/db.php';
require_once 'includes/admin_header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage_contents.php');
    exit();
}

$content_id = $_GET['id'];

try {
    // Fetch content data
    $stmt = $pdo->prepare("SELECT * FROM contents WHERE id = ?");
    $stmt->execute([$content_id]);
    $content = $stmt->fetch();

    if (!$content) {
        header('Location: manage_contents.php');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $judul = $_POST['judul'];
        $isi = $_POST['isi'];
        $penulis = $_POST['penulis'];
        $tanggal = $_POST['tanggal'];
        $video_url = $_POST['video_url'];
        $gambar = $content['gambar']; // Keep existing image by default

        // Handle new image upload if provided
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../public/uploads/contents/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($file_extension, $allowed_extensions)) {
                throw new Exception('Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau WEBP.');
            }

            $file_name = uniqid() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;

            if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $file_path)) {
                throw new Exception('Gagal mengupload file.');
            }

            // Delete old image if exists
            if ($content['gambar'] && file_exists('..' . $content['gambar'])) {
                unlink('..' . $content['gambar']);
            }

            $gambar = '/public/uploads/contents/' . $file_name;
        }

        $stmt = $pdo->prepare("
            UPDATE contents 
            SET judul = ?, isi = ?, penulis = ?, tanggal = ?, gambar = ?, video = ?
            WHERE id = ?
        ");
        
        $stmt->execute([$judul, $isi, $penulis, $tanggal, $gambar, $video_url, $content_id]);
        
        $success_message = "Konten berhasil diperbarui.";
        
        // Refresh content data
        $stmt = $pdo->prepare("SELECT * FROM contents WHERE id = ?");
        $stmt->execute([$content_id]);
        $content = $stmt->fetch();
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $error_message = $e->getMessage();
}
?>

<!-- Page header -->
<div class="bg-white shadow rounded-lg mb-6">
    <div class="px-6 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Konten Edukasi</h1>
        <a href="manage_contents.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
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

<!-- Form container -->
<div class="bg-white shadow rounded-lg">
    <form action="" method="POST" enctype="multipart/form-data" class="content-form">
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8">
                <!-- Main Content Section -->
                <div class="space-y-6 lg:col-span-2">
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6">Detail Konten</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="judul" class="block text-sm font-medium text-gray-700 mb-2">Judul Konten</label>
                                <input type="text" id="judul" name="judul" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                       placeholder="Masukkan judul konten"
                                       value="<?php echo htmlspecialchars($content['judul']); ?>">
                            </div>

                            <div>
                                <label for="isi" class="block text-sm font-medium text-gray-700 mb-2">Isi Konten</label>
                                <div class="editor-toolbar flex flex-wrap gap-2 p-2 bg-gray-200 border border-gray-300 rounded-t-lg">
                                    <button type="button" onclick="formatText('bold')" class="toolbar-btn p-2 bg-white rounded-md hover:bg-gray-100 text-gray-700">
                                        <i class="fas fa-bold"></i>
                                    </button>
                                    <button type="button" onclick="formatText('italic')" class="toolbar-btn p-2 bg-white rounded-md hover:bg-gray-100 text-gray-700">
                                        <i class="fas fa-italic"></i>
                                    </button>
                                    <button type="button" onclick="formatText('underline')" class="toolbar-btn p-2 bg-white rounded-md hover:bg-gray-100 text-gray-700">
                                        <i class="fas fa-underline"></i>
                                    </button>
                                    <button type="button" onclick="insertList('ul')" class="toolbar-btn p-2 bg-white rounded-md hover:bg-gray-100 text-gray-700">
                                        <i class="fas fa-list-ul"></i>
                                    </button>
                                    <button type="button" onclick="insertList('ol')" class="toolbar-btn p-2 bg-white rounded-md hover:bg-gray-100 text-gray-700">
                                        <i class="fas fa-list-ol"></i>
                                    </button>
                                    <button type="button" onclick="insertLink()" class="toolbar-btn p-2 bg-white rounded-md hover:bg-gray-100 text-gray-700">
                                        <i class="fas fa-link"></i>
                                    </button>
                                </div>
                                <div id="editor" class="content-editor min-h-[400px] p-4 border border-gray-300 rounded-b-lg bg-white overflow-y-auto" contenteditable="true">
                                    <?php echo $content['isi']; ?>
                                </div>
                                <textarea id="isi" name="isi" class="hidden"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Section -->
                <div class="space-y-6">
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6">Informasi Tambahan</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="penulis" class="block text-sm font-medium text-gray-700 mb-2">Penulis</label>
                                <input type="text" id="penulis" name="penulis" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                       placeholder="Nama penulis"
                                       value="<?php echo htmlspecialchars($content['penulis']); ?>">
                            </div>

                            <div>
                                <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Publikasi</label>
                                <input type="date" id="tanggal" name="tanggal" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" 
                                       value="<?php echo date('Y-m-d', strtotime($content['tanggal'])); ?>">
                            </div>

                            <div>
                                <label for="gambar" class="block text-sm font-medium text-gray-700 mb-2">Gambar Utama</label>
                                <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary transition-colors">
                                    <input type="file" id="gambar" name="gambar" accept="image/*" 
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    
                                    <div id="upload-placeholder-content" class="text-gray-500 <?php echo $content['gambar'] ? 'hidden' : ''; ?>">
                                        <i class="fas fa-cloud-upload-alt text-4xl mb-4 text-gray-400"></i>
                                        <div class="text-lg font-medium mb-2">Klik atau seret gambar ke sini</div>
                                        <div class="text-sm">Format: JPG, JPEG, PNG, WEBP (Max. 5MB)</div>
                                    </div>
                                    
                                    <div id="image-preview-wrapper-content" class="<?php echo $content['gambar'] ? '' : 'hidden'; ?>">
                                        <img id="preview-image" 
                                             src="<?php echo htmlspecialchars($content['gambar']); ?>" 
                                             alt="Preview" 
                                             class="max-w-full max-h-48 mx-auto rounded-lg mb-2">
                                        <div class="text-sm text-gray-600">Gambar Saat Ini</div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="video_url" class="block text-sm font-medium text-gray-700 mb-2">URL Video (Opsional)</label>
                                <input type="url" id="video_url" name="video_url" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                       placeholder="https://youtube.com/..."
                                       value="<?php echo htmlspecialchars($content['video']); ?>">
                                <p class="mt-1 text-sm text-gray-500">Masukkan URL video YouTube atau video lainnya</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
            <div class="flex justify-end space-x-3">
                <a href="manage_contents.php" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-times mr-2"></i> Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>


<script>
// Rich text editor functions
function formatText(command) {
    document.execCommand(command, false, null);
}

function insertList(type) {
    document.execCommand('insert' + type + 'List', false, null);
}

function insertLink() {
    const url = prompt('Masukkan URL:');
    if (url) {
        document.execCommand('createLink', false, url);
    }
}

// Form submission
document.querySelector('.content-form').addEventListener('submit', function(e) {
    // Copy editor content to hidden textarea
    document.getElementById('isi').value = document.getElementById('editor').innerHTML;
    console.log('Form submitted. Isi content:', document.getElementById('isi').value);
});

// Image preview
document.getElementById('gambar').addEventListener('change', function(e) {
    const file = this.files[0];
    const previewWrapper = document.getElementById('image-preview-wrapper-content');
    const previewImage = document.getElementById('preview-image');
    const placeholder = document.getElementById('upload-placeholder-content');
    
    if (file) {
        // Check file size
        if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 5MB.');
            this.value = '';
            return;
        }

        // Check file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau WEBP.');
            this.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewWrapper.classList.remove('hidden');
            placeholder.classList.add('hidden');
        }
        reader.readAsDataURL(file);
    } else {
        // If no file is selected, revert to original state (if no existing image)
        // This part needs to be dynamic based on initial content['gambar']
        // For now, we'll just hide the preview if no file is selected
        if (!previewImage.src || previewImage.src.includes('placeholder.jpg') || previewImage.src === window.location.href) {
            previewWrapper.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }
    }
});

// Initial check for image display on page load
document.addEventListener('DOMContentLoaded', function() {
    const previewImage = document.getElementById('preview-image');
    const previewWrapper = document.getElementById('image-preview-wrapper-content');
    const placeholder = document.getElementById('upload-placeholder-content');

    if (previewImage && previewImage.src && !previewImage.src.includes('placeholder.jpg') && previewImage.src !== window.location.href) {
        previewWrapper.classList.remove('hidden');
        placeholder.classList.add('hidden');
    } else {
        previewWrapper.classList.add('hidden');
        placeholder.classList.remove('hidden');
    }
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
