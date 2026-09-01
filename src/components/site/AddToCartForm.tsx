"use client";

import { useEffect, useState, useTransition } from "react";
import { Minus, Plus, ShoppingCart } from "lucide-react";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import { addToCartAction, type CartActionResult } from "@/app/(site)/actions";

/** Form tambah ke keranjang dengan stepper qty — dipakai di detail produk. */
export default function AddToCartForm({
  productId,
  stok,
}: {
  productId: number;
  stok: number;
}) {
  const [qty, setQty] = useState(1);
  const [pending, startTransition] = useTransition();
  const [result, setResult] = useState<CartActionResult | null>(null);

  useEffect(() => {
    if (result?.message) {
      if (result.ok) toast.success(result.message);
      else toast.error(result.message);
    }
  }, [result]);

  if (stok <= 0) {
    return (
      <div className="rounded-xl border border-border bg-muted p-4 text-sm font-medium text-muted-foreground">
        Stok produk ini sedang habis. Cek lagi nanti ya! 🙏
      </div>
    );
  }

  function submit(formData: FormData) {
    startTransition(async () => {
      const res = await addToCartAction(formData);
      setResult(res);
    });
  }

  return (
    <form action={submit} className="space-y-3">
      <input type="hidden" name="productId" value={productId} />
      <input type="hidden" name="qty" value={qty} />
      <div className="flex flex-wrap items-center gap-3">
        <div className="flex h-10 items-center rounded-xl border border-border bg-card">
          <button
            type="button"
            aria-label="Kurangi jumlah"
            onClick={() => setQty((q) => Math.max(1, q - 1))}
            disabled={qty <= 1}
            className="flex h-full w-10 items-center justify-center text-foreground transition-colors hover:text-chili-600 disabled:opacity-40"
          >
            <Minus className="size-4" />
          </button>
          <span className="w-10 text-center font-mono text-sm font-bold tabular-nums">{qty}</span>
          <button
            type="button"
            aria-label="Tambah jumlah"
            onClick={() => setQty((q) => Math.min(stok, q + 1))}
            disabled={qty >= stok}
            className="flex h-full w-10 items-center justify-center text-foreground transition-colors hover:text-chili-600 disabled:opacity-40"
          >
            <Plus className="size-4" />
          </button>
        </div>
        <Button
          type="submit"
          disabled={pending}
          size="lg"
          className="h-10 flex-1 bg-chili-600 px-6 text-sm hover:bg-chili-700 sm:flex-none"
        >
          <ShoppingCart className="size-4" />
          {pending ? "Menambahkan…" : "Tambah ke Keranjang"}
        </Button>
      </div>
      <p className="text-xs text-muted-foreground">
        Tersedia <span className="font-mono font-semibold tabular-nums">{stok}</span> botol. Maks.{" "}
        {stok} per pesanan.
      </p>
    </form>
  );
}
