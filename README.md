# LapakJS

Template aplikasi toko online full-stack berbasis [Next.js 16](https://nextjs.org), [Prisma](https://prisma.io), dan MySQL. LapakJS menyediakan storefront publik, autentikasi dengan verifikasi email, keranjang dan checkout, verifikasi pembayaran manual, serta panel admin untuk pengelolaan produk, pesanan, pengguna, dan konten.

LapakJS dikembangkan dari aplikasi e-commerce produksi yang di-rebuild penuh dari codebase PHP legacy ke Next.js, lalu digeneralisasi menjadi template. Identitas toko (nama, tagline, kontak, jam operasional) diatur dari satu file konfigurasi, `src/lib/site.ts`, sehingga dapat diganti tanpa menyentuh komponen.

## Fitur

**Storefront**

- Katalog produk dengan kategori, pencarian, dan halaman detail produk.
- Keranjang belanja terikat sesi pengguna, tersinkron antar perangkat.
- Checkout dengan pilihan transfer bank, QRIS, e-wallet, dan COD.
- Unggah bukti transfer dan riwayat pesanan per pengguna.
- Halaman konten untuk artikel, pengumuman, atau resep.

**Akun dan peran**

- Registrasi dengan verifikasi email, login, dan alur lupa/atur ulang password.
- Tiga peran: `pelanggan`, `reseller` (harga khusus + pencatatan komisi), dan `admin`.

**Panel admin**

- Dashboard ringkasan penjualan, pesanan, dan produk.
- CRUD produk, konten, dan manajemen stok.
- Verifikasi bukti pembayaran, pembaruan status pesanan, dan pencatatan resi pengiriman.
- Manajemen pengguna serta log aktivitas admin.

**Developer experience**

- TypeScript end-to-end dengan Prisma Client yang ter-generate.
- Server Actions dan route handler REST dengan validasi Zod.
- Proteksi route berbasis peran dan rate limiter untuk endpoint sensitif.
- Sitemap dan robots dinamis; tema lewat CSS variables di Tailwind v4.

## Persyaratan

- Node.js 20 atau lebih baru
- MySQL 8 atau MariaDB
- Server SMTP untuk email transaksi (opsional di lokal — tanpa SMTP, email dicetak ke console)

## Memulai

```bash
# 1. Pasang dependensi
npm install

# 2. Salin contoh konfigurasi, lalu isi nilainya
cp .env.example .env

# 3. Buat skema database
npx prisma migrate dev

# 4. Isi data contoh (opsional)
npm run db:seed

# 5. Jalankan server pengembangan
npm run dev
```

Aplikasi tersedia di `http://localhost:3000`; panel admin di `/admin`.

## Kredensial seed

`npm run db:seed` bersifat idempoten: admin dibuat hanya jika belum ada, dan data demo (produk serta konten) hanya ditambahkan saat tabel masih kosong. Kredensial bawaan admin:

| Email | Password |
| --- | --- |
| `admin@lapakjs.local` | `LapakJS!2026` |

Keduanya dapat diubah lewat variabel `SEED_ADMIN_EMAIL` dan `SEED_ADMIN_PASSWORD`. Ganti kredensial ini sebelum deploy produksi.

## Kustomisasi

Sebagian besar penyesuaian merek cukup dilakukan pada satu file:

| Yang diubah | Lokasi |
| --- | --- |
| Nama toko, tagline, deskripsi, kontak, jam operasional | `src/lib/site.ts` |
| Warna dan tipografi tema | `src/app/globals.css` (CSS variables) |
| Favicon | `public/favicon.ico` |
| Label kategori produk | `kategoriLabel()` di `src/components/site/ProductCard.tsx` |
| Data contoh produk dan konten | `prisma/seed.cjs` |
| Domain publik (email, sitemap, robots) | `APP_URL` di `.env` |

Metadata SEO di `src/app/layout.tsx` otomatis mengikuti nilai di `src/lib/site.ts`.

## Variabel lingkungan

| Variabel | Wajib | Keterangan |
| --- | --- | --- |
| `DATABASE_URL` | Ya | Connection string MySQL, contoh `mysql://root:@localhost:3306/lapakjs` |
| `AUTH_SECRET` | Ya | Secret sesi NextAuth; buat dengan `npx auth secret` |
| `AUTH_TRUST_HOST` | Ya | `true` saat berjalan di balik proxy atau di lokal |
| `APP_URL` | Ya | URL publik aplikasi untuk tautan email dan sitemap |
| `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS`, `SMTP_SECURE` | Tidak | Kredensial SMTP; jika kosong, email dicetak ke console |
| `MAIL_FROM` | Tidak | Alamat pengirim email, contoh `Nama Toko <noreply@tokokamu.com>` |
| `SEED_ADMIN_EMAIL`, `SEED_ADMIN_PASSWORD` | Tidak | Kredensial admin saat menjalankan `npm run db:seed` |

Contoh nilai tersedia di `.env.example`.

## Skrip

| Perintah | Keterangan |
| --- | --- |
| `npm run dev` | Menjalankan server pengembangan |
| `npm run build` | Build produksi |
| `npm run start` | Menjalankan build produksi |
| `npm run lint` | Memeriksa gaya kode dengan ESLint |
| `npm run prisma:migrate` | Membuat/menerapkan migrasi skema |
| `npm run prisma:generate` | Generate Prisma Client |
| `npm run prisma:studio` | Membuka Prisma Studio |
| `npm run db:seed` | Mengisi data contoh dan admin |

## Struktur proyek

```
src/
├── app/
│   ├── (auth)/        # login, daftar, verifikasi email, reset password
│   ├── (site)/        # storefront: beranda, produk, keranjang, checkout, pesanan, konten
│   ├── admin/         # panel admin
│   ├── api/           # route handler (auth, pesanan, pembayaran)
│   ├── layout.tsx     # root layout dan metadata SEO
│   ├── globals.css    # tema (CSS variables)
│   ├── robots.ts
│   └── sitemap.ts
├── components/
│   ├── admin/         # komponen panel admin
│   ├── site/          # komponen storefront
│   └── ui/            # primitif shadcn/ui
├── lib/
│   ├── site.ts        # konfigurasi brand terpusat
│   ├── db.ts          # klien Prisma
│   ├── mailer.ts      # pengiriman email transaksional
│   └── ...            # cart, format, password, uploads, activity
└── types/             # tipe bersama
prisma/
├── schema.prisma      # skema database
└── seed.cjs           # data contoh
public/uploads/        # media hasil unggahan
```

## Deployment

- Jalankan `npx prisma migrate deploy` saat rilis, bukan `migrate dev`.
- Generate `AUTH_SECRET` baru untuk setiap lingkungan.
- Atur `APP_URL` ke domain publik; tautan verifikasi email, sitemap, dan robots mengikutinya.
- Pastikan folder `public/uploads` dapat ditulis oleh proses aplikasi.
- Tanpa SMTP yang terkonfigurasi, email verifikasi tidak terkirim — isi variabel SMTP di produksi.

## Roadmap

- Integrasi payment gateway (Midtrans/Xendit) sebagai pengganti verifikasi manual
- Kupon dan diskon
- Pengiriman: integrasi API kurir dan pelacakan resi
- Dukungan database lain (PostgreSQL)
- Mode gelap storefront
- Dokumentasi arsitektur di GitHub Wiki

## Kontribusi

1. Fork repo ini dan buat branch fitur (`git checkout -b feature/nama-fitur`).
2. Pastikan `npm run lint` dan `npm run build` lolos.
3. Ajukan pull request dengan deskripsi perubahan.

Untuk bug atau usulan fitur, silakan buka issue.

## Lisensi

Berlisensi [MIT](LICENSE) — bebas digunakan untuk proyek pribadi maupun komersial.

LapakJS bermula dari aplikasi toko sambal rumahan; produk pada data seed dipertahankan sebagai contoh pengisian katalog dan dapat diganti sepenuhnya.

