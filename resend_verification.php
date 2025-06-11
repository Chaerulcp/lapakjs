<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
require_once 'includes/mailer.php'; // Include the mailer function

$error = '';
$success = '';
$email_value = ''; // To pre-fill the email field

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $email_value = htmlspecialchars($email); // Keep value for form

    if (empty($email)) {
        $error = 'Email harus diisi.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, nama, is_verified FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                if ($user['is_verified'] == 1) {
                    $error = 'Akun Anda sudah diverifikasi. Silakan login.';
                } else {
                    // Generate a new verification token
                    $new_verification_token = bin2hex(random_bytes(16));

                    // Update the user's verification token in the database
                    $update_stmt = $pdo->prepare("UPDATE users SET verification_token = ? WHERE id = ?");
                    if ($update_stmt->execute([$new_verification_token, $user['id']])) {
                        // Send new verification email
                        $verification_link = "http://" . $_SERVER['HTTP_HOST'] . "/verify_email.php?token=" . $new_verification_token;
                        $subject = "Verifikasi Ulang Akun Sambal Mama Ana Anda";
                        $message_body = getVerificationEmailTemplate($user['nama'], $verification_link);
                        
                        if (sendEmail($email, $user['nama'], $subject, $message_body)) {
                            $success = 'Tautan verifikasi baru telah dikirim ke email Anda. Silakan cek kotak masuk Anda.';
                        } else {
                            $error = 'Gagal mengirim email verifikasi. Silakan coba lagi nanti atau hubungi administrator.';
                            // Error logging is already handled inside sendEmail function
                        }
                    } else {
                        $error = 'Gagal memperbarui token verifikasi. Silakan coba lagi.';
                    }
                }
            } else {
                $error = 'Email tidak terdaftar.';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan. Silakan coba lagi nanti.';
            error_log("Resend verification error: " . $e->getMessage());
        }
    }
} elseif (isset($_GET['email']) && !empty($_GET['email'])) {
    // Pre-fill email if passed from login page
    $email_value = htmlspecialchars(filter_input(INPUT_GET, 'email', FILTER_SANITIZE_EMAIL));
}
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Kirim Ulang Verifikasi</h1>
            <p class="text-gray-600">Masukkan email Anda untuk menerima tautan verifikasi baru.</p>
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
                        value="<?php echo $email_value; ?>"
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary"
                    >
                </div>
            </div>

            <div>
                <button 
                    type="submit" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                >
                    Kirim Ulang Tautan Verifikasi
                </button>
            </div>

            <div class="text-center">
                <a href="login.php" class="text-sm text-gray-600 hover:text-primary">
                    Kembali ke Halaman Login
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
