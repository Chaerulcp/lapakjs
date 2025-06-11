<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Sambal Mama Ana</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../public/css/admin-modern.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#e32929',
                        secondary: '#1a1a1a',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="h-full">
    <div class="min-h-full">
        <!-- Mobile menu button -->
        <button 
            type="button" 
            class="lg:hidden fixed top-4 left-4 z-50 rounded-md bg-gray-800 p-2 text-gray-400 hover:bg-gray-700 focus:outline-none"
            onclick="toggleSidebar()"
        >
            <i class="fas fa-bars"></i>
        </button>

        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 ease-in-out lg:translate-x-0" 
             id="sidebar">
            <div class="flex h-full flex-col bg-gray-900">
                <!-- Sidebar header -->
                <div class="flex h-16 items-center gap-3 px-6 bg-gray-900 border-b border-gray-800">
                    <div class="h-10 w-10 rounded-lg bg-primary flex items-center justify-center text-white font-bold text-xl">
                        S
                    </div>
                    <div class="text-white text-lg font-semibold">Admin Panel</div>
                </div>

                <!-- Sidebar content -->
                <nav class="flex-1 space-y-1 px-3 py-4 overflow-y-auto">
                    <a href="dashboard.php" 
                       class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-gray-800 text-white' : ''; ?>">
                        <i class="fas fa-home w-5"></i>
                        <span>Dashboard</span>
                    </a>

                    <!-- E-commerce Management -->
                    <div class="pt-4 mt-4 border-t border-gray-800">
                        <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">E-commerce</h3>
                        <a href="manage_products.php" 
                           class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'manage_products.php' ? 'bg-gray-800 text-white' : ''; ?>">
                            <i class="fas fa-box w-5"></i>
                            <span>Produk</span>
                        </a>
                        <a href="manage_orders.php" 
                           class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'manage_orders.php' ? 'bg-gray-800 text-white' : ''; ?>">
                            <i class="fas fa-shopping-cart w-5"></i>
                            <span>Pesanan</span>
                        </a>
                        <a href="manage_payments.php" 
                           class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'manage_payments.php' ? 'bg-gray-800 text-white' : ''; ?>">
                            <i class="fas fa-credit-card w-5"></i>
                            <span>Pembayaran</span>
                        </a>
                    </div>

                    <!-- Content Management -->
                    <div class="pt-4 mt-4 border-t border-gray-800">
                        <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Konten</h3>
                        <a href="manage_contents.php" 
                           class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'manage_contents.php' ? 'bg-gray-800 text-white' : ''; ?>">
                            <i class="fas fa-newspaper w-5"></i>
                            <span>Manajemen Konten</span>
                        </a>
                    </div>

                    <!-- User Management -->
                    <div class="pt-4 mt-4 border-t border-gray-800">
                        <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pengguna</h3>
                        <a href="manage_users.php" 
                           class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'bg-gray-800 text-white' : ''; ?>">
                            <i class="fas fa-users w-5"></i>
                            <span>Manajemen Pengguna</span>
                        </a>
                    </div>

                    <!-- System/Logs -->
                    <div class="pt-4 mt-4 border-t border-gray-800">
                        <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Sistem</h3>
                        <a href="activity_logs.php" 
                           class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'activity_logs.php' ? 'bg-gray-800 text-white' : ''; ?>">
                            <i class="fas fa-history w-5"></i>
                            <span>Log Aktivitas</span>
                        </a>
                    </div>

                    <div class="pt-4 mt-4 border-t border-gray-800">
                        <a href="../logout.php" 
                           class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-red-400 hover:bg-gray-800 hover:text-red-300 transition-colors">
                            <i class="fas fa-sign-out-alt w-5"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </nav>
            </div>
        </div>

        <!-- Main content -->
        <main class="lg:pl-64">
            <div class="px-4 py-6 sm:px-6 lg:px-8">
