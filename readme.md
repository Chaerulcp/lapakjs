graph TD
    A[Pengguna] --> B(Frontend - PHP/Next.js)
    B --> C{Backend - PHP}
    C --> D[Basis Data - SQL]

    subgraph Frontend
        B1(Halaman Publik)
        B2(Autentikasi Pengguna)
        B3(Tampilan Produk)
        B4(Keranjang & Checkout)
        B5(Proses Pembayaran)
        B6(Riwayat Pembelian)
    end

    subgraph Backend
        C1(Manajemen Pengguna)
        C2(Manajemen Produk)
        C3(Manajemen Konten)
        C4(Manajemen Pesanan)
        C5(Verifikasi Pembayaran)
        C6(Dasbor Admin & Laporan)
        C7(Layanan Email)
        C8(Interaksi Basis Data)
    end

    subgraph Panel Admin
        A1(Pengguna Admin) --> C9(Antarmuka Admin)
        C9 --> C1(Manajemen Pengguna)
        C9 --> C2(Manajemen Produk)
        C9 --> C3(Manajemen Konten)
        C9 --> C4(Manajemen Pesanan)
        C9 --> C5(Verifikasi Pembayaran)
        C9 --> C6(Dasbor Admin & Laporan)
        C9 --> C10(Log Aktivitas)
    end

    subgraph Basis Data
        D1(Tabel Pengguna)
        D2(Tabel Produk)
        D3(Tabel Pesanan)
        D4(Tabel Pembayaran)
        D5(Tabel Konten)
        D6(Tabel Log Aktivitas)
    end

    B --> C8
    C8 --> D

    B1 --> index.php
    B1 --> about.php
    B1 --> content.php
    B2 --> register.php
    B2 --> login.php
    B2 --> forgot_password.php
    B2 --> verify_email.php
    B3 --> products.php
    B3 --> product_detail.php
    B4 --> cart.php
    B4 --> checkout.php
    B5 --> payment.php
    B6 --> purchase_history.php

    C1 --> admin/manage_users.php
    C2 --> admin/manage_products.php
    C2 --> admin/add_product.php
    C2 --> admin/edit_product.php
    C3 --> admin/manage_contents.php
    C3 --> admin/add_content.php
    C3 --> admin/edit_content.php
    C4 --> admin/manage_orders.php
    C4 --> admin/order_detail.php
    C5 --> admin/manage_payments.php
    C5 --> admin/process_payment_verification.php
    C6 --> admin/dashboard.php
    C6 --> admin/get_sales_data.php
    C6 --> admin/get_payment_stats.php
    C7 --> includes/mailer.php
    C8 --> includes/db.php
    C10 --> admin/activity_logs.php

    style A fill:#f9f,stroke:#333,stroke-width:2px
    style A1 fill:#f9f,stroke:#333,stroke-width:2px
    style B fill:#bbf,stroke:#333,stroke-width:2px
    style C fill:#bfb,stroke:#333,stroke-width:2px
    style D fill:#fbb,stroke:#333,stroke-width:2px
    style B1 fill:#ccf,stroke:#333,stroke-width:1px
    style B2 fill:#ccf,stroke:#333,stroke-width:1px
    style B3 fill:#ccf,stroke:#333,stroke-width:1px
    style B4 fill:#ccf,stroke:#333,stroke-width:1px
    style B5 fill:#ccf,stroke:#333,stroke-width:1px
    style B6 fill:#ccf,stroke:#333,stroke-width:1px
    style C1 fill:#cfc,stroke:#333,stroke-width:1px
    style C2 fill:#cfc,stroke:#333,stroke-width:1px
    style C3 fill:#cfc,stroke:#333,stroke-width:1px
    style C4 fill:#cfc,stroke:#333,stroke-width:1px
    style C5 fill:#cfc,stroke:#333,stroke-width:1px
    style C6 fill:#cfc,stroke:#333,stroke-width:1px
    style C7 fill:#cfc,stroke:#333,stroke-width:1px
    style C8 fill:#cfc,stroke:#333,stroke-width:1px
    style C9 fill:#cfc,stroke:#333,stroke-width:1px
    style C10 fill:#cfc,stroke:#333,stroke-width:1px
    style D1 fill:#fcc,stroke:#333,stroke-width:1px
    style D2 fill:#fcc,stroke:#333,stroke-width:1px
    style D3 fill:#fcc,stroke:#333,stroke-width:1px
    style D4 fill:#fcc,stroke:#333,stroke-width:1px
    style D5 fill:#fcc,stroke:#333,stroke-width:1px
    style D6 fill:#fcc,stroke:#333,stroke-width:1px
