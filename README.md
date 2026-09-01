<div align="center">

# 🌶️ Sambal Mama Ana

**Toko online resmi Sambal Mama Ana — sambal rumahan asli, dibuat dengan cinta.**

Sambal Bawang · Sambal Terasi · Sambal Ijo · Sambal Cumi

![Next.js](https://img.shields.io/badge/Next.js-16-black?logo=next.js)
![React](https://img.shields.io/badge/React-19-61DAFB?logo=react)
![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?logo=typescript)
![Prisma](https://img.shields.io/badge/Prisma-6-2D3748?logo=prisma)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4-38B2AC?logo=tailwindcss)

Website e-commerce full-stack hasil migrasi penuh dari versi PHP lama ke **Next.js App Router** — lengkap dengan katalog produk, keranjang & checkout, konfirmasi pembayaran manual, konten/blog, program reseller, dan panel admin.

[Fitur](#fitur-utama) · [Tech Stack](#tech-stack) · [Instalasi](#instalasi) · [Struktur](#struktur-proyek) · [Deployment](#catatan-deployment)

</div>

---

## ✨ Tentang Proyek

**Sambal Mama Ana** adalah toko sambal rumahan yang menjual varian sambal bawang, terasi, ijo, dan cumi. Proyek ini merupakan **rebuild penuh** dari aplikasi PHP legacy menjadi satu aplikasi **Next.js full-stack** yang modern, ter-tipe penuh, dan siap produksi.

Data lama (produk, pengguna, konten) dipertahankan melalui skema Prisma yang dipetakan ke struktur database MySQL warisan, sehingga riwayat pesanan dan akun pelanggan tetap utuh setelah migrasi.

## Fitur Utama

### 🛍️ Toko (publik)
- **Katalog produk** — kategori, pencarian, harga pelanggan & harga khusus reseller, dan indikator stok.
- **Detail produk** — deskripsi, galeri, kadar pedas (*chili meter*), dan tombol tambah ke keranjang.
- **Keranjang belanja** — tersimpan di cookie server-side, tahan sesi, dengan penghitung total otomatis.
- **Checkout** — pengiriman ke seluruh Indonesia dengan perhitungan ongkir.
- **Konfirmasi pembayaran manual** — pelanggan unggah bukti transfer; admin memverifikasi.
- **Pelacakan status pesanan** — menunggu → diproses → dikirim → selesai (atau dibatalkan).
- **Konten/blog** — artikel, resep, dan cerita di balik sambal.
- **Newsletter** — langganan email pelanggan.

### 🔐 Autentikasi
- **Registrasi + verifikasi email** (tautan sekali pakai) dan kirim ulang tautan.
- **Login / logout** berbasis sesi JWT (NextAuth v5).
- **Lupa & reset password** via email.
- **Kompatibilitas password lama** — hash bcrypt versi PHP (`$2y$`) tetap berfungsi.

### 👑 Panel Admin (`/admin`)
- **Dashboard** — statistik penjualan, ringkasan pesanan, dan grafik pendapatan (Recharts).
- **Kelola produk** — tambah, ubah, hapus, dan unggah foto.
- **Kelola pesanan** — detail pesanan dan ubah status.
- **Verifikasi pembayaran** — tinjau bukti transfer, setujui/tolak.
- **Kelola pengguna & konten** — manajemen akun pelanggan dan artikel.
- **Log aktivitas** — jejak audit seluruh aksi admin.

## Tech Stack

| Kategori     | Teknologi                                                    |
| ------------ | ------------------------------------------------------------ |
| Framework    | Next.js 16 (App Router) + React 19                           |
| Bahasa       | TypeScript 5                                                 |
| Database     | MySQL 8 + Prisma ORM 6                                       |
| Autentikasi  | NextAuth v5 (beta) + bcryptjs                                |
| Styling      | Tailwind CSS v4 + shadcn/ui                                  |
| Validasi     | Zod + React Hook Form                                        |
| Grafik admin | Recharts                                                     |
| Email        | Nodemailer (SMTP; fallback console di dev)                   |
| Font         | Plus Jakarta Sans · Bricolage Grotesque · JetBrains Mono     |

## Prasyarat

- **Node.js** ≥ 20
- **MySQL** 8.x (lokal via Laragon/XAMPP, atau server sendiri)
- **npm**

## Instalasi

```bash
# 1. Pasang dependensi
npm install

# 2. Siapkan konfigurasi environment
copy .env.example .env        # macOS/Linux: cp .env.example .env
# lalu edit .env: isi DATABASE_URL dan generate AUTH_SECRET dengan `npx auth secret`

# 3. Buat & sinkronkan database
npx prisma migrate dev        # membuat tabel dari schema Prisma
# — atau, impor dump lama: buat database `sambal_mama_ana`,
#   lalu impor database.sql via phpMyAdmin/CLI

# 4. Generate Prisma Client
npm run prisma:generate

# 5. (Opsional) Seed data awal — membuat akun admin default
npm run db:seed

# 6. Jalankan mode pengembangan
npm run dev
```

Buka **http://localhost:3000** — situs siap dipakai.

### Akun seed default

| Field    | Nilai                          |
| -------- | ------------------------------ |
| Email    | `admin@sambalmamaana.com`      |
| Password | `MamaAna!2026`                 |

> **Catatan:** Seed memakai ulang akun admin lama bila sudah ada di database — password versi PHP lama tetap berfungsi berkat kompatibilitas bcrypt `$2y$`. Password `MamaAna!2026` hanya untuk pengujian lokal; **ganti sebelum produksi** lewat panel Pengguna atau langsung di database.

## Konfigurasi Environment

Salin `.env.example` menjadi `.env` lalu sesuaikan. Tabel di bawah merangkum setiap variabel.

| Variabel | Wajib | Keterangan |
| -------- | :---: | ---------- |
| `DATABASE_URL` | ✅ | Connection string MySQL untuk Prisma |
| `AUTH_SECRET` | ✅ | Secret NextAuth — buat dengan `npx auth secret` |
| `AUTH_TRUST_HOST` | ✅ | `true` (diperlukan di luar Vercel) |
| `APP_URL` | — | Base URL untuk tautan di email (default `http://localhost:3000`) |
| `SMTP_HOST` / `SMTP_PORT` / `SMTP_USER` / `SMTP_PASS` / `SMTP_SECURE` | — | Kredensial SMTP. Jika kosong, email hanya dicetak ke console (mode dev) |
| `MAIL_FROM` | — | Alamat pengirim email |
| `SEED_ADMIN_EMAIL` / `SEED_ADMIN_PASSWORD` | — | Override akun admin saat seeding |

## Scripts

| Perintah | Keterangan |
| -------- | ---------- |
| `npm run dev` | Jalankan server pengembangan (http://localhost:3000) |
| `npm run build` | Build produksi |
| `npm start` | Jalankan server produksi |
| `npm run lint` | Periksa gaya kode (ESLint) |
| `npm run db:seed` | Seed database (admin + data awal) |
| `npm run prisma:generate` | Generate Prisma Client |
| `npm run prisma:migrate` | Jalankan migrasi skema (dev) |
| `npm run prisma:studio` | Buka Prisma Studio (GUI database) |

## Alur Pesanan

1. Pelanggan menambah produk ke **keranjang** (tersimpan di cookie server-side).
2. Saat **checkout**, total dihitung ulang di server dan stok divalidasi.
3. Pesanan dibuat dengan status **menunggu**; stok berkurang otomatis.
4. Pelanggan **unggah bukti transfer** lewat halaman konfirmasi pembayaran.
5. Admin meninjau bukti di **panel admin** lalu menyetujui/menolak.
6. Setelah diverifikasi, pesanan beranjak ke status berikutnya hingga **selesai**.

## Struktur Proyek

```
prisma/                 # Skema database & seed
public/uploads/         # Berkas unggahan (foto produk, bukti pembayaran)
src/
├── app/
│   ├── (auth)/         # Halaman login, register, verifikasi, reset password
│   ├── (site)/         # Situs publik: beranda, produk, konten, keranjang,
│   │                   #   checkout, pesanan
│   ├── admin/          # Panel admin
│   ├── api/            # API routes (auth, pesanan, pembayaran)
│   ├── icon.svg        # Ikon situs (favicon)
│   ├── not-found.tsx   # Halaman 404
│   └── sitemap.ts      # Sitemap dinamis (rute + produk + konten)
├── components/
│   ├── admin/          # Komponen panel admin
│   ├── site/           # Komponen situs publik
│   └── ui/             # Komponen dasar shadcn/ui
├── lib/                # Utilitas: db, cart, mailer, uploads, format, activity
└── auth.ts             # Konfigurasi NextAuth
```

## SEO

- `public/robots.txt` — mengizinkan crawling situs publik, memblokir `/admin`, `/api`, dan halaman privat (keranjang, checkout, akun).
- `src/app/sitemap.ts` — menghasilkan sitemap dinamis dari database.

## Catatan Upload

Foto produk dan bukti pembayaran disimpan di `public/uploads/` (subfolder `products/` dan `payments/`). Nama berkas dipertahankan agar tautan lama tetap valid setelah migrasi.

## Catatan Deployment

Sebelum menaikkan ke produksi:

1. **Ganti kredensial admin** — setel ulang password admin lama, atau pakai `SEED_ADMIN_EMAIL` / `SEED_ADMIN_PASSWORD`.
2. **Set `APP_URL`** sesuai domain asli (dipakai untuk tautan di email).
3. **Konfigurasi SMTP nyata** (`SMTP_HOST`, dll.) agar email verifikasi & reset terkirim.
4. **Domain di `robots.txt`** — ganti dengan domain produksi.
5. **Pastikan `public/uploads/` dapat ditulis** oleh proses server di lingkungan produksi.

## Migrasi dari PHP Legacy

Versi PHP lama diarsipkan di tag Git **`v1.0-php-legacy`**; rebuild Next.js penuh ditandai **`v2.0-nextjs`**. Selama migrasi:

- Beberapa gambar legacy berukuran 0-byte ditambal dengan salinan gambar valid (nama berkas dipertahankan).
- Hash password lama (`$2y$`) dinormalisasi agar kompatibel dengan bcrypt.
- Seed `is_verified` dikirim sebagai boolean (`true`), sesuai ekspektasi Prisma untuk kolom `TinyInt`.

---

<div align="center">

Dibuat dengan 🌶️ oleh tim Sambal Mama Ana

</div>