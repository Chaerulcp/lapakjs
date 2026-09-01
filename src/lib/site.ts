/**
 * ⚙️ KONFIGURASI BRAND TERPUSAT — LapakJS
 *
 * Ini adalah file PERTAMA yang perlu kamu edit saat membangun tokomu sendiri
 * dengan LapakJS. Nama brand, tagline, ikon, kontak, dan teks hak cipta
 * semuanya bersumber dari sini — tidak perlu mengubek banyak komponen
 * hanya untuk rebranding.
 *
 * Tips kustomisasi lanjutan:
 * - Warna tema  → `src/app/globals.css` (bagian "Brand palette")
 * - Data contoh → `prisma/seed.mjs` (produk, konten, admin)
 * - SEO         → `src/app/layout.tsx` (otomatis mengikuti file ini)
 */
export const SITE = {
  /** Nama toko/brand — tampil di navbar, footer, email, dan judul halaman. */
  name: "LapakJS",

  /** Tagline singkat — tampil di bawah nama brand dan di meta halaman. */
  tagline: "Toko online untuk semua usahamu",

  /** Ikon emoji brand. Ganti dengan logo gambar (next/image) bila perlu. */
  emoji: "🛍️",

  /** Deskripsi panjang — dipakai untuk meta description & footer. */
  description:
    "LapakJS adalah template toko online open-source berbasis Next.js yang bisa dikustomisasi untuk usaha kecil maupun besar — lengkap dengan katalog produk, keranjang, checkout, verifikasi pembayaran, dan panel admin.",

  /** URL publik situs (untuk SEO & tautan). Ganti dengan domain tokomu. */
  url: "https://github.com/Chaerulcp/lapakjs",

  /** Kontak yang ditampilkan di footer toko. */
  contact: {
    email: "halo@lapakjs.dev",
    phone: "0812-3456-789 (WhatsApp)",
    address: "Yogyakarta, Indonesia",
  },

  /** Jam operasional yang ditampilkan di footer toko. */
  hours: ["Senin – Sabtu: 08.00 – 17.00 WIB", "Minggu: tutup"],
  hoursNote: "Pesanan sebelum pukul 14.00 diproses di hari yang sama.",

  /** Teks hak cipta di footer & email. */
  copyright: "Semua hak dilindungi.",
} as const;
