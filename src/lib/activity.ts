import { prisma } from "@/lib/db";

/** Tipe aktivitas yang dicatat di log (tabel activity_logs). */
export const ACTIVITY_TYPES = {
  register: "register",
  verify_email: "verify_email",
  resend_verification: "resend_verification",
  forgot_password: "forgot_password",
  reset_password: "reset_password",
  login: "login",
  logout: "logout",
  order_created: "order_created",
  payment_uploaded: "payment_uploaded",
  // Aksi admin (panel admin)
  admin_product_created: "admin_product_created",
  admin_product_updated: "admin_product_updated",
  admin_product_deleted: "admin_product_deleted",
  admin_order_status_updated: "admin_order_status_updated",
  admin_payment_verified: "admin_payment_verified",
  admin_payment_rejected: "admin_payment_rejected",
  admin_user_role_updated: "admin_user_role_updated",
  admin_user_status_updated: "admin_user_status_updated",
  admin_content_created: "admin_content_created",
  admin_content_updated: "admin_content_updated",
  admin_content_deleted: "admin_content_deleted",
} as const;

export type ActivityType = (typeof ACTIVITY_TYPES)[keyof typeof ACTIVITY_TYPES];

/** Label ramah untuk tiap tipe aktivitas (dipakai di log & filter panel admin). */
export const ACTIVITY_TYPE_LABEL: Record<string, string> = {
  register: "Daftar Akun",
  verify_email: "Verifikasi Email",
  resend_verification: "Kirim Ulang Verifikasi",
  forgot_password: "Lupa Password",
  reset_password: "Reset Password",
  login: "Masuk",
  logout: "Keluar",
  order_created: "Pesanan Dibuat",
  payment_uploaded: "Bukti Bayar Diunggah",
  admin_product_created: "Admin: Tambah Produk",
  admin_product_updated: "Admin: Ubah Produk",
  admin_product_deleted: "Admin: Hapus Produk",
  admin_order_status_updated: "Admin: Ubah Status Pesanan",
  admin_payment_verified: "Admin: Pembayaran Dikonfirmasi",
  admin_payment_rejected: "Admin: Pembayaran Ditolak",
  admin_user_role_updated: "Admin: Ubah Role Pengguna",
  admin_user_status_updated: "Admin: Ubah Status Pengguna",
  admin_content_created: "Admin: Tambah Konten",
  admin_content_updated: "Admin: Ubah Konten",
  admin_content_deleted: "Admin: Hapus Konten",
};

/**
 * Catat aktivitas pengguna ke tabel activity_logs.
 * Fire-and-forget: kegagalan mencatat log tidak boleh menggagalkan proses utama.
 */
export async function logActivity(
  userId: number | string,
  activityType: ActivityType | string,
  description: string
): Promise<void> {
  try {
    const id = Number(userId);
    if (!Number.isFinite(id) || id <= 0) return;
    await prisma.activityLog.create({
      data: { user_id: id, activity_type: activityType, description },
    });
  } catch (err) {
    console.error("[activity] gagal mencatat log:", err);
  }
}

/** Log aktivitas terbaru untuk dashboard admin (lengkap dengan data pengguna). */
export async function getRecentActivity(limit = 20) {
  return prisma.activityLog.findMany({
    orderBy: { created_at: "desc" },
    take: Math.max(1, Math.min(limit, 100)),
    include: {
      user: { select: { id: true, nama: true, email: true, role: true } },
    },
  });
}

/** Riwayat aktivitas satu pengguna. */
export async function getUserActivity(userId: number | string, limit = 20) {
  const id = Number(userId);
  if (!Number.isFinite(id) || id <= 0) return [];
  return prisma.activityLog.findMany({
    where: { user_id: id },
    orderBy: { created_at: "desc" },
    take: Math.max(1, Math.min(limit, 100)),
  });
}
