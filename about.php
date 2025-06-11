<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="relative bg-gradient-to-br from-red-600 via-red-700 to-red-800 py-24 text-white text-center overflow-hidden">
    <div class="absolute inset-0">
        <div class="absolute top-20 left-10 w-32 h-32 bg-yellow-400/20 rounded-full blur-xl animate-pulse"></div>
        <div class="absolute bottom-20 right-10 w-40 h-40 bg-orange-400/20 rounded-full blur-xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/3 w-24 h-24 bg-red-300/20 rounded-full blur-xl animate-pulse delay-500"></div>
    </div>
    <div class="container mx-auto px-4 relative z-10">
        <h1 class="text-5xl md:text-7xl font-bold mb-4 animate-fade-in-up">Tentang Kami</h1>
        <p class="text-xl md:text-2xl text-red-100 animate-fade-in-up delay-200">Kisah di Balik Kelezatan Sambal Mama Ana</p>
    </div>
</section>

<!-- Our Story Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="lg:order-1">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6 leading-tight">
                    Perjalanan <span class="text-red-600">Sambal Mama Ana</span>
                </h2>
                <p class="text-lg text-gray-700 mb-6 leading-relaxed">
                    Sambal Mama Ana berawal dari dapur sederhana seorang ibu bernama Ana, yang memiliki kecintaan mendalam terhadap kuliner pedas dan tradisi masakan rumahan. Sejak tahun 2018, Mama Ana mulai meracik sambal dengan resep turun-temurun yang telah disempurnakan selama bertahun-tahun. Awalnya hanya untuk konsumsi keluarga dan kerabat dekat, namun karena respons yang luar biasa, Mama Ana memutuskan untuk berbagi kelezatan ini dengan lebih banyak orang.
                </p>
                <p class="text-lg text-gray-700 mb-8 leading-relaxed">
                    Setiap botol Sambal Mama Ana adalah hasil dari proses yang teliti, dimulai dari pemilihan bahan-bahan segar terbaik langsung dari petani lokal, hingga proses pengolahan yang higienis dan penuh cinta. Kami berkomitmen untuk menjaga kualitas dan keaslian rasa, sehingga setiap suapan membawa Anda pada pengalaman kuliner yang tak terlupakan.
                </p>
                <a 
                    href="products.php" 
                    class="inline-flex items-center px-8 py-4 bg-yellow-400 hover:bg-yellow-300 text-red-800 font-semibold rounded-full transition-all duration-300 transform hover:scale-105 shadow-lg"
                >
                    Lihat Produk Kami
                    <i class="fas fa-arrow-right ml-3"></i>
                </a>
            </div>
            <div class="lg:order-2">
                <img 
                    src="https://www.sasa.co.id/medias/page_medias/resep_sambal_terasi_yang_pedas_dan_enak.jpg" 
                    alt="Our Story" 
                    class="rounded-3xl shadow-xl transform hover:scale-105 transition-transform duration-500"
                >
            </div>
        </div>
    </div>
</section>

<!-- Our Mission Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6">Misi Kami</h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto mb-12">
            Menghadirkan cita rasa sambal autentik Indonesia ke setiap meja makan, dengan kualitas terbaik dan kebahagiaan di setiap gigitan.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <div class="w-20 h-20 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-pepper-hot text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Kelezatan Autentik</h3>
                <p class="text-gray-600">Menjaga resep tradisional dan cita rasa asli Indonesia.</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <div class="w-20 h-20 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-seedling text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Bahan Berkualitas</h3>
                <p class="text-gray-600">Hanya menggunakan bahan-bahan segar dan alami pilihan.</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <div class="w-20 h-20 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-smile-beam text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Kepuasan Pelanggan</h3>
                <p class="text-gray-600">Memberikan pengalaman terbaik bagi setiap penikmat sambal.</p>
            </div>
        </div>
    </div>
</section>

<!-- Our Values Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6">Nilai-Nilai Kami</h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto mb-12">
            Prinsip yang membimbing setiap langkah kami dalam menciptakan sambal terbaik.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="bg-gray-50 p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <div class="w-20 h-20 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-hand-holding-heart text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Cinta & Dedikasi</h3>
                <p class="text-gray-600">Setiap botol dibuat dengan hati dan dedikasi tinggi.</p>
            </div>
            <div class="bg-gray-50 p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-certificate text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Kualitas Terjamin</h3>
                <p class="text-gray-600">Komitmen pada standar kualitas tertinggi.</p>
            </div>
            <div class="bg-gray-50 p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-users text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Komunitas</h3>
                <p class="text-gray-600">Membangun hubungan erat dengan pelanggan dan mitra.</p>
            </div>
            <div class="bg-gray-50 p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-leaf text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Keberlanjutan</h3>
                <p class="text-gray-600">Praktik ramah lingkungan dalam setiap aspek produksi.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="py-20 bg-red-700 text-white text-center">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl md:text-5xl font-bold mb-4">Siap Merasakan Kelezatan Sambal Mama Ana?</h2>
        <p class="text-xl text-red-100 max-w-2xl mx-auto mb-8">
            Jelajahi koleksi sambal kami dan temukan pedas favorit Anda hari ini!
        </p>
        <a 
            href="products.php" 
            class="inline-flex items-center px-8 py-4 bg-yellow-400 hover:bg-yellow-300 text-red-800 font-semibold rounded-full transition-all duration-300 transform hover:scale-105 shadow-lg"
        >
            <i class="fas fa-shopping-bag mr-3"></i>
            Belanja Sekarang
        </a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
