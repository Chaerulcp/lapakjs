<div align="center">

# 🛍️ LapakJS

**Template toko online open-source berbasis Next.js — bisa dikustomisasi untuk usaha kecil maupun besar.**

Satu codebase, siap jadi toko onlinemu sendiri: katalog produk, keranjang, checkout dengan verifikasi transfer bank, hingga panel admin lengkap.

[![Next.js](https://img.shields.io/badge/Next.js-16-black?logo=nextdotjs)](https://nextjs.org)
[![React](https://img.shields.io/badge/React-19-61dafb?logo=react)](https://react.dev)
[![TypeScript](https://img.shields.io/badge/TypeScript-Strict-3178c6?logo=typescript&logoColor=white)](https://www.typescriptlang.org)
[![Prisma](https://img.shields.io/badge/Prisma-ORM-2d3748?logo=prisma&logoColor=white)](https://www.prisma.io)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479a1?logo=mysql&logoColor=white)](https://www.mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-38bdf8?logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)

</div>

---

## ✨ Tentang Proyek

**LapakJS** adalah starter kit e-commerce full-stack yang lahir dari proyek nyata: toko online **Sambal Mama Ana**, hasil rebuild penuh dari aplikasi PHP legacy menjadi satu aplikasi Next.js modern yang ter-tipe penuh. Karena dibangun untuk toko sungguhan, setiap fiturnya sudah teruji untuk kebutuhan jualan sehari-hari — bukan sekadar demo.

Kini LapakJS di-open-source-kan agar siapa pun — UMKM, reseller, hingga bisnis besar — bisa punya toko online sendiri dengan cepat:

1. **Clone** repo ini,
2. **Ubah satu file** (`src/lib/site.ts`) untuk mengganti nama & identitas toko,
3. **Jalankan** — tokomu siap menerima pesanan.

> 🌶️ *Fun fact: seluruh fitur di sini dipakai produksi oleh toko sambal rumahan sebelum dibuka untuk publik.*

## 🧰 Fitur Utama

### Untuk Pembeli (Storefront)
- 🗂️ **Katalog produk** dengan pencarian, filter kategori, dan paginasi
- 🛒 **Keranjang belanja** (tersimpan di database, bukan cookie — sinkron lintas perangkat)
- 💳 **Checkout dengan verifikasi transfer bank** — pembeli unggah bukti bayar, admin menyetujui
- 📦 **Pelacakan status pesanan** (pending → paid → processing → shipped → done)
- ⭐ **Ulasan & rating produk** dari pembeli terverifikasi
- ✉️ **Verifikasi email & reset password** dengan tautan aman sekali pakai
- 💰 **Harga khusus reseller** — otomatis tampil untuk akun ber-role `reseller`

### Untuk Penjual (Panel Admin)
- 📊 **Dashboard** dengan ringkasan penjualan, pesanan terbaru, dan statistik
- 🏷️ **CRUD produk** lengkap: harga normal & harga reseller, stok, kategori, upload foto
- ✅ **Verifikasi pembayaran** pesanan masuk
- 👥 **Manajemen pengguna & role** (admin / reseller / pelanggan)
- 📝 **Manajemen konten** (artikel/cerita toko) dan **testimoni pelanggan**
- 📈 **Log aktivitas** untuk audit trail

### Fondasi Teknis
- 🔒 NextAuth v5, password di-hash dengan bcrypt, validasi Zod di setiap input
- 🖼️ Optimasi gambar otomatis via `next/image` + Sharp
- 🔍 SEO siap pakai: metadata dinamis, Open Graph, JSON-LD structured data
- 🧩 UI konsisten dengan Tailwind CSS v4 + shadcn/ui
- 🗄️ Skema Prisma yang kompatibel dengan struktur database MySQL warisan (legacy)

## 🧱 Teknologi

| Layer      | Teknologi                                    |
| ---------- | -------------------------------------------- |
| Framework  | Next.js 16 (App Router, Server Components)   |
| Bahasa     | TypeScript (strict)                          |
| Database   | MySQL / MariaDB                              |
| ORM        | Prisma 6                                     |
| Auth       | NextAuth v5 (beta) + bcrypt                  |
| Styling    | Tailwind CSS v4 + shadcn/ui                  |
| Email      | Nodemailer (fallback: console di mode dev)   |
| Grafik     | Recharts (panel admin)                       |

## 🚀 Memulai (Quick Start)

### Prasyarat
- Node.js 20+
- MySQL / MariaDB (Laragon, XAMPP, Docker, atau cloud)

### Langkah-langkah

```bash
# 1. Clone repository
git clone https://github.com/Chaerulcp/lapakjs.git
cd lapakjs

# 2. Pasang dependensi
npm install

# 3. Siapkan environment
copy .env.example .env        # Linux/macOS: cp .env.example .env

# 4. Buat database kosong bernama `lapakjs` di MySQL, lalu jalankan migrasi
npx prisma migrate dev

# 5. Isi data awal (admin + produk & konten demo)
npm run db:seed

# 6. Jalankan di mode pengembangan
npm run dev
```

Buka http://localhost:3000 — toko contoh sudah berisi produk demo dan siap dijelajahi.

### Login admin default (hasil seed)

| Field    | Nilai                     |
| -------- | ------------------------- |
| Email    | `admin@lapakjs.local`     |
| Password | `LapakJS!2026`            |

> ⚠️ Ganti kredensial ini di produksi. Kamu bisa menimpanya lewat variabel `SEED_ADMIN_EMAIL` dan `SEED_ADMIN_PASSWORD` sebelum menjalankan seed.

## 🎨 Rebranding: Cara Menjadikannya Tokomu

LapakJS dirancang agar bisa dikustomisasi **tanpa menyentuh banyak file**:

| Yang ingin diubah              | File yang diedit                          |
| ------------------------------ | ----------------------------------------- |
| Nama, tagline, kontak, ikon    | `src/lib/site.ts` ← **mulai dari sini**   |
| Warna tema & palet brand       | `src/app/globals.css` (bagian `@theme`)   |
| Produk & konten contoh         | `prisma/seed.cjs`                         |
| Label kategori produk          | `src/components/site/ProductCard.tsx`     |
| Domain sitemap                 | `public/robots.txt`                       |
| Judul & deskripsi global (SEO) | otomatis mengikuti `src/lib/site.ts`      |

Contoh: ubah `name` di `src/lib/site.ts` menjadi `"Kopi Senja"`, ganti emoji jadi ☕, sesuaikan palet warna — dan seluruh navbar, footer, email, judul halaman, hingga panel admin langsung mengikuti.

## 🔐 Variabel Environment

| Variabel              | Wajib | Keterangan                                              |
| --------------------- | :---: | ------------------------------------------------------- |
| `DATABASE_URL`        |  ✅   | URL koneksi MySQL (`mysql://user:pass@host:3306/db`)    |
| `AUTH_SECRET`         |  ✅   | Secret NextAuth — buat dengan `npx auth secret`         |
| `AUTH_TRUST_HOST`     |  ✅   | Set `true` saat deploy di belakang reverse proxy        |
| `APP_URL`             |  ✅   | URL publik aplikasi (untuk tautan di email)             |
| `SMTP_HOST/PORT/USER/PASS/SECURE` | — | Konfigurasi SMTP; jika kosong, email dicetak ke console |
| `MAIL_FROM`           |   —   | Nama & alamat pengirim email                            |
| `SEED_ADMIN_EMAIL`    |   —   | Email admin saat seeding                                |
| `SEED_ADMIN_PASSWORD` |   —   | Password admin saat seeding                             |

## 📜 Skrip npm

| Skrip                     | Fungsi                                        |
| ------------------------- | --------------------------------------------- |
| `npm run dev`             | Server pengembangan di http://localhost:3000  |
| `npm run build`           | Build produksi                                |
| `npm run start`           | Jalankan build produksi                       |
| `npm run lint`            | Pemeriksaan ESLint                            |
| `npm run db:seed`         | Seed admin + data demo (idempoten)            |
| `npm run prisma:migrate`  | Jalankan migrasi skema                        |
| `npm run prisma:studio`   | Buka Prisma Studio (GUI database)             |

## 🗂️ Struktur Proyek

```
├── prisma/
│   ├── schema.prisma      # Skema database (kompatibel DB legacy)
│   └── seed.cjs           # Seed admin + data demo
├── public/
│   └── uploads/           # Berkas unggahan (foto produk, bukti bayar)
├── src/
│   ├── app/
│   │   ├── (site)/        # Storefront: beranda, produk, keranjang, checkout, pesanan
│   │   ├── (auth)/        # Login, register, verifikasi, reset password
│   │   ├── admin/         # Panel admin (guard role di layout + server action)
│   │   └── api/           # Route API (auth, unggahan, dsb.)
│   ├── components/        # UI: site/, admin/, ui/ (shadcn)
│   └── lib/               # Utilitas: site.ts (brand!), db, auth, mailer, format
├── .env.example           # Contoh konfigurasi environment
└── LICENSE                # MIT
```

## ☁️ Deploy ke Produksi

1. **Database** — gunakan MySQL terkelola (RDS, PlanetScale, Aiven, dsb.) lalu jalankan `npx prisma migrate deploy`.
2. **Hosting** — Vercel adalah target paling mudah:
   ```bash
   npx vercel --prod
   ```
   Set semua variabel environment dari `.env.example` di dashboard Vercel.
3. **Penyimpanan berkas** — folder `public/uploads` tidak cocok untuk serverless jangka panjang; pindahkan unggahan ke object storage (S3/R2) atau gunakan penyimpanan disk pada VPS (Docker/PM2).
4. **Email** — isi kredensial SMTP produksi agar verifikasi email & reset password terkirim sungguhan.

## 🗺️ Roadmap

- [ ] Integrasi payment gateway (Midtrans/Xendit)
- [ ] Kupon & diskon
- [ ] Perhitungan ongkos kirim otomatis (RajaOngkir/Biteship)
- [ ] Notifikasi WhatsApp/email saat status pesanan berubah
- [ ] Dukungan multi-bahasa (i18n)
- [ ] Mode katalog tanpa checkout (tombol langsung ke WhatsApp)

## 🤝 Kontribusi

Kontribusi sangat diterima! Silakan:

1. Fork repo ini dan buat branch fitur (`git checkout -b feature/keren-banget`).
2. Commit perubahanmu dengan pesan yang jelas (`git commit -m "feat: tambah kupon diskon"`).
3. Push ke branch dan buka Pull Request.

Untuk perubahan besar, buka dulu Issue untuk berdiskusi agar sejalan dengan arah proyek.

## 📄 Lisensi

Didistribusikan di bawah **Lisensi MIT** — bebas dipakai untuk proyek pribadi, komersial, maupun klien. Lihat [LICENSE](./LICENSE) untuk detail lengkap.

## 🙏 Kredit

LapakJS dimulai sebagai rebuild dari toko **Sambal Mama Ana** (PHP legacy → Next.js). Terima kasih untuk semua yang sudah mencoba, melaporkan bug, dan memberi masukan.

---

<div align="center">

**Suka dengan proyek ini? Beri ⭐ di repo ini agar lebih banyak orang menemukannya.**

Dibuat dengan ❤️ oleh [chaerulcp](https://github.com/Chaerulcp) · Lisensi MIT

</div>

