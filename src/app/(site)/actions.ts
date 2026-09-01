"use server";

import { revalidatePath } from "next/cache";
import { signOut } from "@/auth";
import { addToCart, clearCart, removeFromCart, setCartQty } from "@/lib/cart";

/** Hasil action keranjang — dipakai form client untuk feedback (toast). */
export type CartActionResult = { ok: boolean; message: string };

function revalidateSite() {
  // Badge keranjang ada di layout, jadi revalidasi seluruh layout (site).
  revalidatePath("/", "layout");
}

function readNumber(formData: FormData, key: string): number {
  const raw = formData.get(key);
  const num = Number(typeof raw === "string" ? raw : "");
  return Number.isFinite(num) ? num : NaN;
}

/** Tambah item ke keranjang. FormData: productId, qty. */
export async function addToCartAction(formData: FormData): Promise<CartActionResult> {
  const productId = readNumber(formData, "productId");
  const qty = readNumber(formData, "qty");

  const added = await addToCart(productId, Number.isFinite(qty) && qty > 0 ? qty : 1);
  revalidateSite();

  return added
    ? { ok: true, message: "Ditambahkan ke keranjang. Pesananmu siap diproses! 🛍️" }
    : { ok: false, message: "Maaf, produk tidak tersedia atau stok sedang habis." };
}

/**
 * Ubah qty item. FormData: productId + salah satu dari:
 * - op="dec" / op="inc"  → qty saat ini (field qty) ditambah/dikurangi 1
 * - qty langsung         → set qty absolut (qty <= 0 berarti hapus)
 */
export async function updateQtyAction(formData: FormData): Promise<void> {
  const productId = readNumber(formData, "productId");
  const op = formData.get("op");
  const current = readNumber(formData, "qty");

  if (op === "inc") {
    await setCartQty(productId, (Number.isFinite(current) ? current : 0) + 1);
  } else if (op === "dec") {
    await setCartQty(productId, (Number.isFinite(current) ? current : 0) - 1);
  } else {
    await setCartQty(productId, Number.isFinite(readNumber(formData, "qty")) ? readNumber(formData, "qty") : 0);
  }
  revalidateSite();
}

/** Hapus item dari keranjang. FormData: productId. */
export async function removeItemAction(formData: FormData): Promise<void> {
  const productId = readNumber(formData, "productId");
  await removeFromCart(productId);
  revalidateSite();
}

/** Kosongkan keranjang (dipanggil setelah checkout sukses). */
export async function clearCartAction(): Promise<void> {
  await clearCart();
  revalidateSite();
}

/** Keluar dari akun, kembali ke beranda. */
export async function logoutAction(): Promise<void> {
  await signOut({ redirectTo: "/" });
}
