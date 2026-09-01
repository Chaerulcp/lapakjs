/**
 * Seed idempoten untuk LapakJS — template toko online open-source.
 * - Memastikan ada admin yang sudah terverifikasi untuk login.
 * - Menambahkan data demo (produk & konten) hanya jika tabel masih kosong.
 * - Aman dijalankan berulang kali; tidak pernah menghapus/mengubah data lama.
 *
 * Kustomisasi: ganti email/password admin lewat variabel environment
 * SEED_ADMIN_EMAIL dan SEED_ADMIN_PASSWORD, dan ubah data demo di bawah
 * sesuai produk tokomu sendiri.
 */
import { PrismaClient } from "@prisma/client";
import bcrypt from "bcryptjs";

const prisma = new PrismaClient();

const ADMIN_EMAIL = process.env.SEED_ADMIN_EMAIL || "admin@lapakjs.local";
const ADMIN_PASSWORD = process.env.SEED_ADMIN_PASSWORD || "LapakJS!2026";

async function main() {
  const admin = await prisma.user.findFirst({ where: { role: "admin" } });

  if (admin) {
    // Jangan sentuh password lama; cukup pastikan bisa login (terverifikasi).
    if (!admin.is_verified) {
      await prisma.user.update({ where: { id: admin.id }, data: { is_verified: true } });
      console.log(`[seed] Admin lama "${admin.email}" ditandai terverifikasi.`);
    } else {
      console.log(`[seed] Admin sudah ada: ${admin.email} (password lama tetap dipakai).`);
    }
  } else {
    const hash = await bcrypt.hash(ADMIN_PASSWORD, 10);
    await prisma.user.create({
      data: {
        nama: "Admin LapakJS",
        email: ADMIN_EMAIL,
        password: hash,
        alamat: "Alamat Toko (ubah sesuai kebutuhan)",
        no_hp: "080000000000",
        role: "admin",
        status: "active",
        is_verified: true,
      },
    });
    console.log(`[seed] Admin baru dibuat: ${ADMIN_EMAIL} / ${ADMIN_PASSWORD}`);
  }

  const [users, products, orders, contents] = await Promise.all([
    prisma.user.count(),
    prisma.product.count(),
    prisma.order.count(),
    prisma.content.count(),
  ]);

  // Data demo toko contoh (bertema sambal) — hanya jika tabel masih kosong.
  // Ganti dengan produk tokomu sendiri atau biarkan sebagai contoh.
  if (products === 0) {
    const demo = [
      { nama: "Sambal Bawang Original", kategori: "sambal-bawang", harga: 25000, harga_reseller: 20000, stok: 50, foto: "uploads/products/683dfdef85ce8.jpg", deskripsi: "Sambal bawang pedas nampol dengan bawang merah goreng renyah. Digoreng segar setiap hari tanpa pengawet. Cocok dengan nasi hangat, ayam goreng, dan tahu tempe." },
      { nama: "Sambal Terasi Bakar", kategori: "sambal-terasi", harga: 25000, harga_reseller: 20000, stok: 45, foto: "uploads/products/683dffa0e1ec6.jpg", deskripsi: "Perpaduan cabai pilihan dan terasi udang bakar asli. Gurih, wangi, dan pedasnya pas untuk lalapan serta ikan bakar." },
      { nama: "Sambal Ijo Padang", kategori: "sambal-ijo", harga: 25000, harga_reseller: 20000, stok: 40, foto: "uploads/products/683dffa78a5c6.png", deskripsi: "Cabai hijau segar ditumbuk kasar khas rumah makan Padang. Pedas segar dengan sedikit asam yang bikin nagih." },
      { nama: "Sambal Cumi Asin", kategori: "sambal-cumi", harga: 32000, harga_reseller: 26000, stok: 30, foto: "uploads/products/683dffbaeed1b.png", deskripsi: "Cumi asin kualitas premium dimasak bersama sambal bawang hingga meresap. Lauk praktis yang bikin tambah nasi." },
      { nama: "Sambal Bawang Cumi", kategori: "sambal-cumi", harga: 32000, harga_reseller: 26000, stok: 30, foto: "uploads/products/684837d981f46.jpg", deskripsi: "Best seller! Sambal bawang legendaris dengan potongan cumi segar melimpah. Pedasnya bikin nagih." },
      { nama: "Sambal Goreng Ati Ampela", kategori: "sambal-goreng", harga: 30000, harga_reseller: 24000, stok: 25, foto: "uploads/products/684837f4d70a4.png", deskripsi: "Ati ampela empuk dimasak sambal goreng dengan petai dan santan. Menu rumahan favorit untuk keluarga." },
      { nama: "Paket Hemat 3 Botol", kategori: "paket", harga: 70000, harga_reseller: 56000, stok: 20, foto: "uploads/products/product_1748875664_Screnshoot Image 30 Mei 2025, 18.12.40.png", deskripsi: "Paket hemat berisi 3 botol pilihan: Sambal Bawang, Sambal Terasi, dan Sambal Cumi. Hemat Rp5.000 dibanding beli satuan." },
    ];
    await prisma.product.createMany({ data: demo });
    console.log(`[seed] ${demo.length} produk contoh ditambahkan.`);
  }

  if (contents === 0) {
    await prisma.content.createMany({
      data: [
        { judul: "Rahasia Dapur Kami", isi: "Semua produk kami dibuat dari bahan pilihan berkualitas. Tanpa pengawet, tanpa pewarna buatan — hanya bahan terbaik dan resep yang terus kami jaga.", gambar: "uploads/products/683dfdef85ce8.jpg", penulis: "Tim Toko" },
        { judul: "Cara Pesan dan Pembayaran", isi: "Tambahkan produk favoritmu ke keranjang, isi alamat pengiriman, lalu pilih metode pembayaran transfer bank. Unggah bukti transfer di halaman pesanan, dan tim kami akan memverifikasi pembayaranmu secepat mungkin.", penulis: "Admin" },
      ],
    });
    console.log("[seed] Konten contoh ditambahkan.");
  }

  const finalCounts = await Promise.all([prisma.product.count(), prisma.content.count()]);
  console.log(`[seed] Data saat ini — users: ${users}, products: ${finalCounts[0]}, orders: ${orders}, contents: ${finalCounts[1]}`);
}

main()
  .catch((e) => {
    console.error("[seed] gagal:", e);
    process.exitCode = 1;
  })
  .finally(() => prisma.$disconnect());
