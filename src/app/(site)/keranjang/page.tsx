import Link from "next/link";
import type { Metadata } from "next";
import { ArrowRight, ShoppingCart } from "lucide-react";
import { getCartLines } from "@/lib/cart";
import { formatRupiah } from "@/lib/format";
import { SITE } from "@/lib/site";
import CartItemRow from "@/components/site/CartItemRow";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";

export const metadata: Metadata = {
  title: "Keranjang",
  description: `Keranjang belanja ${SITE.name}.`,
};

export default async function KeranjangPage() {
  const lines = await getCartLines();
  const total = lines.reduce((sum, l) => sum + l.subtotal, 0);
  const jumlahItem = lines.reduce((sum, l) => sum + l.qty, 0);

  if (lines.length === 0) {
    return (
      <div className="mx-auto w-full max-w-2xl px-4 py-20 text-center">
        <p className="text-6xl">🛒</p>
        <h1 className="mt-5 font-display text-2xl font-extrabold tracking-tight text-ink">
          Keranjangmu masih kosong
        </h1>
        <p className="mt-2 text-muted-foreground">
          Belum ada sambal di sini. Yuk, pilih sambal favoritmu dulu!
        </p>
        <Button asChild size="lg" className="mt-6 h-11 bg-chili-600 px-6 hover:bg-chili-700">
          <Link href="/produk">
            <ShoppingCart className="size-4" />
            Mulai Belanja
          </Link>
        </Button>
      </div>
    );
  }

  return (
    <div className="mx-auto w-full max-w-6xl px-4 py-10">
      <h1 className="font-display text-3xl font-extrabold tracking-tight text-ink">
        Keranjang Belanja
      </h1>
      <p className="mt-1 text-muted-foreground">
        {jumlahItem} item menanti untuk dikirim.
      </p>

      <div className="mt-8 grid items-start gap-8 lg:grid-cols-[1fr_340px]">
        {/* Daftar item */}
        <div className="rounded-2xl border border-border bg-card px-5 shadow-sm">
          {lines.map((line) => (
            <div key={line.product.id} className="border-b border-border last:border-b-0">
              <CartItemRow line={line} />
            </div>
          ))}
        </div>

        {/* Ringkasan */}
        <aside className="sticky top-24 rounded-2xl border border-border bg-card p-6 shadow-sm">
          <h2 className="font-display text-lg font-bold text-ink">Ringkasan Belanja</h2>
          <div className="mt-4 space-y-2.5 text-sm">
            {lines.map((l) => (
              <div key={l.product.id} className="flex justify-between gap-3 text-muted-foreground">
                <span className="truncate">
                  {l.product.nama} <span className="font-mono tabular-nums">×{l.qty}</span>
                </span>
                <span className="font-mono shrink-0 tabular-nums">{formatRupiah(l.subtotal)}</span>
              </div>
            ))}
          </div>
          <Separator className="my-4" />
          <div className="flex items-center justify-between">
            <span className="text-sm font-medium">Total</span>
            <span className="font-mono text-xl font-bold text-chili-700 tabular-nums">
              {formatRupiah(total)}
            </span>
          </div>
          <Button asChild size="lg" className="mt-5 h-11 w-full bg-chili-600 hover:bg-chili-700">
            <Link href="/checkout">
              Lanjut ke Checkout
              <ArrowRight className="size-4" />
            </Link>
          </Button>
          <Button asChild variant="ghost" className="mt-2 w-full text-muted-foreground">
            <Link href="/produk">Lanjut Belanja</Link>
          </Button>
          <p className="mt-4 text-center text-xs text-muted-foreground">
            Ongkos kirim dihitung saat verifikasi pesanan oleh tim kami.
          </p>
        </aside>
      </div>
    </div>
  );
}
