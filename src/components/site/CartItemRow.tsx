import Image from "next/image";
import Link from "next/link";
import { Minus, Plus, Trash2 } from "lucide-react";
import { Button } from "@/components/ui/button";
import { imageUrl, formatRupiah } from "@/lib/format";
import type { CartLine } from "@/lib/cart";
import { removeItemAction, updateQtyAction } from "@/app/(site)/actions";

/** Satu baris item keranjang dengan kontrol qty berbasis server action. */
export default function CartItemRow({ line }: { line: CartLine }) {
  const { product, qty, subtotal } = line;
  const harga = Number(product.harga);
  const maxStok = product.stok;

  return (
    <div className="flex gap-4 py-5">
      <Link href={`/produk/${product.id}`} className="relative block size-20 shrink-0 overflow-hidden rounded-xl border border-border bg-muted sm:size-24">
        <Image
          src={imageUrl(product.foto)}
          alt={product.nama}
          fill
          sizes="96px"
          className="object-cover"
        />
      </Link>

      <div className="flex flex-1 flex-col gap-2">
        <div className="flex items-start justify-between gap-3">
          <div>
            <Link
              href={`/produk/${product.id}`}
              className="font-display text-sm font-bold leading-snug text-ink hover:text-chili-700 sm:text-base"
            >
              {product.nama}
            </Link>
            <p className="mt-0.5 font-mono text-sm text-muted-foreground tabular-nums">
              {formatRupiah(harga)} / botol
            </p>
          </div>
          <form action={removeItemAction}>
            <input type="hidden" name="productId" value={product.id} />
            <Button
              type="submit"
              variant="ghost"
              size="icon-sm"
              aria-label={`Hapus ${product.nama} dari keranjang`}
              className="text-muted-foreground hover:bg-destructive/10 hover:text-destructive"
            >
              <Trash2 className="size-4" />
            </Button>
          </form>
        </div>

        <div className="mt-auto flex flex-wrap items-center justify-between gap-3">
          <form action={updateQtyAction} className="flex items-center gap-1">
            <input type="hidden" name="productId" value={product.id} />
            <input type="hidden" name="qty" value={qty} />
            <Button
              type="submit"
              name="op"
              value="dec"
              variant="outline"
              size="icon-sm"
              disabled={qty <= 1}
              aria-label="Kurangi jumlah"
            >
              <Minus className="size-3.5" />
            </Button>
            <span className="w-9 text-center font-mono text-sm font-bold tabular-nums">{qty}</span>
            <Button
              type="submit"
              name="op"
              value="inc"
              variant="outline"
              size="icon-sm"
              disabled={qty >= maxStok}
              aria-label="Tambah jumlah"
            >
              <Plus className="size-3.5" />
            </Button>
          </form>

          <span className="font-mono text-base font-bold text-chili-700 tabular-nums">
            {formatRupiah(subtotal)}
          </span>
        </div>
        {qty >= maxStok && (
          <p className="text-xs text-muted-foreground">Stok maksimum untuk produk ini.</p>
        )}
      </div>
    </div>
  );
}
