import { cookies } from "next/headers";
import type { Product } from "@prisma/client";
import { prisma } from "@/lib/db";

/**
 * Keranjang belanja server-side berbasis cookie.
 * Cookie "cart" berisi JSON [{ id, qty }] — produk di-join lewat Prisma,
 * produk yang sudah hilang dari database otomatis dilewati.
 */

const COOKIE_NAME = "cart";
const COOKIE_MAX_AGE = 60 * 60 * 24 * 30; // 30 hari

export type CartEntry = { id: number; qty: number };

export type CartLine = {
  product: Product;
  qty: number;
  subtotal: number;
};

function parseCart(raw: string | undefined): CartEntry[] {
  if (!raw) return [];
  try {
    const parsed: unknown = JSON.parse(raw);
    if (!Array.isArray(parsed)) return [];
    const seen = new Set<number>();
    const entries: CartEntry[] = [];
    for (const item of parsed) {
      if (!item || typeof item !== "object") continue;
      const id = Number((item as Record<string, unknown>).id);
      const qty = Math.floor(Number((item as Record<string, unknown>).qty));
      if (!Number.isInteger(id) || id <= 0) continue;
      if (!Number.isInteger(qty) || qty <= 0) continue;
      if (seen.has(id)) continue;
      seen.add(id);
      entries.push({ id, qty: Math.min(qty, 999) });
    }
    return entries;
  } catch {
    return [];
  }
}

async function readCart(): Promise<CartEntry[]> {
  const store = await cookies();
  return parseCart(store.get(COOKIE_NAME)?.value);
}

async function writeCart(entries: CartEntry[]): Promise<void> {
  const store = await cookies();
  store.set(COOKIE_NAME, JSON.stringify(entries), {
    path: "/",
    maxAge: entries.length > 0 ? COOKIE_MAX_AGE : 0,
    sameSite: "lax",
    httpOnly: false,
  });
}

/** Baris keranjang + data produk terkini. Produk yang hilang di-skip. */
export async function getCartLines(): Promise<CartLine[]> {
  const entries = await readCart();
  if (entries.length === 0) return [];

  const products = await prisma.product.findMany({
    where: { id: { in: entries.map((e) => e.id) } },
  });
  const byId = new Map(products.map((p) => [p.id, p]));

  return entries.flatMap((entry) => {
    const product = byId.get(entry.id);
    if (!product) return [];
    const qty = Math.min(entry.qty, Math.max(product.stok, 1));
    return [{ product, qty, subtotal: Number(product.harga) * qty }];
  });
}

/**
 * Tambah produk ke keranjang. Qty diakumulasi dan dibatasi stok.
 * Mengembalikan false bila produk tidak ditemukan / stok habis.
 */
export async function addToCart(productId: number, qty = 1): Promise<boolean> {
  if (!Number.isInteger(productId) || productId <= 0) return false;
  const product = await prisma.product.findUnique({ where: { id: productId } });
  if (!product || product.stok <= 0) return false;

  const entries = await readCart();
  const existing = entries.find((e) => e.id === productId);
  const nextQty = Math.min(product.stok, (existing?.qty ?? 0) + Math.max(1, Math.floor(qty)));

  if (existing) existing.qty = nextQty;
  else entries.push({ id: productId, qty: nextQty });

  await writeCart(entries);
  return true;
}

/** Set qty absolut sebuah item. qty <= 0 menghapus item; qty dibatasi stok. */
export async function setCartQty(productId: number, qty: number): Promise<void> {
  if (!Number.isInteger(productId) || productId <= 0) return;
  const entries = await readCart();
  const index = entries.findIndex((e) => e.id === productId);
  if (index === -1) return;

  if (!Number.isFinite(qty) || qty <= 0) {
    entries.splice(index, 1);
  } else {
    const product = await prisma.product.findUnique({
      where: { id: productId },
      select: { stok: true },
    });
    if (!product || product.stok <= 0) {
      entries.splice(index, 1);
    } else {
      entries[index]!.qty = Math.min(product.stok, Math.floor(qty));
    }
  }
  await writeCart(entries);
}

/** Hapus satu produk dari keranjang. */
export async function removeFromCart(productId: number): Promise<void> {
  const entries = await readCart();
  const next = entries.filter((e) => e.id !== productId);
  if (next.length !== entries.length) await writeCart(next);
}

/** Kosongkan seluruh keranjang. */
export async function clearCart(): Promise<void> {
  await writeCart([]);
}

/** Total jumlah item (sum qty) — untuk badge navbar. */
export async function cartCount(): Promise<number> {
  const entries = await readCart();
  return entries.reduce((sum, e) => sum + e.qty, 0);
}
