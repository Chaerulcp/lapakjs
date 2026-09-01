/** Format angka menjadi Rupiah: 25000 -> "Rp25.000" */
export function formatRupiah(value: number | string | { toString(): string }): string {
  const num = typeof value === "number" ? value : Number(value.toString());
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(num);
}

/** Format tanggal Indonesia: "12 Jun 2026" */
export function formatTanggal(date: Date | string | null | undefined, withTime = false): string {
  if (!date) return "-";
  const d = typeof date === "string" ? new Date(date) : date;
  return new Intl.DateTimeFormat("id-ID", {
    day: "numeric",
    month: "short",
    year: "numeric",
    ...(withTime ? { hour: "2-digit", minute: "2-digit" } : {}),
  }).format(d);
}

/**
 * Normalisasi path gambar dari database lama ke URL publik Next.js.
 * "public/uploads/products/x.jpg" -> "/uploads/products/x.jpg"
 * "uploads/products/x.jpg"        -> "/uploads/products/x.jpg"
 */
export function imageUrl(foto: string | null | undefined, fallback = "/uploads/products/placeholder.jpg"): string {
  if (!foto) return fallback;
  let path = foto.trim().replace(/\\/g, "/");
  if (path.startsWith("public/")) path = path.slice("public/".length);
  if (!path.startsWith("/")) path = "/" + path;
  return encodeURI(path);
}

export const ORDER_STATUS_LABEL: Record<string, string> = {
  menunggu: "Menunggu",
  diproses: "Diproses",
  dikirim: "Dikirim",
  selesai: "Selesai",
  dibatalkan: "Dibatalkan",
};

export const PAYMENT_STATUS_LABEL: Record<string, string> = {
  menunggu: "Menunggu Verifikasi",
  dikonfirmasi: "Dikonfirmasi",
  gagal: "Gagal",
};
