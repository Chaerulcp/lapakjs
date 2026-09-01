"use server";

/**
 * Server actions panel admin — semua mutasi toko (produk, pesanan, pembayaran,
 * pengguna, konten) terpusat di sini. Setiap aksi:
 * 1. Memverifikasi sesi & role admin (defense in depth, selain guard layout).
 * 2. Memvalidasi input dengan zod.
 * 3. Mencatat jejak audit via logActivity (activity_logs).
 * 4. Merevalidasi path terdampak agar UI langsung segar.
 */

import { revalidatePath } from "next/cache";
import { z } from "zod";
import { auth, signOut } from "@/auth";
import { prisma } from "@/lib/db";
import { ACTIVITY_TYPES, logActivity } from "@/lib/activity";
import { saveUploadedFile, UploadError } from "@/lib/uploads";

/** Hasil aksi — dipakai form client untuk umpan balik (toast). */
export type ActionResult = { ok: boolean; message: string };

type AdminIdentity = { id: number; nama: string; email: string };

const fail = (message: string): ActionResult => ({ ok: false, message });
const ok = (message: string): ActionResult => ({ ok: true, message });

/** Hanya admin terautentikasi yang boleh menjalankan aksi. */
async function requireAdmin(): Promise<AdminIdentity | null> {
  const session = await auth();
  if (!session?.user || session.user.role !== "admin") return null;
  return {
    id: Number(session.user.id),
    nama: session.user.name ?? "Admin",
    email: session.user.email ?? "",
  };
}

function readId(formData: FormData, key = "id"): number {
  const num = Number(formData.get(key));
  return Number.isInteger(num) && num > 0 ? num : NaN;
}

function isFileWithSize(value: FormDataEntryValue | null): value is File {
  return value instanceof File && value.size > 0;
}

/** Error prisma foreign-key (mis. produk masih dipakai pesanan). */
function isFkError(err: unknown): boolean {
  return (
    typeof err === "object" &&
    err !== null &&
    "code" in err &&
    (err as { code?: string }).code === "P2003"
  );
}

/* =========================================================================
 * PRODUK
 * ========================================================================= */

const productSchema = z.object({
  nama: z.string().trim().min(3, "Nama produk minimal 3 karakter.").max(150),
  kategori: z.string().trim().min(2, "Kategori wajib diisi.").max(80),
  deskripsi: z.string().trim().min(10, "Deskripsi minimal 10 karakter.").max(5000),
  harga: z.coerce.number().int().min(0, "Harga tidak boleh negatif."),
  harga_reseller: z.coerce.number().int().min(0, "Harga reseller tidak boleh negatif."),
  stok: z.coerce.number().int().min(0, "Stok tidak boleh negatif.").max(1_000_000),
});

/** Tambah produk baru. FormData: field produk + file "foto" (wajib). */
export async function createProductAction(formData: FormData): Promise<ActionResult> {
  const admin = await requireAdmin();
  if (!admin) return fail("Akses ditolak. Halaman ini khusus admin.");

  const parsed = productSchema.safeParse(Object.fromEntries(formData));
  if (!parsed.success) {
    return fail(parsed.error.issues[0]?.message ?? "Data produk tidak valid.");
  }

  const file = formData.get("foto");
  if (!isFileWithSize(file)) {
    return fail("Foto produk wajib diunggah (JPG/PNG/WEBP, maks. 5 MB).");
  }

  let foto: string;
  try {
    foto = await saveUploadedFile(file, "products", `product_${parsed.data.nama}`);
  } catch (err) {
    if (err instanceof UploadError) return fail(err.message);
    console.error("[admin] gagal menyimpan foto produk:", err);
    return fail("Gagal menyimpan foto produk. Coba lagi.");
  }

  try {
    const product = await prisma.product.create({ data: { ...parsed.data, foto } });
    await logActivity(
      admin.id,
      ACTIVITY_TYPES.admin_product_created,
      `Admin menambahkan produk "${product.nama}" (ID ${product.id}).`
    );
    revalidatePath("/admin/produk");
    revalidatePath("/produk", "layout");
    return ok(`Produk "${product.nama}" berhasil ditambahkan.`);
  } catch (err) {
    console.error("[admin] gagal membuat produk:", err);
    return fail("Gagal menyimpan produk. Coba lagi.");
  }
}

