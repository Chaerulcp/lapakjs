<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
?>

<!-- Hero Section with Modern Design -->
<div class="relative bg-gradient-to-br from-red-600 via-red-700 to-red-800 h-screen -mt-20 overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0">
        <div class="absolute top-20 left-10 w-32 h-32 bg-yellow-400/20 rounded-full blur-xl animate-pulse"></div>
        <div class="absolute bottom-20 right-10 w-40 h-40 bg-orange-400/20 rounded-full blur-xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/3 w-24 h-24 bg-red-300/20 rounded-full blur-xl animate-pulse delay-500"></div>
    </div>

    <!-- Hero Content -->
    <div class="relative h-full flex items-center justify-center text-center px-4 z-10">
        <div class="max-w-5xl">
            <div class="mb-6">
                <span class="inline-block px-4 py-2 bg-yellow-400 text-red-800 font-semibold rounded-full text-sm mb-4 animate-bounce">
                    🌶️ Sambal Terlezat Se-Indonesia
                </span>
            </div>
            <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 leading-tight animate-fade-in-up">
                Sambal Mama Ana
                <span class="block text-3xl md:text-4xl text-yellow-300 font-normal mt-2">
                    Pedasnya Cinta, Nikmatnya Tradisi
                </span>
            </h1>
            <p class="text-xl md:text-2xl text-red-100 mb-10 max-w-3xl mx-auto leading-relaxed animate-fade-in-up delay-200">
                Rasakan kelezatan sambal homemade dengan resep rahasia keluarga yang telah diwariskan turun-temurun. 
                Dibuat dengan bahan pilihan dan cinta yang tulus.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center animate-fade-in-up delay-400">
                <a 
                    href="products.php" 
                    class="inline-flex items-center px-8 py-4 text-lg font-semibold text-red-800 bg-yellow-400 hover:bg-yellow-300 rounded-full transition-all duration-300 transform hover:scale-105 shadow-lg"
                >
                    <i class="fas fa-shopping-bag mr-3"></i>
                    Belanja Sekarang
                </a>
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 text-white animate-bounce">
        <i class="fas fa-chevron-down text-2xl"></i>
    </div>
</div>

<!-- Statistics Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div class="group">
                <div class="text-4xl md:text-5xl font-bold text-red-600 mb-2 group-hover:scale-110 transition-transform">1000+</div>
                <div class="text-gray-600 font-medium">Pelanggan Puas</div>
            </div>
            <div class="group">
                <div class="text-4xl md:text-5xl font-bold text-red-600 mb-2 group-hover:scale-110 transition-transform">Resep</div>
                <div class="text-gray-600 font-medium">Autentik</div>
            </div>
            <div class="group">
                <div class="text-4xl md:text-5xl font-bold text-red-600 mb-2 group-hover:scale-110 transition-transform">5</div>
                <div class="text-gray-600 font-medium">Tahun Pengalaman</div>
            </div>
            <div class="group">
                <div class="text-4xl md:text-5xl font-bold text-red-600 mb-2 group-hover:scale-110 transition-transform">100%</div>
                <div class="text-gray-600 font-medium">Bahan Alami</div>
            </div>
        </div>
    </div>
</section>