```

# Gambaran Umum Proyek

Proyek ini adalah aplikasi web komprehensif, kemungkinan sistem e-commerce atau manajemen konten, yang dirancang untuk menyediakan platform yang kuat untuk operasi bisnis online. Dibangun dengan penekanan kuat pada modularitas dan skalabilitas, menggunakan PHP untuk logika backend dan kombinasi PHP serta kerangka kerja JavaScript modern (berpotensi Next.js) untuk frontend. Sistem ini memiliki panel administratif yang kuat untuk mengelola semua aspek aplikasi, di samping antarmuka publik yang ramah pengguna.

## Fitur Utama:

### Manajemen Pengguna:
- **Autentikasi Pengguna Aman**: Mengimplementasikan sistem autentikasi pengguna lengkap termasuk fungsionalitas pendaftaran, login, dan reset kata sandi.
- **Verifikasi Email**: Memastikan keaslian pengguna melalui proses verifikasi email.
- **Manajemen Pengguna Admin**: Bagian khusus dalam panel admin memungkinkan administrator untuk mengelola akun pengguna, termasuk menambahkan pengguna baru, mengedit profil yang ada, dan menghapus akun.

### Manajemen Produk:
- **Tampilan Produk Dinamis**: Produk ditampilkan secara dinamis di frontend, memungkinkan penelusuran yang mudah dan tampilan detail.
- **Kontrol Produk Admin Komprehensif**: Panel admin menyediakan operasi CRUD (Create, Read, Update, Delete) penuh untuk produk, termasuk fitur untuk mengunggah dan mengelola gambar produk.

### Manajemen Konten:
- **Tampilan Konten Fleksibel**: Mendukung tampilan konten dinamis di frontend, memungkinkan pembaruan dan pengelolaan halaman informasi dengan mudah.
- **Editor Konten Admin**: Administrator dapat menambahkan, mengedit, dan mengelola berbagai jenis konten melalui antarmuka khusus.

### Keranjang Belanja & Checkout:
- **Keranjang Belanja Intuitif**: Pengguna dapat dengan mudah menambahkan, memperbarui, dan menghapus item dari keranjang belanja mereka.
- **Proses Checkout yang Efisien**: Proses checkout multi-langkah memastikan pengalaman pembelian yang lancar dan efisien bagi pelanggan.

### Proses Pembayaran:
- **Gateway Pembayaran Terintegrasi**: Memfasilitasi pembayaran online melalui halaman pembayaran khusus.
- **Verifikasi Pembayaran Admin**: Panel admin mencakup alat yang kuat untuk mengelola pembayaran, memverifikasi bukti pembayaran yang dikirimkan oleh pengguna, dan melihat statistik dan laporan pembayaran terperinci.

### Manajemen Pesanan:
- **Pelacakan Pesanan Terpusat**: Administrator dapat secara efisien mengelola semua pesanan pelanggan dari panel admin.
- **Tampilan Detail Pesanan**: Menyediakan fungsionalitas untuk melihat informasi pesanan terperinci dan menghasilkan faktur yang dapat dicetak.

### Dasbor Admin & Pelaporan:
- **Dasbor Ikhtisar**: Dasbor terpusat menawarkan administrator gambaran umum cepat tentang metrik utama dan status sistem.
- **Analisis Penjualan dan Pembayaran**: Menghasilkan data penjualan komprehensif dan laporan statistik pembayaran untuk membantu dalam pengambilan keputusan bisnis.
- **Log Aktivitas**: Mempertahankan log terperinci dari tindakan administratif, meningkatkan akuntabilitas dan keamanan.

### Integrasi Basis Data:
- **Sistem Basis Data Relasional**: Menggunakan basis data SQL (kemungkinan MySQL) sebagai tulang punggung untuk menyimpan semua data aplikasi, termasuk profil pengguna, katalog produk, detail pesanan, catatan pembayaran, dan konten.
- **Skrip Manajemen Basis Data**: Mencakup skrip SQL untuk pengaturan basis data awal, pembuatan skema, dan pengisian data uji, memastikan penyebaran dan pengujian yang mudah.

### Layanan Email:
- **Notifikasi Otomatis**: Mengintegrasikan layanan email untuk mengirim notifikasi otomatis seperti tautan verifikasi akun, instruksi reset kata sandi, dan konfirmasi pesanan, meningkatkan komunikasi pengguna.

### Teknologi Frontend:
- **Arsitektur Frontend Hibrida**: Terutama menggunakan PHP untuk rendering sisi server dan logika aplikasi inti. Kehadiran `next.config.js`, `package.json`, dan `src/app` menunjukkan kerangka kerja frontend modern seperti Next.js (React) baik terintegrasi untuk komponen interaktif tertentu atau direncanakan untuk migrasi di masa mendatang, menawarkan pendekatan hibrida untuk memanfaatkan kekuatan kedua teknologi.
- **Gaya Modern**: Menggunakan file CSS (`public/css/styles.css`, `public/css/admin-modern.css`, `public/css/manage_payments.css`) untuk antarmuka pengguna yang bersih dan responsif.
- **Interaktivitas Sisi Klien**: Menggabungkan JavaScript (`public/js/script.js`) untuk interaktivitas sisi klien yang ditingkatkan dan pembaruan konten dinamis.

## Sorotan Struktur Proyek:

- **`admin/`**: Direktori khusus untuk semua fungsionalitas administratif, termasuk modul manajemen pengguna, produk, konten, pesanan, dan pembayaran.
- **`includes/`**: Berisi file PHP penting seperti koneksi basis data (`db.php`), konfigurasi global (`config.php`), penanganan email (`mailer.php`), dan komponen header/footer yang dapat digunakan kembali.
- **`public/`**: Menyimpan semua aset statis yang dapat diakses publik, termasuk stylesheet CSS, file JavaScript, dan media yang diunggah (misalnya, gambar produk, bukti pembayaran).
- **`src/`**: Kemungkinan root untuk aplikasi Next.js, berisi komponen React (`src/components/ui/`), gaya global (`src/app/globals.css`), dan fungsi utilitas (`src/lib/utils.ts`), menunjukkan alur kerja pengembangan frontend modern.
- **Direktori Root**: Berisi halaman PHP utama yang menghadap publik (misalnya, `index.php`, `products.php`, `cart.php`, `login.php`, `register.php`) dan berbagai skrip utilitas untuk pengaturan dan pengujian sistem.

Proyek ini dirancang untuk menjadi solusi full-stack yang kuat, skalabel, dan ramah pengguna, menyediakan antarmuka publik yang intuitif dan backend yang kuat untuk kontrol administratif yang komprehensif.

## Pengembang:
- **Chaerul Candra Pranugrah**

## Cara Menjalankan Proyek

Untuk menjalankan proyek ini di lingkungan lokal Anda, ikuti langkah-langkah berikut:

### Prasyarat

Pastikan Anda telah menginstal perangkat lunak berikut di sistem Anda:

*   **Web Server**: Apache atau Nginx (disarankan menggunakan Laragon, XAMPP, atau WAMP untuk lingkungan pengembangan lokal).
*   **PHP**: Versi 7.4 atau lebih tinggi.
*   **Composer**: Manajer dependensi untuk PHP.
*   **MySQL**: Sistem manajemen basis data.
*   **Node.js**: Versi 18 atau lebih tinggi.
*   **npm** atau **Yarn**: Manajer paket untuk Node.js.

### Instalasi

1.  **Clone Repositori:**
    ```bash
    git clone [URL_REPOSITORI_ANDA]
    cd sambal-mama-ana
    ```
    Ganti `[URL_REPOSITORI_ANDA]` dengan URL repositori GitHub Anda.

2.  **Instal Dependensi PHP:**
    Navigasi ke direktori proyek dan instal dependensi Composer:
    ```bash
    composer install
    ```

3.  **Konfigurasi Basis Data:**
    *   Buat basis data MySQL baru (misalnya, `sambal_mama_ana_db`).
    *   Impor skema basis data dari file `database.sql` atau `setup_database.sql` ke basis data yang baru dibuat. Anda bisa menggunakan phpMyAdmin atau klien MySQL lainnya.
        ```bash
        mysql -u username -p sambal_mama_ana_db < database.sql
        ```
        (Ganti `username` dengan username MySQL Anda dan `sambal_mama_ana_db` dengan nama basis data Anda.)

4.  **Konfigurasi Lingkungan:**
    *   Buat file `.env` di root proyek dengan menyalin `example.env` (jika ada) atau buat secara manual.
    *   Isi detail koneksi basis data dan konfigurasi lainnya. Contoh:
        ```
        DB_HOST=localhost
        DB_USER=root
        DB_PASS=
        DB_NAME=sambal_mama_ana_db
        ```

5.  **Instal Dependensi Frontend (Next.js):**
    Navigasi ke direktori proyek dan instal dependensi Node.js:
    ```bash
    npm install
    # atau
    yarn install
    ```

### Menjalankan Aplikasi

1.  **Mulai Web Server PHP:**
    Pastikan web server Anda (Apache/Nginx) dan PHP-FPM berjalan. Jika Anda menggunakan Laragon/XAMPP/WAMP, pastikan layanan Apache/Nginx dan MySQL telah dimulai. Proyek harus diakses melalui URL yang dikonfigurasi di web server Anda (misalnya, `http://localhost/sambal-mama-ana/`).

2.  **Mulai Server Pengembangan Next.js:**
    Untuk bagian frontend Next.js, jalankan server pengembangan:
    ```bash
    npm run dev
    # atau
    yarn dev
    ```
    Aplikasi frontend akan tersedia di `http://localhost:3000` (atau port lain yang dikonfigurasi).

Setelah langkah-langkah ini, proyek Anda seharusnya sudah berjalan dan dapat diakses melalui browser.
