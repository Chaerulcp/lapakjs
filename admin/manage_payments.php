<?php
require_once '../includes/db.php';
require_once 'includes/admin_header.php';

// Check for admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Initialize CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

try {
    // Fetch payments with filters using prepared statements
    $where_conditions = [];
    $params = [];

    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (in_array($status, ['menunggu', 'dikonfirmasi', 'gagal'])) {
            $where_conditions[] = "p.status = ?";
            $params[] = $status;
        }
    }

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search_term = "%" . filter_input(INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS) . "%";
        $where_conditions[] = "(o.id LIKE ? OR u.nama LIKE ? OR u.no_hp LIKE ?)";
        $params = array_merge($params, [$search_term, $search_term, $search_term]);
    }

    if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
        $date_from = filter_input(INPUT_GET, 'date_from', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (strtotime($date_from)) {
            $where_conditions[] = "DATE(p.created_at) >= ?";
            $params[] = $date_from;
        }
    }

    if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
        $date_to = filter_input(INPUT_GET, 'date_to', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (strtotime($date_to)) {
            $where_conditions[] = "DATE(p.created_at) <= ?";
            $params[] = $date_to;
        }
    }

    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Initialize default stats
    $stats = [
        'total_payments'    => 0,
        'pending_payments'  => 0,
        'verified_payments' => 0,
        'rejected_payments' => 0,
        'total_verified'    => 0
    ];

    // Get payment statistics
    try {
        $stats_stmt = $pdo->prepare("
            SELECT 
                COALESCE(COUNT(*), 0) as total_payments,
                COALESCE(SUM(CASE WHEN p.status = 'menunggu' THEN 1 ELSE 0 END), 0) as pending_payments,
                COALESCE(SUM(CASE WHEN p.status = 'dikonfirmasi' THEN 1 ELSE 0 END), 0) as verified_payments,
                COALESCE(SUM(CASE WHEN p.status = 'gagal' THEN 1 ELSE 0 END), 0) as rejected_payments,
                COALESCE(SUM(CASE WHEN p.status = 'dikonfirmasi' THEN o.total ELSE 0 END), 0) as total_verified
            FROM payments p
            LEFT JOIN orders o ON p.order_id = o.id
        ");
        
        if ($stats_stmt->execute()) {
            $result = $stats_stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && is_array($result)) {
                foreach ($result as $key => $value) {
                    $stats[$key] = is_numeric($value) ? (int)$value : 0;
                }
            }
        }
    } catch (PDOException $e) {
        error_log("Error fetching payment statistics: " . $e->getMessage());
    }

    // Initialize payments array
    $payments = [];

    // Fetch payments with related data
    try {
        $query = "
            SELECT 
                p.*, 
                o.total as amount, 
                u.nama as customer_name, 
                u.no_hp,
                GROUP_CONCAT(pr.nama SEPARATOR ', ') as products,
                v.nama as verifier_name
            FROM payments p
            JOIN orders o ON p.order_id = o.id
            JOIN users u ON o.user_id = u.id
            JOIN order_items oi ON o.id = oi.order_id
            JOIN products pr ON oi.product_id = pr.id
            LEFT JOIN users v ON p.verified_by = v.id
            $where_clause
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ";

        $stmt = $pdo->prepare($query);
        if ($stmt->execute($params)) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($result && is_array($result)) {
                $payments = $result;
            }
        }
    } catch (PDOException $e) {
        error_log("Error fetching payments: " . $e->getMessage());
    }

} catch (Exception $e) {
    error_log("Error in manage_payments.php: " . $e->getMessage());
    $error_message = "Terjadi kesalahan saat memuat data pembayaran. Silakan coba lagi nanti.";
}
?>

<!-- Page header -->
<div class="bg-white shadow rounded-lg mb-6">
    <div class="px-6 py-4">
        <h1 class="text-2xl font-semibold text-gray-900">Verifikasi Pembayaran</h1>
    </div>
</div>

