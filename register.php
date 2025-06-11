<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
require_once 'includes/mailer.php'; // Include the mailer function

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input data
    $nama = htmlspecialchars(trim(filter_input(INPUT_POST, 'nama', FILTER_UNSAFE_RAW)));
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $alamat = htmlspecialchars(trim(filter_input(INPUT_POST, 'alamat', FILTER_UNSAFE_RAW)));
    $no_hp = preg_replace('/[^0-9+\-]/', '', filter_input(INPUT_POST, 'no_hp', FILTER_UNSAFE_RAW));
    $role = filter_input(INPUT_POST, 'role', FILTER_UNSAFE_RAW);
    $role = in_array($role, ['pelanggan', 'reseller']) ? $role : '';

    if (empty($nama) || empty($email) || empty($password) || empty($confirm_password) || empty($alamat) || empty($no_hp) || empty($role)) {
        $error = 'Semua field harus diisi';
    } elseif ($password !== $confirm_password) {
        $error = 'Password tidak cocok';
    } elseif (!in_array($role, ['pelanggan', 'reseller'])) {
        $error = 'Role tidak valid';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Email sudah terdaftar';
            } else {
                // Generate verification token
                $verification_token = bin2hex(random_bytes(16)); // 32-character hex string

                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO users (nama, email, password, alamat, no_hp, role, is_verified, verification_token, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, 0, ?, NOW())
                ");
                $stmt->execute([$nama, $email, $hashed_password, $alamat, $no_hp, $role, $verification_token]);
                
                // Send verification email
                $verification_link = "http://" . $_SERVER['HTTP_HOST'] . "/verify_email.php?token=" . $verification_token;
                $subject = "Verifikasi Akun Sambal Mama Ana Anda";
                $message_body = getVerificationEmailTemplate($nama, $verification_link);
                
                // Attempt to send email using PHPMailer function
                if (sendEmail($email, $nama, $subject, $message_body)) {
                    // Redirect to success page
                    header("Location: registration_success.php?email=" . urlencode($email));
                    exit();
                } else {
                    // If email sending fails, still redirect but with a warning (optional, could also show error on this page)
                    // For now, we'll redirect to the success page and let it handle the "check email" message.
                    // The user will still be registered, but might need to manually request resend.
                    header("Location: registration_success.php?email=" . urlencode($email) . "&email_sent=false");
                    exit();
                }
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan. Silakan coba lagi nanti.';
            error_log("Registration error: " . $e->getMessage());
        }
    }
}
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Daftar Akun</h1>
            <p class="text-gray-600">Bergabunglah dengan Sambal Mama Ana</p>
        </div>

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
                <label for="nama" class="block text-sm font-medium text-gray-700">
                    Nama Lengkap
                </label>
                <div class="mt-1">
                    <input 
                        type="text" 
                        id="nama" 
                        name="nama" 
                        required
                        value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>"
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary"
                    >
                </div>
            </div>

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
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary"
                    >
                </div>
            </div>

            <div>
                <label for="alamat" class="block text-sm font-medium text-gray-700">
                    Alamat Lengkap
                </label>
                <div class="mt-1">
                    <textarea 
                        id="alamat" 
                        name="alamat" 
                        rows="3"
                        required
                        placeholder="Masukkan alamat lengkap Anda"
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary"
                    ><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
                </div>
            </div>

            <div>
                <label for="no_hp" class="block text-sm font-medium text-gray-700">
                    Nomor HP/WhatsApp
                </label>
                <div class="mt-1">
                    <input 
                        type="tel" 
                        id="no_hp" 
                        name="no_hp" 
                        required
                        placeholder="08xxxxxxxxxx"
                        value="<?php echo isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : ''; ?>"
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary"
                    >
                </div>
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">
                    Daftar Sebagai
                </label>
                <div class="mt-1">
                    <select 
                        id="role" 
                        name="role" 
                        required
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary"
                    >
                        <option value="">Pilih Role</option>
                        <option value="pelanggan" <?php echo (isset($_POST['role']) && $_POST['role'] == 'pelanggan') ? 'selected' : ''; ?>>
                            Pelanggan (Pembeli)
                        </option>
                        <option value="reseller" <?php echo (isset($_POST['role']) && $_POST['role'] == 'reseller') ? 'selected' : ''; ?>>
                            Reseller (Penjual)
                        </option>
                    </select>
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    Pilih "Pelanggan" jika ingin membeli produk, atau "Reseller" jika ingin menjual produk dengan harga khusus
                </p>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Password
                </label>
                <div class="mt-1">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        minlength="6"
                        placeholder="Minimal 6 karakter"
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary"
                    >
                </div>
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700">
                    Konfirmasi Password
                </label>
                <div class="mt-1">
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required
                        minlength="6"
                        placeholder="Ulangi password"
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary"
                    >
                </div>
            </div>

            <div>
                <button 
                    type="submit" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                >
                    Daftar
                </button>
            </div>

            <div class="text-center">
                <a href="login.php" class="text-sm text-gray-600 hover:text-primary">
                    Sudah punya akun? Login di sini
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
