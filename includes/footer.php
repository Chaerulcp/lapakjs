</main>
    <footer class="bg-secondary mt-20">
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Brand Section -->
                <div class="space-y-4">
                    <h3 class="text-white text-xl font-semibold">Sambal Mama Ana</h3>
                    <p class="text-gray-300">Rasakan Pedasnya Cinta dalam Setiap Sendok Sambal</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-300 hover:text-primary transition-colors">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-primary transition-colors">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-primary transition-colors">
                            <i class="fab fa-whatsapp text-xl"></i>
                        </a>
                    </div>
                </div>

                <!-- Menu Section -->
                <div class="space-y-4">
                    <h3 class="text-white text-xl font-semibold">Menu</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="index.php" class="text-gray-300 hover:text-primary transition-colors">Beranda</a>
                        </li>
                        <li>
                            <a href="products.php" class="text-gray-300 hover:text-primary transition-colors">Produk</a>
                        </li>
                        <li>
                            <a href="content.php" class="text-gray-300 hover:text-primary transition-colors">Edukasi Kuliner</a>
                        </li>
                        <li>
                            <a href="about.php" class="text-gray-300 hover:text-primary transition-colors">Tentang Kami</a>
                        </li>
                    </ul>
                </div>

                <!-- Contact Section -->
                <div class="space-y-4">
                    <h3 class="text-white text-xl font-semibold">Kontak</h3>
                    <ul class="space-y-2">
                        <li class="flex items-center text-gray-300">
                            <i class="fas fa-phone w-6"></i>
                            <span>+62 812-3456-7890</span>
                        </li>
                        <li class="flex items-center text-gray-300">
                            <i class="fas fa-envelope w-6"></i>
                            <span>info@sambalmamaana.com</span>
                        </li>
                        <li class="flex items-center text-gray-300">
                            <i class="fas fa-map-marker-alt w-6"></i>
                            <span>Jl. Contoh No. 123, Kota, Indonesia</span>
                        </li>
                    </ul>
                </div>

                <!-- Newsletter Section -->
                <div class="space-y-4">
                    <h3 class="text-white text-xl font-semibold">Newsletter</h3>
                    <p class="text-gray-300">Dapatkan info terbaru dan promo menarik</p>
                    <form action="subscribe.php" method="POST" class="space-y-2">
                        <input 
                            type="email" 
                            name="email" 
                            placeholder="Email Anda" 
                            required
                            class="w-full px-4 py-2 rounded-md bg-gray-800 text-white border border-gray-700 focus:outline-none focus:border-primary"
                        >
                        <button 
                            type="submit" 
                            class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-md transition-colors"
                        >
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>

            <!-- Copyright -->
            <div class="mt-12 pt-8 border-t border-gray-800 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> Sambal Mama Ana. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