<div id="alert-container"></div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="stat-card hover-shadow">
        <div class="stat-icon bg-red-100 text-red-600">
            <i class="fas fa-credit-card"></i>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Total Pembayaran</h3>
            <div class="text-2xl font-semibold text-gray-900" data-stat="total_payments"><?php echo number_format($stats['total_payments']); ?></div>
        </div>
    </div>
    
    <div class="stat-card hover-shadow">
        <div class="stat-icon bg-yellow-100 text-yellow-600">
            <i class="fas fa-clock"></i>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Menunggu Verifikasi</h3>
            <div class="text-2xl font-semibold text-gray-900" data-stat="pending_payments"><?php echo number_format($stats['pending_payments']); ?></div>
        </div>
    </div>
    
    <div class="stat-card hover-shadow">
        <div class="stat-icon bg-green-100 text-green-600">
            <i class="fas fa-check-circle"></i>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Terverifikasi</h3>
            <div class="text-2xl font-semibold text-gray-900" data-stat="verified_payments"><?php echo number_format($stats['verified_payments']); ?></div>
        </div>
    </div>
    
    <div class="stat-card hover-shadow">
        <div class="stat-icon bg-blue-100 text-blue-600">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Total Terverifikasi</h3>
            <div class="text-2xl font-semibold text-gray-900" data-stat="total_verified">Rp <?php echo number_format($stats['total_verified'], 0, ',', '.'); ?></div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <form action="" method="GET" class="space-y-4 sm:space-y-0 sm:flex sm:items-center sm:gap-4">
        <div class="flex-1">
            <div class="relative">
                <input type="text" 
                       name="search" 
                       placeholder="Cari pembayaran..." 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>

        <div class="w-full sm:w-48">
            <select name="status" 
                    class="w-full border border-gray-300 rounded-lg py-2 px-4 focus:ring-2 focus:ring-primary focus:border-primary">
                <option value="">Semua Status</option>
                <option value="menunggu" <?php echo (isset($_GET['status']) && $_GET['status'] === 'menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                <option value="dikonfirmasi" <?php echo (isset($_GET['status']) && $_GET['status'] === 'dikonfirmasi') ? 'selected' : ''; ?>>Terverifikasi</option>
                <option value="gagal" <?php echo (isset($_GET['status']) && $_GET['status'] === 'gagal') ? 'selected' : ''; ?>>Ditolak</option>
            </select>
        </div>

        <div class="w-full sm:w-48">
            <input type="date" 
                   name="date_from" 
                   class="w-full border border-gray-300 rounded-lg py-2 px-4 focus:ring-2 focus:ring-primary focus:border-primary"
                   value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>"
                   placeholder="Dari Tanggal">
        </div>

        <div class="w-full sm:w-48">
            <input type="date" 
                   name="date_to" 
                   class="w-full border border-gray-300 rounded-lg py-2 px-4 focus:ring-2 focus:ring-primary focus:border-primary"
                   value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>"
                   placeholder="Sampai Tanggal">
        </div>

        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors">
                Filter
            </button>
            <a href="manage_payments.php" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Payments Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
    <?php if (!empty($payments)): ?>
        <?php foreach ($payments as $payment): ?>
        <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow" data-payment-id="<?php echo htmlspecialchars($payment['id']); ?>">
            <!-- Payment Header -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Order #<?php echo htmlspecialchars($payment['order_id']); ?></h3>
                        <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                            <span class="flex items-center">
                                <i class="fas fa-calendar mr-1"></i>
                                <?php echo date('d/m/Y H:i', strtotime($payment['created_at'])); ?>
                            </span>
                            <span class="px-2 py-1 rounded-full text-xs font-medium <?php 
                                switch($payment['status']) {
                                    case 'menunggu': echo 'bg-yellow-100 text-yellow-800'; break;
                                    case 'dikonfirmasi': echo 'bg-green-100 text-green-800'; break;
                                    case 'gagal': echo 'bg-red-100 text-red-800'; break;
                                    default: echo 'bg-gray-100 text-gray-800';
                                }
                            ?>">
                                <?php 
                                $status_labels = [
                                    'menunggu' => 'Menunggu',
                                    'dikonfirmasi' => 'Terverifikasi',
                                    'gagal' => 'Ditolak'
                                ];
                                echo htmlspecialchars($status_labels[$payment['status']] ?? 'Unknown');
                                ?>
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xl font-bold text-gray-900">Rp <?php echo number_format($payment['amount'] ?? 0, 0, ',', '.'); ?></div>
                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($payment['metode']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="p-6 border-b border-gray-200">
                <div class="space-y-3">
                    <div>
                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($payment['customer_name']); ?></div>
                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($payment['no_hp']); ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 line-clamp-2">
                            <?php echo htmlspecialchars($payment['products']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Proof -->
            <?php if ($payment['bukti_transfer']): ?>
                <?php
                $bukti_transfer = htmlspecialchars($payment['bukti_transfer']);
                $image_path = '../public/uploads/payments/' . $bukti_transfer;
                if (file_exists($image_path)): 
                ?>
                <div class="p-6 border-b border-gray-200">
                    <div class="text-center">
                        <img src="<?php echo $image_path; ?>" 
                             alt="Bukti Transfer untuk Order #<?php echo htmlspecialchars($payment['order_id']); ?>" 
                             onclick="viewImage(this.src)"
                             loading="lazy"
                             class="max-w-full max-h-48 mx-auto rounded-lg cursor-pointer hover:opacity-90 transition-opacity">
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Payment Actions or Verification Info -->
            <?php if ($payment['status'] === 'menunggu'): ?>
                <div class="p-6">
                    <div class="verify-form" data-payment-id="<?php echo htmlspecialchars($payment['id']); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="payment_id" value="<?php echo htmlspecialchars($payment['id']); ?>">
                        
                        <div class="mb-4">
                            <label for="notes-<?php echo htmlspecialchars($payment['id']); ?>" class="block text-sm font-medium text-gray-700 mb-2">Catatan:</label>
                            <textarea id="notes-<?php echo htmlspecialchars($payment['id']); ?>" 
                                      name="notes" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" 
                                      rows="2" 
                                      maxlength="255"
                                      placeholder="Tambahkan catatan (opsional)"></textarea>
                        </div>

                        <div class="flex gap-3">
                            <button type="button" 
                                    class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors verify-btn" 
                                    data-status="dikonfirmasi">
                                <i class="fas fa-check mr-2"></i> Verifikasi
                            </button>
                            <button type="button" 
                                    class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors reject-btn" 
                                    data-status="gagal">
                                <i class="fas fa-times mr-2"></i> Tolak
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="p-6 bg-gray-50">
                    <?php if ($payment['verified_by']): ?>
                        <div class="text-sm text-gray-600 mb-2">
                            <?php echo $payment['status'] === 'dikonfirmasi' ? 'Diverifikasi' : 'Ditolak'; ?> oleh: <?php echo htmlspecialchars($payment['verifier_name']); ?> pada 
                            <?php echo date('d/m/Y H:i', strtotime($payment['verified_at'])); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($payment['notes']): ?>
                        <div class="text-sm text-gray-600">
                            <strong>Catatan:</strong> <?php echo htmlspecialchars($payment['notes']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-span-full">
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <i class="fas fa-info-circle text-4xl text-gray-300 mb-4"></i>
                <div class="text-lg font-medium text-gray-900">Tidak ada data pembayaran yang tersedia</div>
                <div class="text-sm text-gray-500">Pembayaran yang masuk akan muncul di sini</div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Image Preview Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden items-center justify-center p-4">
    <button class="absolute top-4 right-4 text-white text-4xl font-bold hover:text-gray-300 z-10" onclick="closeModal()">&times;</button>
    <img id="modalImage" src="" alt="Preview Bukti Transfer" class="max-w-full max-h-full rounded-lg">
    <div class="absolute bottom-4 left-0 right-0 text-center text-white text-lg">Preview Bukti Transfer</div>
</div>

<script>
// Payment verification handling
function handleVerification(form, status) {
    if (!form || !status) {
        console.error('Invalid form or status');
        return;
    }

    const csrfToken = form.querySelector('input[name="csrf_token"]');
    const paymentId = form.querySelector('input[name="payment_id"]');
    const notes = form.querySelector('textarea[name="notes"]');
    
    if (!csrfToken || !csrfToken.value) {
        showAlert('error', 'Error: CSRF token missing');
        return;
    }
    
    if (!paymentId || !paymentId.value) {
        showAlert('error', 'Error: Payment ID missing');
        return;
    }

    const action = status === 'dikonfirmasi' ? 'memverifikasi' : 'menolak';
    
    if (!confirm(`Apakah Anda yakin ingin ${action} pembayaran ini?`)) {
        return;
    }

    // Disable form elements during submission
    const buttons = form.querySelectorAll('button');
    const textareas = form.querySelectorAll('textarea');
    buttons.forEach(button => button.disabled = true);
    textareas.forEach(textarea => textarea.disabled = true);

    // Show loading state
    const paymentCard = form.closest('[data-payment-id]');
    if (paymentCard) {
        paymentCard.style.opacity = '0.7';
    }

    // Prepare form data
    const formData = new FormData();
    formData.append('csrf_token', csrfToken.value);
    formData.append('payment_id', paymentId.value);
    formData.append('status', status);
    formData.append('notes', notes ? notes.value : '');

    // Send AJAX request
    fetch('process_payment_verification.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update payment card status
            updatePaymentCard(data.payment_id, status, notes ? notes.value : '');
            
            // Show success message
            showAlert('success', data.message);
            
            // Update statistics
            updateStatistics();
        } else {
            throw new Error(data.message || 'Terjadi kesalahan saat memproses verifikasi');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', error.message || 'Terjadi kesalahan saat memproses verifikasi');
        
        // Re-enable form elements on error
        buttons.forEach(button => button.disabled = false);
        textareas.forEach(textarea => textarea.disabled = false);
        if (paymentCard) {
            paymentCard.style.opacity = '1';
        }
    });
}

// Function to update payment card after verification
function updatePaymentCard(paymentId, status, notes) {
    const paymentCard = document.querySelector(`[data-payment-id="${paymentId}"]`);
    if (!paymentCard) {
        console.error('Payment card not found:', paymentId);
        return;
    }

    try {
        // Update status badge
        const statusBadge = paymentCard.querySelector('.px-2.py-1.rounded-full');
        if (statusBadge) {
            const statusClasses = {
                'dikonfirmasi': 'bg-green-100 text-green-800',
                'gagal': 'bg-red-100 text-red-800'
            };
            statusBadge.className = `px-2 py-1 rounded-full text-xs font-medium ${statusClasses[status]}`;
            const statusText = status === 'dikonfirmasi' ? 'Terverifikasi' : 'Ditolak';
            statusBadge.textContent = statusText;
        }

        // Replace verification form with verification info
        const verificationForm = paymentCard.querySelector('.verify-form').parentElement;
        if (verificationForm) {
            const currentDate = new Date().toLocaleString('id-ID', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
            verificationForm.innerHTML = `
                <div class="p-6 bg-gray-50">
                    <div class="text-sm text-gray-600 mb-2">
                        ${status === 'dikonfirmasi' ? 'Diverifikasi' : 'Ditolak'} oleh: Admin pada 
                        ${currentDate}
                    </div>
                    ${notes ? `<div class="text-sm text-gray-600"><strong>Catatan:</strong> ${notes}</div>` : ''}
                </div>
            `;
        }

        // Update card opacity
        paymentCard.style.opacity = '1';

        // Update payment statistics
        updateStatistics();
    } catch (error) {
        console.error('Error updating payment card:', error);
        showAlert('error', 'Terjadi kesalahan saat memperbarui tampilan. Silakan muat ulang halaman.');
    }
}

// Function to update statistics
function updateStatistics() {
    fetch('get_payment_stats.php')
        .then(response => response.json())
        .then(stats => {
            // Update each stat card
            Object.keys(stats).forEach(key => {
                const statElement = document.querySelector(`[data-stat="${key}"]`);
                if (statElement) {
                    if (key === 'total_verified') {
                        statElement.textContent = `Rp ${Number(stats[key]).toLocaleString('id-ID')}`;
                    } else {
                        statElement.textContent = Number(stats[key]).toLocaleString('id-ID');
                    }
                }
            });
        })
        .catch(error => console.error('Error updating statistics:', error));
}

// Function to show alert messages
function showAlert(type, message) {
    const alertContainer = document.getElementById('alert-container');
    
    // Remove existing alerts
    alertContainer.innerHTML = '';
    
    const alertDiv = document.createElement('div');
    const alertClasses = type === 'success' 
        ? 'bg-green-100 border border-green-400 text-green-700' 
        : 'bg-red-100 border border-red-400 text-red-700';
    
    alertDiv.className = `mb-6 p-4 rounded-lg ${alertClasses}`;
    alertDiv.innerHTML = `
        <span>${message}</span>
        <button type="button" class="float-right text-xl leading-none" onclick="this.parentElement.remove()">×</button>
    `;

    alertContainer.appendChild(alertDiv);
    
    // Auto-remove alert after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.remove();
        }
    }, 5000);
}

// Image preview functionality
function viewImage(src) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    
    if (!src || typeof src !== 'string') {
        console.error('Invalid image source');
        return;
    }
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modalImg.src = src;
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('imageModal');
    if (!modal) return;
    
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = 'auto';
    document.getElementById('modalImage').src = '';
}

document.addEventListener('DOMContentLoaded', function() {
    try {
        // Add click handlers to verification buttons
        document.querySelectorAll('.verify-form').forEach(form => {
            const verifyButton = form.querySelector('.verify-btn');
            const rejectButton = form.querySelector('.reject-btn');

            if (verifyButton) {
                verifyButton.onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    handleVerification(form, 'dikonfirmasi');
                };
            }

            if (rejectButton) {
                rejectButton.onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    handleVerification(form, 'gagal');
                };
            }
        });

        // Modal event listeners
        const imageModal = document.getElementById('imageModal');
        
        if (imageModal) {
            imageModal.addEventListener('click', function(e) {
                if (e.target === this) closeModal();
            });
        }

        // Keyboard event for modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !document.getElementById('imageModal').classList.contains('hidden')) {
                closeModal();
            }
        });

    } catch (error) {
        console.error('Error initializing event handlers:', error);
        showAlert('error', 'Terjadi kesalahan saat menginisialisasi halaman. Silakan muat ulang halaman.');
    }
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