<!-- About Sambal Mama Ana Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="lg:order-2">
                <img 
                    src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQEzkqVSUxMKrWlGWdTgq2yhtjud2iPrzB1IA&s" 
                    alt="Sambal Mama Ana" 
                    class="w-full h-96 object-cover rounded-3xl shadow-2xl transform hover:scale-105 transition-transform duration-500 aspect-video"
                >
            </div>
            <div class="lg:order-1 text-center lg:text-left">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6 leading-tight">
                    Kisah di Balik <span class="text-red-600">Sambal Mama Ana</span>
                </h2>
                <p class="text-lg text-gray-700 mb-6 leading-relaxed">
                    Sambal Mama Ana lahir dari kecintaan mendalam terhadap kuliner pedas dan keinginan untuk berbagi 
                    kelezatan resep keluarga yang telah diwariskan secara turun-temurun. Dimulai dari dapur rumahan 
                    dengan bahan-bahan segar pilihan, setiap botol Sambal Mama Ana adalah perwujudan dari dedikasi 
                    dan cinta.
                </p>
                <p class="text-lg text-gray-700 mb-8 leading-relaxed">
                    Kami percaya bahwa sambal bukan hanya sekadar pelengkap makanan, melainkan sebuah pengalaman 
                    rasa yang membangkitkan selera dan kenangan. Dengan perpaduan sempurna antara rempah alami 
                    dan cabai pilihan, Sambal Mama Ana menghadirkan cita rasa autentik yang pedasnya pas, nikmatnya 
                    mantap, dan selalu bikin nagih.
                </p>
                <a 
                    href="about.php" 
                    class="inline-flex items-center px-8 py-4 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-full transition-all duration-300 transform hover:scale-105 shadow-lg"
                >
                    Pelajari Lebih Lanjut
                    <i class="fas fa-arrow-right ml-3"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">Mengapa Pilih Kami?</h2>
            <p class="text-xl text-gray-600">Komitmen kami untuk kualitas terbaik</p>
            <div class="w-24 h-1 bg-red-600 mx-auto mt-6"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="group bg-white p-8 rounded-2xl shadow-lg text-center hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <div class="w-20 h-20 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                    <i class="fas fa-home text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">100% Homemade</h3>
                <p class="text-gray-600">Dibuat dengan tangan dan penuh cinta di dapur rumahan</p>
            </div>

            <div class="group bg-white p-8 rounded-2xl shadow-lg text-center hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                    <i class="fas fa-leaf text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Bahan Alami</h3>
                <p class="text-gray-600">Tanpa pengawet, menggunakan bahan-bahan segar pilihan</p>
            </div>

            <div class="group bg-white p-8 rounded-2xl shadow-lg text-center hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                    <i class="fas fa-award text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Kualitas Terjamin</h3>
                <p class="text-gray-600">Resep rahasia keluarga yang telah teruji bertahun-tahun</p>
            </div>

            <div class="group bg-white p-8 rounded-2xl shadow-lg text-center hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                    <i class="fas fa-shipping-fast text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Pengiriman Cepat</h3>
                <p class="text-gray-600">Pelayanan responsif dengan pengiriman ke seluruh Indonesia</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">Kata Pelanggan</h2>
            <p class="text-xl text-gray-600">Kepuasan pelanggan adalah prioritas utama kami</p>
            <div class="w-24 h-1 bg-red-600 mx-auto mt-6"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-gray-50 p-8 rounded-2xl shadow-lg">
                <div class="flex items-center mb-4">
                    <div class="flex text-yellow-400 text-xl">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <p class="text-gray-600 mb-6 italic">"Sambal Mama Ana benar-benar luar biasa! Pedasnya pas dan rasanya autentik. Sudah jadi langganan keluarga kami."</p>
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center text-white font-bold mr-4">
                        S
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800">Sari Dewi</div>
                        <div class="text-sm text-gray-500">Jakarta</div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-8 rounded-2xl shadow-lg">
                <div class="flex items-center mb-4">
                    <div class="flex text-yellow-400 text-xl">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <p class="text-gray-600 mb-6 italic">"Kualitas terbaik dengan harga yang sangat terjangkau. Pengiriman juga cepat. Highly recommended!"</p>
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center text-white font-bold mr-4">
                        B
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800">Budi Santoso</div>
                        <div class="text-sm text-gray-500">Surabaya</div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-8 rounded-2xl shadow-lg">
                <div class="flex items-center mb-4">
                    <div class="flex text-yellow-400 text-xl">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <p class="text-gray-600 mb-6 italic">"Sambal Mama Ana selalu jadi pilihan utama saya. Rasanya konsisten enak dan bikin nagih. Top banget!"</p>
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center text-white font-bold mr-4">
                        A
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800">Ani Rahayu</div>
                        <div class="text-sm text-gray-500">Bandung</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-20 bg-red-700 text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-4xl md:text-5xl font-bold mb-4">Dapatkan Update Terbaru!</h2>
        <p class="text-xl text-red-100 max-w-2xl mx-auto mb-8">
            Daftar newsletter kami untuk mendapatkan informasi produk terbaru, promo eksklusif, dan resep menarik langsung ke inbox Anda.
        </p>
        <form action="#" method="POST" class="max-w-lg mx-auto flex flex-col sm:flex-row gap-4">
            <input 
                type="email" 
                placeholder="Masukkan email Anda" 
                class="flex-grow px-6 py-3 rounded-full border-2 border-white bg-white/20 text-white placeholder-white/70 focus:outline-none focus:border-yellow-400 transition-colors"
                required
            >
            <button 
                type="submit" 
                class="px-8 py-3 bg-yellow-400 hover:bg-yellow-300 text-red-800 font-semibold rounded-full transition-all duration-300 transform hover:scale-105 shadow-lg"
            >
                Berlangganan
            </button>
        </form>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
