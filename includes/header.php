<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sambal Mama Ana</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#e32929',
                        secondary: '#1a1a1a',
                    },
                    fontFamily: {
                        poppins: ['Poppins', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="font-poppins bg-gray-50">
    <header class="fixed top-0 left-0 right-0 z-50 bg-white shadow-md">
        <nav class="container mx-auto px-4 py-4 flex items-center justify-between">
            <a href="index.php" class="text-2xl font-bold text-primary hover:text-primary/90 transition-colors">
                Sambal Mama Ana
            </a>

            <!-- Desktop Navigation -->
            <ul class="hidden md:flex items-center space-x-8">
                <li><a href="index.php" class="text-gray-700 hover:text-primary transition-colors">Beranda</a></li>
                <li><a href="products.php" class="text-gray-700 hover:text-primary transition-colors">Produk</a></li>
                <li><a href="content.php" class="text-gray-700 hover:text-primary transition-colors">Edukasi Kuliner</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="purchase_history.php" class="text-gray-700 hover:text-primary transition-colors">Riwayat Pembelian</a></li>
                    <li><a href="cart.php" class="text-gray-700 hover:text-primary transition-colors"><i class="fas fa-shopping-cart mr-2"></i>Keranjang</a></li>
                    <li>
                        <a href="logout.php" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90 transition-colors">
                            Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="login.php" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90 transition-colors">
                            Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Mobile menu button -->
            <button id="mobile-menu-button" class="md:hidden text-gray-600 hover:text-primary focus:outline-none">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </nav>

        <!-- Mobile Navigation -->
        <div id="mobile-menu" class="hidden md:hidden bg-white shadow-md">
            <ul class="flex flex-col space-y-4 px-4 py-4">
                <li><a href="index.php" class="block text-gray-700 hover:text-primary transition-colors">Beranda</a></li>
                <li><a href="products.php" class="block text-gray-700 hover:text-primary transition-colors">Produk</a></li>
                <li><a href="content.php" class="block text-gray-700 hover:text-primary transition-colors">Edukasi Kuliner</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="purchase_history.php" class="block text-gray-700 hover:text-primary transition-colors">Riwayat Pembelian</a></li>
                    <li><a href="cart.php" class="block text-gray-700 hover:text-primary transition-colors"><i class="fas fa-shopping-cart mr-2"></i>Keranjang</a></li>
                    <li>
                        <a href="logout.php" class="block w-full text-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90 transition-colors">
                            Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="login.php" class="block w-full text-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90 transition-colors">
                            Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <main class="mt-20">
    
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
    </script>