/**
 * Perbarui produk. FormData: id + field produk + file "foto" (opsional —
 * bila kosong, foto lama dipertahankan).
 */
export async function updateProductAction(formData: FormData): Promise<ActionResult> {
  const admin = await requireAdmin();
  if (!admin) return fail("Akses ditolak. Halaman ini khusus admin.");

  const id = readId(formData);
  if (!Number.isFinite(id)) return fail("ID produk tidak valid.");

  const parsed = productSchema.safeParse(Object.fromEntries(formData));
  if (!parsed.success) {
    return fail(parsed.error.issues[0]?.message ?? "Data produk tidak valid.");
  }

  const existing = await prisma.product.findUnique({ where: { id } });
  if (!existing) return fail("Produk tidak ditemukan.");

  let foto = existing.foto;
  const file = formData.get("foto");
  if (isFileWithSize(file)) {
    try {
      foto = await saveUploadedFile(file, "products", `product_${parsed.data.nama}`);
    } catch (err) {
      if (err instanceof UploadError) return fail(err.message);
      console.error("[admin] gagal menyimpan foto produk:", err);
      return fail("Gagal menyimpan foto produk. Coba lagi.");
    }
  }

  try {
    await prisma.product.update({ where: { id }, data: { ...parsed.data, foto } });
    await logActivity(
      admin.id,
      ACTIVITY_TYPES.admin_product_updated,
      `Admin memperbarui produk "${parsed.data.nama}" (ID ${id}).`
    );
    revalidatePath("/admin/produk");
    revalidatePath("/produk", "layout");
    return ok(`Produk "${parsed.data.nama}" berhasil diperbarui.`);
  } catch (err) {
    console.error("[admin] gagal memperbarui produk:", err);
    return fail("Gagal memperbarui produk. Coba lagi.");
  }
}

/** Hapus produk. FormData: id. Gagal bila produk masih dipakai pesanan/testimoni. */
export async function deleteProductAction(formData: FormData): Promise<ActionResult> {
  const admin = await requireAdmin();
  if (!admin) return fail("Akses ditolak. Halaman ini khusus admin.");

  const id = readId(formData);
  if (!Number.isFinite(id)) return fail("ID produk tidak valid.");

  const existing = await prisma.product.findUnique({ where: { id } });
  if (!existing) return fail("Produk tidak ditemukan.");

  try {
    await prisma.product.delete({ where: { id } });
    await logActivity(
      admin.id,
      ACTIVITY_TYPES.admin_product_deleted,
      `Admin menghapus produk "${existing.nama}" (ID ${id}).`
    );
    revalidatePath("/admin/produk");
    revalidatePath("/produk", "layout");
    return ok(`Produk "${existing.nama}" dihapus.`);
  } catch (err) {
    if (isFkError(err)) {
      return fail(
        "Produk sudah dipakai oleh pesanan/testimoni sehingga tidak bisa dihapus. Nol-kan stok sebagai gantinya."
      );
    }
    console.error("[admin] gagal menghapus produk:", err);
    return fail("Gagal menghapus produk. Coba lagi.");
  }
}

/* =========================================================================
 * PESANAN
 * ========================================================================= */

const ORDER_STATUSES = ["menunggu", "diproses", "dikirim", "selesai", "dibatalkan"] as const;

const orderStatusSchema = z.object({
  id: z.coerce.number().int().positive(),
  status: z.enum(ORDER_STATUSES),
});

