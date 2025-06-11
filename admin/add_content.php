<?php
require_once '../includes/db.php';
require_once 'includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $judul = $_POST['judul'];
        $isi = $_POST['isi'];
        $penulis = $_POST['penulis'];
        $tanggal = $_POST['tanggal'];
        $video_url = $_POST['video_url'];

        // Handle image upload
        $gambar = '';
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

            $gambar = '/public/uploads/contents/' . $file_name;
        }

        $stmt = $pdo->prepare("
            INSERT INTO contents (judul, isi, penulis, tanggal, gambar, video)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$judul, $isi, $penulis, $tanggal, $gambar, $video_url]);
        
        $success_message = "Konten berhasil ditambahkan.";
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        $error_message = $e->getMessage();
    }
}
?>

<!-- Page header -->
<div class="bg-white shadow rounded-lg mb-6">
    <div class="px-6 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Tambah Konten Edukasi</h1>
        <a href="manage_contents.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
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

<!-- Content form -->
<div class="bg-white shadow rounded-lg">
    <form action="" method="POST" enctype="multipart/form-data" class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main content section -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Title -->
                <div>
                    <label for="judul" class="block text-sm font-medium text-gray-700 mb-2">Judul Konten</label>
                    <input type="text" id="judul" name="judul" required 
                           class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                           placeholder="Masukkan judul konten">
                </div>

                <!-- Content editor -->
                <div>
                    <label for="isi" class="block text-sm font-medium text-gray-700 mb-2">Isi Konten</label>
                    <div class="border border-gray-300 rounded-lg overflow-hidden">
                        <!-- Editor toolbar -->
                        <div class="bg-gray-50 border-b border-gray-300 p-2 flex flex-wrap gap-2">
                            <button type="button" onclick="formatText('bold')" 
                                    class="p-2 hover:bg-gray-200 rounded transition-colors">
                                <i class="fas fa-bold"></i>
                            </button>
                            <button type="button" onclick="formatText('italic')" 
                                    class="p-2 hover:bg-gray-200 rounded transition-colors">
                                <i class="fas fa-italic"></i>
                            </button>
                            <button type="button" onclick="formatText('underline')" 
                                    class="p-2 hover:bg-gray-200 rounded transition-colors">
                                <i class="fas fa-underline"></i>
                            </button>
                            <button type="button" onclick="insertList('ul')" 
                                    class="p-2 hover:bg-gray-200 rounded transition-colors">
                                <i class="fas fa-list-ul"></i>
                            </button>
                            <button type="button" onclick="insertList('ol')" 
                                    class="p-2 hover:bg-gray-200 rounded transition-colors">
                                <i class="fas fa-list-ol"></i>
                            </button>
                            <button type="button" onclick="insertLink()" 
                                    class="p-2 hover:bg-gray-200 rounded transition-colors">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
                        <!-- Editor area -->
                        <div id="editor" class="min-h-[400px] p-4 focus:outline-none" contenteditable="true"></div>
                        <textarea id="isi" name="isi" class="hidden"></textarea> <!-- Removed required attribute -->
                    </div>
                </div>
            </div>

            <!-- Sidebar section -->
            <div class="space-y-6">
                <!-- Author -->
                <div>
                    <label for="penulis" class="block text-sm font-medium text-gray-700 mb-2">Penulis</label>
                    <input type="text" id="penulis" name="penulis" required 
                           class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                           placeholder="Nama penulis"
                           value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>">
                </div>

                <!-- Publication date -->
                <div>
                    <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Publikasi</label>
                    <input type="date" id="tanggal" name="tanggal" required 
                           class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                           value="<?php echo date('Y-m-d'); ?>">
                </div>

                <!-- Image upload -->
                <div>
                    <label for="gambar" class="block text-sm font-medium text-gray-700 mb-2">Gambar Utama</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary transition-colors">
                        <input type="file" id="gambar" name="gambar" accept="image/*" class="hidden">
                        <img id="preview-image" src="#" alt="Preview" class="max-h-48 mx-auto mb-4 rounded-lg hidden">
                        <div class="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                            <p class="text-sm text-gray-600">Klik atau seret gambar ke sini</p>
                            <p class="text-xs text-gray-500 mt-2">Format: JPG, JPEG, PNG, WEBP (Max. 5MB)</p>
                        </div>
                    </div>
                </div>

                <!-- Video URL -->
                <div>
                    <label for="video_url" class="block text-sm font-medium text-gray-700 mb-2">URL Video (Opsional)</label>
                    <input type="url" id="video_url" name="video_url" 
                           class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                           placeholder="https://youtube.com/...">
                    <p class="mt-1 text-xs text-gray-500">Masukkan URL video YouTube atau video lainnya</p>
                </div>

                <!-- Form actions -->
                <div class="flex flex-col gap-3">
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        <i class="fas fa-save mr-2"></i> Publikasikan
                    </button>
                    <button type="button" onclick="saveDraft()" 
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-save mr-2"></i> Simpan Draft
                    </button>
                </div>
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
document.querySelector('form').addEventListener('submit', function(e) {
    const editorContent = document.getElementById('editor').innerHTML.trim();
    if (editorContent === '' || editorContent === '<br>') { // Check for empty content or just a line break
        e.preventDefault(); // Prevent form submission
        alert('Isi Konten tidak boleh kosong.'); // Alert the user
        return;
    }
    // Copy editor content to hidden textarea
    document.getElementById('isi').value = editorContent;
});

// Image upload functionality
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('gambar');
    const uploadArea = fileInput.parentElement;
    const preview = document.getElementById('preview-image');
    const placeholder = document.querySelector('.upload-placeholder');

    // Click to upload
    uploadArea.addEventListener('click', function() {
        fileInput.click();
    });

    // Drag and drop
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('border-primary');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('border-primary');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('border-primary');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelect(files[0]);
        }
    });

    // File input change
    fileInput.addEventListener('change', function(e) {
        if (this.files[0]) {
            handleFileSelect(this.files[0]);
        }
    });

    function handleFileSelect(file) {
        // Check file size
        if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 5MB.');
            fileInput.value = '';
            return;
        }

        // Check file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau WEBP.');
            fileInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        }
        reader.readAsDataURL(file);
    }
});

// Save draft functionality
function saveDraft() {
    const content = {
        judul: document.getElementById('judul').value,
        isi: document.getElementById('editor').innerHTML,
        penulis: document.getElementById('penulis').value,
        tanggal: document.getElementById('tanggal').value,
        video_url: document.getElementById('video_url').value
    };
    
    localStorage.setItem('content_draft', JSON.stringify(content));
    alert('Draft berhasil disimpan!');
}

// Load draft if exists
window.addEventListener('load', function() {
    const draft = localStorage.getItem('content_draft');
    if (draft) {
        const content = JSON.parse(draft);
        if (confirm('Ditemukan draft yang tersimpan. Muat draft?')) {
            document.getElementById('judul').value = content.judul;
            document.getElementById('editor').innerHTML = content.isi;
            document.getElementById('penulis').value = content.penulis;
            document.getElementById('tanggal').value = content.tanggal;
            document.getElementById('video_url').value = content.video_url;
        }
        localStorage.removeItem('content_draft');
    }
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
