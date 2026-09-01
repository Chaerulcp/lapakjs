import bcrypt from "bcryptjs";

/**
 * Password lama dibuat dengan PHP (prefix $2y$). Algoritmanya identik dengan
 * bcrypt $2a$; normalisasi prefix agar bcryptjs bisa memverifikasi.
 */
function normalizeHash(hash: string): string {
  if (hash.startsWith("$2y$")) return "$2a$" + hash.slice(4);
  return hash;
}

export async function verifyPassword(password: string, hash: string): Promise<boolean> {
  try {
    return await bcrypt.compare(password, normalizeHash(hash));
  } catch {
    return false;
  }
}

export async function hashPassword(password: string): Promise<string> {
  return bcrypt.hash(password, 10);
}