/** Ubah status pesanan. FormData: id, status. Status "dikirim" mengisi tanggal_kirim. */
export async function updateOrderStatusAction(formData: FormData): Promise<ActionResult> {
  const admin = await requireAdmin();
  if (!admin) return fail("Akses ditolak. Halaman ini khusus admin.");

  const parsed = orderStatusSchema.safeParse(Object.fromEntries(formData));
  if (!parsed.success) return fail("Status pesanan tidak valid.");

  const { id, status } = parsed.data;
  const order = await prisma.order.findUnique({
    where: { id },
    include: { user: { select: { nama: true } } },
  });
  if (!order) return fail("Pesanan tidak ditemukan.");
  if (order.status === status) return fail("Status pesanan tidak berubah.");

  try {
    await prisma.order.update({
      where: { id },
      data: {
        status,
        // Catat tanggal kirim pertama kali pesanan dikirim.
        ...(status === "dikirim" && !order.tanggal_kirim
          ? { tanggal_kirim: new Date() }
          : {}),
      },
    });
    await logActivity(
      admin.id,
      ACTIVITY_TYPES.admin_order_status_updated,
      `Admin mengubah status pesanan #${id} (${order.user.nama}) dari "${order.status}" menjadi "${status}".`
    );
    revalidatePath("/admin/pesanan");
    revalidatePath(`/admin/pesanan/${id}`);
    revalidatePath("/admin");
    revalidatePath("/pesanan", "layout");
    return ok(`Pesanan #${id} kini berstatus "${status}".`);
  } catch (err) {
    console.error("[admin] gagal mengubah status pesanan:", err);
    return fail("Gagal mengubah status pesanan. Coba lagi.");
  }
}

/* =========================================================================
 * PEMBAYARAN
 * ========================================================================= */

const paymentVerifySchema = z.object({
  id: z.coerce.number().int().positive(),
  keputusan: z.enum(["konfirmasi", "gagal"]),
  catatan: z.string().trim().max(1000).optional(),
});

/**
 * Verifikasi pembayaran. FormData: id, keputusan (konfirmasi|gagal), catatan?
 * Konfirmasi otomatis menaikkan pesanan "menunggu" menjadi "diproses".
 */
export async function verifyPaymentAction(formData: FormData): Promise<ActionResult> {
  const admin = await requireAdmin();
  if (!admin) return fail("Akses ditolak. Halaman ini khusus admin.");

  const parsed = paymentVerifySchema.safeParse(Object.fromEntries(formData));
  if (!parsed.success) return fail("Data verifikasi tidak valid.");

  const { id, keputusan, catatan } = parsed.data;
  const payment = await prisma.payment.findUnique({
    where: { id },
    include: { order: { select: { id: true, status: true } } },
  });
  if (!payment) return fail("Pembayaran tidak ditemukan.");
  if (payment.status !== "menunggu") {
    return fail("Pembayaran ini sudah diverifikasi sebelumnya.");
  }

  const dikonfirmasi = keputusan === "konfirmasi";
  try {
    await prisma.$transaction([
      prisma.payment.update({
        where: { id },
        data: {
          status: dikonfirmasi ? "dikonfirmasi" : "gagal",
          verified_at: new Date(),
          verified_by: admin.id,
          ...(catatan ? { notes: catatan } : {}),
        },
      }),
      // Pembayaran sah → pesanan menunggu langsung diproses.
      ...(dikonfirmasi && payment.order.status === "menunggu"
        ? [
            prisma.order.update({
              where: { id: payment.order.id },
              data: { status: "diproses" },
            }),
          ]
        : []),
    ]);

    await logActivity(
      admin.id,
      dikonfirmasi
        ? ACTIVITY_TYPES.admin_payment_verified
        : ACTIVITY_TYPES.admin_payment_rejected,
      dikonfirmasi
        ? `Admin mengonfirmasi pembayaran #${id} untuk pesanan #${payment.order.id}.`
        : `Admin menolak pembayaran #${id} untuk pesanan #${payment.order.id}.`
    );
    revalidatePath("/admin/pembayaran");
    revalidatePath("/admin/pesanan");
    revalidatePath("/admin");
    return dikonfirmasi
      ? ok(`Pembayaran #${id} dikonfirmasi; pesanan #${payment.order.id} diproses.`)
      : ok(`Pembayaran #${id} ditandai gagal.`);
  } catch (err) {
    console.error("[admin] gagal memverifikasi pembayaran:", err);
    return fail("Gagal memverifikasi pembayaran. Coba lagi.");
  }
}

/* =========================================================================
 * PENGGUNA
 * ========================================================================= */

const USER_ROLES = ["admin", "reseller", "pelanggan"] as const;
const USER_STATUSES = ["active", "inactive"] as const;

