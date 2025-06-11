<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$error = '';

// Check if admin exists, if not create one
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $adminCount = $stmt->fetchColumn();

    if ($adminCount == 0) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (nama, email, password, role, created_at) 
            VALUES (?, ?, ?, 'admin', NOW())
        ");
        $stmt->execute(['Administrator', 'admin@admin.com', $password]);
        error_log("Default admin user created - Email: admin@admin.com");
        $success_message = "Default admin account created with:<br>Email: admin@admin.com<br>Password: admin123";
    }
} catch (PDOException $e) {
    error_log("Error checking/creating admin: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Semua field harus diisi';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] == 'inactive') {
                    $error = 'Akun Anda dinonaktifkan. Silakan hubungi administrator.';
                } elseif ($user['is_verified'] == 0) {
                    $error = 'Akun belum diverifikasi. Silakan cek email Anda untuk tautan verifikasi.';
                    $resend_link_html = '<p class="text-sm text-red-700 mt-2">Belum menerima email? <a href="resend_verification.php?email=' . htmlspecialchars($email) . '" class="font-medium text-blue-700 hover:text-blue-800">Kirim ulang kode verifikasi</a></p>';
                } else {
                    session_regenerate_id(true);
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_name'] = $user['nama'];

                    if ($user['role'] == 'admin') {
                        header('Location: admin/dashboard.php');
                    } elseif ($user['role'] == 'reseller') {
                        header('Location: index.php');
                    } else {
                        header('Location: index.php');
                    }
                    exit();
                }
            } else {
                $error = 'Email atau password salah';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan. Silakan coba lagi nanti.';
            error_log("Login error: " . $e->getMessage());
        }
    }
}
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                SELAMAT DATANG DI<br>SAMBAL MAMA ANA
            </h1>
            <p class="text-gray-600">
                Silakan login untuk melanjutkan
            </p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="mb-4 p-4 rounded-md bg-green-50 border border-green-200">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            <?php echo $success_message; ?>
                        </p>
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
                        <p class="text-sm text-red-700">
                            <?php echo htmlspecialchars($error); ?>
                        </p>
                        <?php if (isset($resend_link_html)) { echo $resend_link_html; } ?>
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
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary"
                    >
                </div>
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
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary"
                    >
                </div>
            </div>

            <div>
                <button 
                    type="submit" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                >
                    Masuk
                </button>
            </div>

            <div class="text-center space-y-2">
                <p>
                    <a href="register.php" class="text-sm text-gray-600 hover:text-primary">
                        Belum punya akun? Daftar di sini
                    </a>
                </p>
                <p>
                    <a href="forgot_password.php" class="text-sm text-gray-500 hover:text-primary">
                        Lupa Password?
                    </a>
                </p>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
