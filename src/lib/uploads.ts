import { mkdir, writeFile } from "node:fs/promises";
import path from "node:path";
import crypto from "node:crypto";

export type UploadFolder = "payments" | "products" | "content";

/** Batas ukuran file: 5 MB (lebih dari cukup untuk bukti transfer foto HP). */
export const MAX_FILE_SIZE = 5 * 1024 * 1024;

const ALLOWED_TYPES: Record<string, string> = {
  "image/jpeg": ".jpg",
  "image/png": ".png",
  "image/webp": ".webp",
};

/** Error validasi upload — aman ditampilkan ke pengguna. */
export class UploadError extends Error {}

/**
 * Simpan file (dari FormData) ke public/uploads/<folder>.
 * Nama file dibuat unik dan aman, mengikuti pola legacy,
 * mis. payment_proof_<order_id>_<timestamp>_<acak>.png.
 *
 * Mengembalikan path relatif ala database "uploads/<folder>/<nama>"
 * yang kompatibel dengan helper imageUrl() di @/lib/format.
 */
export async function saveUploadedFile(
  file: File,
  folder: UploadFolder,
  baseName: string
): Promise<string> {
  if (!file || typeof file.arrayBuffer !== "function") {
    throw new UploadError("File tidak ditemukan.");
  }
  if (file.size === 0) throw new UploadError("File kosong.");
  if (file.size > MAX_FILE_SIZE) {
    throw new UploadError("Ukuran file maksimal 5 MB.");
  }

  const ext = ALLOWED_TYPES[file.type.toLowerCase()];
  if (!ext) {
    throw new UploadError("Format file harus JPG, PNG, atau WEBP.");
  }

  const safeBase =
    baseName
      .toLowerCase()
      .replace(/[^a-z0-9_-]+/g, "_")
      .replace(/^_+|_+$/g, "") || "file";
  const fileName = `${safeBase}_${Math.floor(Date.now() / 1000)}_${crypto
    .randomBytes(3)
    .toString("hex")}${ext}`;

  const dir = path.join(process.cwd(), "public", "uploads", folder);
  await mkdir(dir, { recursive: true });
  const buffer = Buffer.from(await file.arrayBuffer());
  await writeFile(path.join(dir, fileName), buffer);

  return `uploads/${folder}/${fileName}`;
}
