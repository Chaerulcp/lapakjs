<?php
require_once 'includes/header.php';
require_once 'includes/db.php'; // For potential future use with resend verification

$email = $_GET['email'] ?? ''; // Get email from URL parameter

// Basic validation for email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // If email is not valid, redirect to home or a generic error page
    header('Location: index.php');
    exit();
}
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Pendaftaran Berhasil!</h1>
            <p class="text-gray-600">Akun Anda telah berhasil didaftarkan.</p>
        </div>

        <div class="mb-6 p-4 rounded-md bg-green-50 border border-green-200">
            <div class="flex items-center justify-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400 text-2xl"></i>
                </div>
                <div class="ml-3 text-left">
                    <p class="text-sm text-green-700">
                        Silakan cek email Anda (<?php echo htmlspecialchars($email); ?>) untuk memverifikasi akun Anda.
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Email verifikasi mungkin masuk ke folder spam/junk Anda.
                    </p>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <p class="text-gray-700">Belum menerima email verifikasi?</p>
            <a href="resend_verification.php?email=<?php echo urlencode($email); ?>" 
               class="w-full inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Kirim Ulang Kode Verifikasi
            </a>
            <p class="text-sm text-gray-600 mt-4">
                Setelah verifikasi, Anda bisa <a href="login.php" class="text-primary hover:underline">Login di sini</a>.
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
