<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (empty($email)) {
        $error = 'Email harus diisi';
    } else {
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // In a real application, you would send an email with reset link
                // For now, we'll just show a success message
                $success = 'Jika email terdaftar, instruksi reset password telah dikirim ke email Anda.';
            } else {
                // Don't reveal if email exists or not for security
                $success = 'Jika email terdaftar, instruksi reset password telah dikirim ke email Anda.';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan. Silakan coba lagi nanti.';
            error_log("Forgot password error: " . $e->getMessage());
        }
    }
}
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-key text-2xl text-primary"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Lupa Password?</h1>
            <p class="text-gray-600">
                Masukkan email Anda dan kami akan mengirimkan instruksi untuk reset password
            </p>
        </div>

        <?php if ($success): ?>
            <div class="mb-4 p-4 rounded-md bg-green-50 border border-green-200">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700"><?php echo $success; ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-4 p-4 rounded-md bg-red-50 border border-red-200">
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

        <form method="POST" action="" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                    Email
                </label>
                <div class="mt-1">
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        placeholder="Masukkan email Anda"
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary"
                    >
                </div>
            </div>

            <div>
                <button 
                    type="submit" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                >
                    Reset Password
                </button>
            </div>

            <div class="text-center">
                <a href="login.php" class="text-sm text-gray-600 hover:text-primary">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Login
                </a>
            </div>
        </form>

        <!-- Additional Info -->
        <div class="mt-8 p-4 bg-gray-50 rounded-md">
            <h3 class="text-sm font-medium text-gray-900 mb-2">Catatan:</h3>
            <ul class="text-sm text-gray-600 space-y-1">
                <li>• Periksa folder spam jika tidak menerima email</li>
                <li>• Link reset berlaku selama 24 jam</li>
                <li>• Hubungi admin jika masih bermasalah</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