const userRoleSchema = z.object({
  id: z.coerce.number().int().positive(),
  role: z.enum(USER_ROLES),
});

/** Ubah role pengguna. FormData: id, role. Tidak bisa mengubah akun sendiri. */
export async function updateUserRoleAction(formData: FormData): Promise<ActionResult> {
  const admin = await requireAdmin();
  if (!admin) return fail("Akses ditolak. Halaman ini khusus admin.");

  const parsed = userRoleSchema.safeParse(Object.fromEntries(formData));
  if (!parsed.success) return fail("Role tidak valid.");

  const { id, role } = parsed.data;
  if (id === admin.id) return fail("Anda tidak dapat mengubah role akun sendiri.");

  const target = await prisma.user.findUnique({ where: { id } });
  if (!target) return fail("Pengguna tidak ditemukan.");
  if (target.role === role) return fail("Role pengguna tidak berubah.");

  try {
    await prisma.user.update({ where: { id }, data: { role } });
    await logActivity(
      admin.id,
      ACTIVITY_TYPES.admin_user_role_updated,
      `Admin mengubah role ${target.nama} (${target.email}) dari "${target.role}" menjadi "${role}".`
    );
    revalidatePath("/admin/pengguna");
    return ok(`Role ${target.nama} diubah menjadi "${role}".`);
  } catch (err) {
    console.error("[admin] gagal mengubah role:", err);
    return fail("Gagal mengubah role pengguna. Coba lagi.");
  }
}

const userStatusSchema = z.object({
  id: z.coerce.number().int().positive(),
  status: z.enum(USER_STATUSES),
});

/** Ubah status pengguna (active/inactive). FormData: id, status. */
export async function updateUserStatusAction(formData: FormData): Promise<ActionResult> {
  const admin = await requireAdmin();
  if (!admin) return fail("Akses ditolak. Halaman ini khusus admin.");

  const parsed = userStatusSchema.safeParse(Object.fromEntries(formData));
  if (!parsed.success) return fail("Status tidak valid.");

  const { id, status } = parsed.data;
  if (id === admin.id) return fail("Anda tidak dapat menonaktifkan akun sendiri.");

  const target = await prisma.user.findUnique({ where: { id } });
  if (!target) return fail("Pengguna tidak ditemukan.");
  if (target.status === status) return fail("Status pengguna tidak berubah.");

  try {
    await prisma.user.update({ where: { id }, data: { status } });
    await logActivity(
      admin.id,
      ACTIVITY_TYPES.admin_user_status_updated,
      `Admin ${status === "active" ? "mengaktifkan" : "menonaktifkan"} akun ${target.nama} (${target.email}).`
    );
    revalidatePath("/admin/pengguna");
    return ok(
      status === "active"
        ? `Akun ${target.nama} diaktifkan.`
        : `Akun ${target.nama} dinonaktifkan (tidak bisa login).`
    );
  } catch (err) {
    console.error("[admin] gagal mengubah status pengguna:", err);
    return fail("Gagal mengubah status pengguna. Coba lagi.");
  }
}

/* =========================================================================
 * KONTEN
 * ========================================================================= */

const contentSchema = z.object({
  judul: z.string().trim().min(3, "Judul minimal 3 karakter.").max(200),
  penulis: z.string().trim().min(2, "Penulis wajib diisi.").max(100),
  isi: z.string().trim().min(10, "Isi konten minimal 10 karakter.").max(20000),
  video: z.string().trim().max(500).optional(),
});

