<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$message = '';
$is_success = false;

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = filter_input(INPUT_GET, 'token', FILTER_UNSAFE_RAW);

    // Validate that the token is a valid hexadecimal string
    if (!preg_match('/^[a-f0-9]{32}$/i', $token)) { // Assuming 32-character hex string from bin2hex(random_bytes(16))
        $message = 'Token verifikasi tidak valid.';
        goto end_verification; // Jump to the end of the verification logic
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_token = ? AND is_verified = 0");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            $pdo->beginTransaction();

            $update_stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
            if ($update_stmt->execute([$user['id']])) {
                $pdo->commit();
                $message = 'Email Anda berhasil diverifikasi! Anda sekarang dapat login.';
                $is_success = true;
            } else {
                $pdo->rollBack();
                $message = 'Gagal memverifikasi email Anda. Silakan coba lagi.';
            }
        } else {
            $message = 'Token verifikasi tidak valid atau sudah digunakan.';
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Email verification error: " . $e->getMessage());
        $message = 'Terjadi kesalahan saat memverifikasi email Anda. Silakan coba lagi nanti.';
    }
} else {
    $message = 'Token verifikasi tidak ditemukan.';
}

end_verification:
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="mb-8">
            <?php if ($is_success): ?>
                <i class="fas fa-check-circle text-green-500 text-6xl mb-4"></i>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Verifikasi Berhasil!</h1>
            <?php else: ?>
                <i class="fas fa-exclamation-circle text-red-500 text-6xl mb-4"></i>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Verifikasi Gagal</h1>
            <?php endif; ?>
            <p class="text-gray-600">
                <?php echo htmlspecialchars($message); ?>
            </p>
            <?php if (!$is_success): // Only show resend link if verification failed ?>
                <p class="mt-4 text-gray-600">
                    <a href="resend_verification.php" class="text-primary hover:underline">Kirim ulang tautan verifikasi</a>
                </p>
            <?php endif; ?>
        </div>
        <a href="login.php" class="w-full inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
            Kembali ke Halaman Login
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
