<?php
require_once '../includes/db.php';
require_once 'includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nama = $_POST['nama'];
        $deskripsi = $_POST['deskripsi'];
        $harga = $_POST['harga'];
        $harga_reseller = $_POST['harga_reseller'];
        $stok = $_POST['stok'];
        $kategori = $_POST['kategori'];

        // Handle file upload
        $foto = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../public/uploads/products/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($file_extension, $allowed_extensions)) {
                throw new Exception('Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau WEBP.');
            }

            $file_name = uniqid() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;

            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $file_path)) {
                throw new Exception('Gagal mengupload file.');
            }

            $foto = 'public/uploads/products/' . $file_name;
        }

        $stmt = $pdo->prepare("
            INSERT INTO products (nama, deskripsi, harga, harga_reseller, stok, kategori, foto)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$nama, $deskripsi, $harga, $harga_reseller, $stok, $kategori, $foto]);
        
        $success_message = "Produk berhasil ditambahkan.";
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        $error_message = $e->getMessage();
    }
}
?>

<!-- Page header -->
<div class="bg-white shadow rounded-lg mb-6">
    <div class="px-6 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Tambah Produk Baru</h1>
        <a href="manage_products.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
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
    <form action="" method="POST" enctype="multipart/form-data" class="product-form">
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8">
                <!-- Product Information -->
                <div class="space-y-6">
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6">Informasi Produk</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Produk</label>
                                <input type="text" 
                                       id="nama" 
                                       name="nama" 
                                       required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                       placeholder="Masukkan nama produk">
                            </div>

                            <div>
                                <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                                <textarea id="deskripsi" 
                                          name="deskripsi" 
                                          required 
                                          rows="5"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                          placeholder="Masukkan deskripsi produk"></textarea>
                            </div>

                            <div>
                                <label for="kategori" class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                                <select id="kategori" 
                                        name="kategori" 
                                        required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                    <option value="">Pilih Kategori</option>
                                    <option value="Sambal">Sambal</option>
                                    <option value="Bumbu">Bumbu</option>
                                    <option value="Paket">Paket</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Price & Stock -->
                <div class="space-y-6">
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6">Harga & Stok</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="harga" class="block text-sm font-medium text-gray-700 mb-2">Harga Normal (Rp)</label>
                                <div class="flex">
                                    <span class="inline-flex items-center px-3 text-sm text-gray-500 bg-gray-200 border border-r-0 border-gray-300 rounded-l-lg">
                                        Rp
                                    </span>
                                    <input type="number" 
                                           id="harga" 
                                           name="harga" 
                                           required 
                                           min="0" 
                                           step="1000"
                                           class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                           placeholder="25000">
                                </div>
                            </div>

                            <div>
                                <label for="harga_reseller" class="block text-sm font-medium text-gray-700 mb-2">Harga Reseller (Rp)</label>
                                <div class="flex">
                                    <span class="inline-flex items-center px-3 text-sm text-gray-500 bg-gray-200 border border-r-0 border-gray-300 rounded-l-lg">
                                        Rp
                                    </span>
                                    <input type="number" 
                                           id="harga_reseller" 
                                           name="harga_reseller" 
                                           required 
                                           min="0" 
                                           step="1000"
                                           class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                           placeholder="20000">
                                </div>
                            </div>

                            <div>
                                <label for="stok" class="block text-sm font-medium text-gray-700 mb-2">Stok</label>
                                <input type="number" 
                                       id="stok" 
                                       name="stok" 
                                       required 
                                       min="0"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                       placeholder="Masukkan jumlah stok">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Photo -->
                <div class="space-y-6">
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6">Foto Produk</h2>
                        
                        <div>
                            <label for="foto" class="block text-sm font-medium text-gray-700 mb-2">Upload Foto</label>
                            <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary transition-colors">
                                <input type="file" 
                                       id="foto" 
                                       name="foto" 
                                       accept="image/*" 
                                       required
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                
                                <div id="upload-placeholder" class="text-gray-500">
                                    <i class="fas fa-cloud-upload-alt text-4xl mb-4 text-gray-400"></i>
                                    <div class="text-lg font-medium mb-2">Klik atau seret foto ke sini</div>
                                    <div class="text-sm">Format: JPG, JPEG, PNG, WEBP (Max. 5MB)</div>
                                </div>
                                
                                <img id="preview-image" 
                                     src="#" 
                                     alt="Preview" 
                                     class="hidden max-w-full max-h-48 mx-auto rounded-lg">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
            <div class="flex justify-end space-x-3">
                <button type="reset" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-undo mr-2"></i> Reset Form
                </button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                    <i class="fas fa-save mr-2"></i> Simpan Produk
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Image preview
document.getElementById('foto').addEventListener('change', function(e) {
    const file = this.files[0];
    const preview = document.getElementById('preview-image');
    const placeholder = document.getElementById('upload-placeholder');
    
    if (file) {
        // Check file size
        if (file.size > 5 * 1024 * 1024) {
            showNotification('Ukuran file terlalu besar. Maksimal 5MB.', 'error');
            this.value = '';
            return;
        }

        // Check file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            showNotification('Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau WEBP.', 'error');
            this.value = '';
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

// Form validation
document.querySelector('.product-form').addEventListener('submit', function(e) {
    const harga = parseInt(document.getElementById('harga').value);
    const hargaReseller = parseInt(document.getElementById('harga_reseller').value);

    if (hargaReseller >= harga) {
        e.preventDefault();
        showNotification('Harga reseller harus lebih kecil dari harga normal.', 'error');
    }
});

// Reset form handler
document.querySelector('button[type="reset"]').addEventListener('click', function() {
    const preview = document.getElementById('preview-image');
    const placeholder = document.getElementById('upload-placeholder');
    
    preview.classList.add('hidden');
    placeholder.classList.remove('hidden');
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