/** Tambah konten baru. FormData: judul, penulis, isi, video?, gambar? (file). */
export async function createContentAction(formData: FormData): Promise<ActionResult> {
  const admin = await requireAdmin();
  if (!admin) return fail("Akses ditolak. Halaman ini khusus admin.");

  const parsed = contentSchema.safeParse(Object.fromEntries(formData));
  if (!parsed.success) {
    return fail(parsed.error.issues[0]?.message ?? "Data konten tidak valid.");
  }

  let gambar: string | null = null;
  const file = formData.get("gambar");
  if (isFileWithSize(file)) {
    try {
      gambar = await saveUploadedFile(file, "content", `content_${parsed.data.judul}`);
    } catch (err) {
      if (err instanceof UploadError) return fail(err.message);
      console.error("[admin] gagal menyimpan gambar konten:", err);
      return fail("Gagal menyimpan gambar. Coba lagi.");
    }
  }

  try {
    const content = await prisma.content.create({
      data: {
        judul: parsed.data.judul,
        penulis: parsed.data.penulis,
        isi: parsed.data.isi,
        video: parsed.data.video || null,
        gambar,
      },
    });
    await logActivity(
      admin.id,
      ACTIVITY_TYPES.admin_content_created,
      `Admin menambahkan konten "${content.judul}" (ID ${content.id}).`
    );
    revalidatePath("/admin/konten");
    revalidatePath("/", "layout");
    return ok(`Konten "${content.judul}" berhasil ditambahkan.`);
  } catch (err) {
    console.error("[admin] gagal membuat konten:", err);
    return fail("Gagal menyimpan konten. Coba lagi.");
  }
}

/** Perbarui konten. FormData: id + field konten + gambar? (opsional). */
export async function updateContentAction(formData: FormData): Promise<ActionResult> {
  const admin = await requireAdmin();
  if (!admin) return fail("Akses ditolak. Halaman ini khusus admin.");

  const id = readId(formData);
  if (!Number.isFinite(id)) return fail("ID konten tidak valid.");

  const parsed = contentSchema.safeParse(Object.fromEntries(formData));
  if (!parsed.success) {
    return fail(parsed.error.issues[0]?.message ?? "Data konten tidak valid.");
  }

  const existing = await prisma.content.findUnique({ where: { id } });
  if (!existing) return fail("Konten tidak ditemukan.");

  let gambar = existing.gambar;
  const file = formData.get("gambar");
  if (isFileWithSize(file)) {
    try {
      gambar = await saveUploadedFile(file, "content", `content_${parsed.data.judul}`);
    } catch (err) {
      if (err instanceof UploadError) return fail(err.message);
      console.error("[admin] gagal menyimpan gambar konten:", err);
      return fail("Gagal menyimpan gambar. Coba lagi.");
    }
  }

  try {
    await prisma.content.update({
      where: { id },
      data: {
        judul: parsed.data.judul,
        penulis: parsed.data.penulis,
        isi: parsed.data.isi,
        video: parsed.data.video || null,
        gambar,
      },
    });
    await logActivity(
      admin.id,
      ACTIVITY_TYPES.admin_content_updated,
      `Admin memperbarui konten "${parsed.data.judul}" (ID ${id}).`
    );
    revalidatePath("/admin/konten");
    revalidatePath("/", "layout");
    return ok(`Konten "${parsed.data.judul}" berhasil diperbarui.`);
  } catch (err) {
    console.error("[admin] gagal memperbarui konten:", err);
    return fail("Gagal memperbarui konten. Coba lagi.");
  }
}

/** Hapus konten. FormData: id. */
export async function deleteContentAction(formData: FormData): Promise<ActionResult> {
  const admin = await requireAdmin();
  if (!admin) return fail("Akses ditolak. Halaman ini khusus admin.");

  const id = readId(formData);
  if (!Number.isFinite(id)) return fail("ID konten tidak valid.");

  const existing = await prisma.content.findUnique({ where: { id } });
  if (!existing) return fail("Konten tidak ditemukan.");

  try {
    await prisma.content.delete({ where: { id } });
    await logActivity(
      admin.id,
      ACTIVITY_TYPES.admin_content_deleted,
      `Admin menghapus konten "${existing.judul}" (ID ${id}).`
    );
    revalidatePath("/admin/konten");
    revalidatePath("/", "layout");
    return ok(`Konten "${existing.judul}" dihapus.`);
  } catch (err) {
    console.error("[admin] gagal menghapus konten:", err);
    return fail("Gagal menghapus konten. Coba lagi.");
  }
}

/* =========================================================================
 * SESI
 * ========================================================================= */

/** Keluar dari panel admin. */
export async function adminLogoutAction(): Promise<void> {
  await signOut({ redirectTo: "/login" });
}