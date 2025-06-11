<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Get order ID from URL
$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

if (!$order_id) {
    header('Location: index.php');
    exit();
}

try {
    // Get order and payment details
    $stmt = $pdo->prepare("
        SELECT o.*, p.status as payment_status, p.metode as payment_method 
        FROM orders o 
        JOIN payments p ON o.id = p.order_id 
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();

    if (!$order) {
        header('Location: index.php');
        exit();
    }

    // Handle payment proof upload
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['bukti_transfer'])) {
        $file = $_FILES['bukti_transfer'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowed_types)) {
            $error = 'File harus berupa gambar (JPG, JPEG, atau PNG)';
        } elseif ($file['size'] > $max_size) {
            $error = 'Ukuran file maksimal 5MB';
        } else {
            $filename = 'payment_proof_' . $order_id . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $upload_path = 'public/uploads/payments/' . $filename;

            // Create directory if it doesn't exist
            if (!file_exists('public/uploads/payments')) {
                mkdir('public/uploads/payments', 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Update payment record
                $stmt = $pdo->prepare("
                    UPDATE payments 
                    SET bukti_transfer = ?, status = 'menunggu' 
                    WHERE order_id = ?
                ");
                $stmt->execute([$filename, $order_id]);
                $success = 'Bukti pembayaran berhasil diunggah';
            } else {
                $error = 'Gagal mengunggah file';
            }
        }
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'Terjadi kesalahan saat memproses pembayaran';
}

// Payment account information
$payment_accounts = [
    'transfer' => [
        'BCA' => '1234567890 (a.n. Mama Ana)',
        'BNI' => '0987654321 (a.n. Mama Ana)',
        'Mandiri' => '2468135790 (a.n. Mama Ana)'
    ],
    'qris' => [
        'image' => 'public/images/qris-code.png',
        'name' => 'Sambal Mama Ana'
    ],
    'ewallet' => [
        'DANA' => '081234567890',
        'OVO' => '081234567890',
        'GoPay' => '081234567890'
    ]
];
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-center text-gray-900 mb-8">Pembayaran</h1>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
        <!-- Payment Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Information -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center mb-6">
                    <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-receipt text-sm"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900">Informasi Pesanan</h2>
                </div>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <span class="text-gray-600">Nomor Pesanan:</span>
                        <span class="font-semibold text-gray-900">#<?php echo $order_id; ?></span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <span class="text-gray-600">Total Pembayaran:</span>
                        <span class="font-bold text-xl text-primary">Rp <?php echo number_format($order['total'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <span class="text-gray-600">Status Pembayaran:</span>
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            <?php 
                            switch($order['payment_status']) {
                                case 'menunggu':
                                    echo 'bg-yellow-100 text-yellow-800';
                                    break;
                                case 'dikonfirmasi':
                                    echo 'bg-green-100 text-green-800';
                                    break;
                                case 'gagal':
                                    echo 'bg-red-100 text-red-800';
                                    break;
                                default:
                                    echo 'bg-gray-100 text-gray-800';
                            }
                            ?>">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-3">
                        <span class="text-gray-600">Metode Pembayaran:</span>
                        <span class="font-semibold text-gray-900 capitalize"><?php echo $order['payment_method']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Payment Instructions -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center mb-6">
                    <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-credit-card text-sm"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900">Instruksi Pembayaran</h2>
                </div>
                
                <?php if ($order['payment_method'] == 'transfer'): ?>
                    <div class="space-y-4">
                        <p class="text-gray-600 mb-4">Silakan transfer ke salah satu rekening berikut:</p>
                        <?php foreach ($payment_accounts['transfer'] as $bank => $account): ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-primary hover:bg-primary/5 transition-colors">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mr-4">
                                        <i class="fas fa-university text-primary"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900"><?php echo $bank; ?></h3>
                                        <p class="text-lg font-mono text-gray-700"><?php echo $account; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ($order['payment_method'] == 'qris'): ?>
                    <div class="text-center">
                        <p class="text-gray-600 mb-6">Scan QRIS code berikut untuk melakukan pembayaran:</p>
                        <div class="inline-block p-4 bg-gray-50 rounded-lg">
                            <img src="<?php echo $payment_accounts['qris']['image']; ?>" 
                                 alt="QRIS Code" 
                                 class="w-48 h-48 mx-auto">
                        </div>
                        <p class="text-sm text-gray-500 mt-4">
                            Pembayaran ke: <strong><?php echo $payment_accounts['qris']['name']; ?></strong>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <p class="text-gray-600 mb-4">Silakan transfer ke salah satu e-wallet berikut:</p>
                        <?php foreach ($payment_accounts['ewallet'] as $wallet => $number): ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-primary hover:bg-primary/5 transition-colors">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mr-4">
                                        <i class="fas fa-mobile-alt text-primary"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900"><?php echo $wallet; ?></h3>
                                        <p class="text-lg font-mono text-gray-700"><?php echo $number; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($order['payment_status'] == 'menunggu'): ?>
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex items-center mb-4">
                            <div class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-upload text-xs"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Upload Bukti Pembayaran</h3>
                        </div>
                        <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                            <div>
                                <label for="bukti_transfer" class="block text-sm font-medium text-gray-700 mb-2">
                                    Pilih File Bukti Transfer
                                </label>
                                <input type="file" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" 
                                       id="bukti_transfer" 
                                       name="bukti_transfer" 
                                       accept="image/*" 
                                       required>
                                <p class="text-sm text-gray-500 mt-2">
                                    Format: JPG, JPEG, PNG (Maksimal 5MB)
                                </p>
                            </div>
                            <button type="submit" 
                                    class="w-full bg-primary hover:bg-primary/90 text-white font-medium py-3 px-6 rounded-md transition-colors duration-200 flex items-center justify-center space-x-2">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Upload Bukti Pembayaran</span>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Status -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg p-6 sticky top-24">
                <div class="flex items-center mb-6">
                    <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-truck text-sm"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900">Status Pesanan</h2>
                </div>
                
                <div class="space-y-6">
                    <!-- Status 1 -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold
                                <?php echo ($order['status'] == 'menunggu' || $order['status'] == 'diproses' || $order['status'] == 'dikirim' || $order['status'] == 'selesai') ? 'bg-primary text-white' : 'bg-gray-200 text-gray-500'; ?>">
                                1
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium <?php echo ($order['status'] == 'menunggu' || $order['status'] == 'diproses' || $order['status'] == 'dikirim' || $order['status'] == 'selesai') ? 'text-primary' : 'text-gray-500'; ?>">
                                Menunggu Pembayaran
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">Pesanan menunggu konfirmasi pembayaran</p>
                        </div>
                    </div>

                    <!-- Connector Line -->
                    <div class="flex">
                        <div class="w-10 flex justify-center">
                            <div class="w-0.5 h-6 <?php echo ($order['status'] == 'diproses' || $order['status'] == 'dikirim' || $order['status'] == 'selesai') ? 'bg-primary' : 'bg-gray-200'; ?>"></div>
                        </div>
                    </div>

                    <!-- Status 2 -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold
                                <?php echo ($order['status'] == 'diproses' || $order['status'] == 'dikirim' || $order['status'] == 'selesai') ? 'bg-primary text-white' : 'bg-gray-200 text-gray-500'; ?>">
                                2
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium <?php echo ($order['status'] == 'diproses' || $order['status'] == 'dikirim' || $order['status'] == 'selesai') ? 'text-primary' : 'text-gray-500'; ?>">
                                Pesanan Diproses
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">Pesanan sedang disiapkan</p>
                        </div>
                    </div>

                    <!-- Connector Line -->
                    <div class="flex">
                        <div class="w-10 flex justify-center">
                            <div class="w-0.5 h-6 <?php echo ($order['status'] == 'dikirim' || $order['status'] == 'selesai') ? 'bg-primary' : 'bg-gray-200'; ?>"></div>
                        </div>
                    </div>

                    <!-- Status 3 -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold
                                <?php echo ($order['status'] == 'dikirim' || $order['status'] == 'selesai') ? 'bg-primary text-white' : 'bg-gray-200 text-gray-500'; ?>">
                                3
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium <?php echo ($order['status'] == 'dikirim' || $order['status'] == 'selesai') ? 'text-primary' : 'text-gray-500'; ?>">
                                Dalam Pengiriman
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">Pesanan sedang dalam perjalanan</p>
                        </div>
                    </div>

                    <!-- Connector Line -->
                    <div class="flex">
                        <div class="w-10 flex justify-center">
                            <div class="w-0.5 h-6 <?php echo ($order['status'] == 'selesai') ? 'bg-primary' : 'bg-gray-200'; ?>"></div>
                        </div>
                    </div>

                    <!-- Status 4 -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold
                                <?php echo ($order['status'] == 'selesai') ? 'bg-primary text-white' : 'bg-gray-200 text-gray-500'; ?>">
                                4
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium <?php echo ($order['status'] == 'selesai') ? 'text-primary' : 'text-gray-500'; ?>">
                                Pesanan Selesai
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">Pesanan telah diterima</p>
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-2"></i>
                        <div class="text-sm text-blue-700">
                            <p class="font-medium mb-1">Informasi Penting:</p>
                            <ul class="space-y-1 text-xs">
                                <li>• Upload bukti pembayaran untuk mempercepat proses</li>
                                <li>• Konfirmasi pembayaran dalam 24 jam</li>
                                <li>• Hubungi customer service jika ada kendala</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
