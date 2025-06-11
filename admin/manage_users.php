<?php
require_once '../includes/db.php';
require_once 'includes/admin_header.php';

$error = '';
$success = '';

// Handle user status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_UNSAFE_RAW);
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $user_id])) {
            $success = 'Status pengguna berhasil diperbarui';
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $error = 'Gagal memperbarui status pengguna';
    }
}

// Fetch users
try {
    $stmt = $pdo->query("
        SELECT u.*, 
            COUNT(DISTINCT o.id) as total_orders,
            SUM(o.total) as total_spent
        FROM users u 
        LEFT JOIN orders o ON u.id = o.user_id
        WHERE u.role != 'admin'
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'Gagal memuat daftar pengguna';
}
?>

<!-- Page header -->
<div class="bg-white shadow rounded-lg mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-semibold text-gray-900">Kelola Pengguna</h1>
            <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm font-medium">
                <?php echo count($users); ?> Pengguna
            </span>
        </div>
    </div>
</div>

<!-- Alerts -->
<?php if ($error || $success): ?>
    <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Users table -->
<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pesanan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pembelian</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            #<?php echo $user['id']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo htmlspecialchars($user['nama']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo htmlspecialchars($user['email']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $roleClass = $user['role'] === 'reseller' ? 
                                'bg-blue-100 text-blue-800' : 
                                'bg-purple-100 text-purple-800';
                            ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $roleClass; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form method="POST" action="" class="inline-flex">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="update_status" value="1">
                                <select name="status" 
                                        onchange="this.form.submit()"
                                        class="text-sm border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                                    <option value="active" <?php echo $user['status'] == 'active' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="inactive" <?php echo $user['status'] == 'inactive' ? 'selected' : ''; ?>>Nonaktif</option>
                                </select>
                            </form>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo $user['total_orders'] ?: 0; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Rp <?php echo number_format($user['total_spent'] ?: 0, 0, ',', '.'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors view-details" 
                                    data-user-id="<?php echo $user['id']; ?>">
                                <i class="fas fa-eye mr-1"></i> Detail
                            </button>
                        </td>
                    </tr>
                    <!-- User Details Row -->
                    <tr>
                        <td colspan="8" class="hidden bg-gray-50 p-6" id="details-<?php echo $user['id']; ?>">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Alamat -->
                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <h6 class="text-sm font-medium text-gray-600 mb-2">Alamat</h6>
                                    <p class="text-gray-900"><?php echo nl2br(htmlspecialchars($user['alamat'])); ?></p>
                                </div>
                                <!-- No. HP -->
                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <h6 class="text-sm font-medium text-gray-600 mb-2">No. HP</h6>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($user['no_hp']); ?></p>
                                </div>
                                <!-- Tanggal Registrasi -->
                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <h6 class="text-sm font-medium text-gray-600 mb-2">Tanggal Registrasi</h6>
                                    <p class="text-gray-900"><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></p>
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end">
                                <button class="delete-user-btn inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                                        data-user-id="<?php echo $user['id']; ?>"
                                        data-user-name="<?php echo htmlspecialchars($user['nama']); ?>">
                                    <i class="fas fa-trash-alt mr-2"></i> Hapus Akun
                                </button>
                            </div>
                            <!-- Recent Orders -->
                            <?php 
                            $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                            $stmt->execute([$user['id']]);
                            $recent_orders = $stmt->fetchAll();
                            if ($recent_orders): ?>
                                <div class="mt-6">
                                    <h6 class="text-sm font-medium text-gray-600 mb-4">Riwayat Pesanan Terakhir</h6>
                                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                <?php foreach ($recent_orders as $order): ?>
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            #<?php echo $order['id']; ?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            Rp <?php echo number_format($order['total'], 0, ',', '.'); ?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <?php
                                                            $statusClasses = [
                                                                'menunggu' => 'bg-yellow-100 text-yellow-800',
                                                                'diproses' => 'bg-blue-100 text-blue-800',
                                                                'dikirim' => 'bg-indigo-100 text-indigo-800',
                                                                'selesai' => 'bg-green-100 text-green-800',
                                                                'dibatalkan' => 'bg-red-100 text-red-800'
                                                            ];
                                                            $statusClass = $statusClasses[$order['status']] ?? 'bg-gray-100 text-gray-800';
                                                            ?>
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                                                <?php echo ucfirst($order['status']); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Toggle user details
    document.querySelectorAll('.view-details').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const details = document.getElementById(`details-${userId}`);
            
            // Close other open details
            document.querySelectorAll('[id^="details-"]').forEach(detail => {
                if (detail !== details) {
                    detail.classList.add('hidden');
                }
            });

            // Toggle current details with animation
            if (details.classList.contains('hidden')) {
                details.classList.remove('hidden');
                details.classList.add('animate-fade-in');
            } else {
                details.classList.add('hidden');
                details.classList.remove('animate-fade-in');
            }
        });
    });

    // Auto-hide alerts after 3 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.display = 'none';
        }, 3000);
    });

    // Handle delete user
    document.querySelectorAll('.delete-user-btn').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');
            
            showDeleteConfirmation(userId, userName);
        });
    });

    function showDeleteConfirmation(userId, userName) {
        const modal = document.getElementById('deleteConfirmationModal');
        const message = document.getElementById('deleteConfirmationMessage');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        const cancelBtn = document.getElementById('cancelDeleteBtn');

        message.innerHTML = `Apakah Anda yakin ingin menghapus akun <strong>${userName}</strong> (ID: ${userId})? Semua data terkait (pesanan, pembayaran, dll.) akan dihapus secara permanen.`;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex'); // Use flex to center content

        // Remove previous event listeners to prevent multiple bindings
        confirmBtn.onclick = null;
        cancelBtn.onclick = null;

        confirmBtn.onclick = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            
            fetch('delete_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `user_id=${userId}`
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => { throw new Error(text) });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Gagal menghapus akun: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                alert('Terjadi kesalahan saat menghapus akun: ' + error.message);
            });
        };

        cancelBtn.onclick = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        // Close modal if clicked outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });
    }
</script>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmationModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-50 items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl p-6 max-w-sm mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Konfirmasi Penghapusan Akun</h3>
            <button type="button" class="text-gray-400 hover:text-gray-600" onclick="document.getElementById('deleteConfirmationModal').classList.add('hidden'); document.getElementById('deleteConfirmationModal').classList.remove('flex');">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mb-6">
            <p id="deleteConfirmationMessage" class="text-sm text-gray-700"></p>
        </div>
        <div class="flex justify-end space-x-3">
            <button id="cancelDeleteBtn" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                Batal
            </button>
            <button id="confirmDeleteBtn" type="button" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Ya, Hapus
            </button>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
