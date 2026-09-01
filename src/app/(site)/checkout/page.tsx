import Link from "next/link";
import { redirect } from "next/navigation";
import type { Metadata } from "next";
import { ChevronRight, Lock } from "lucide-react";
import { auth } from "@/auth";
import { prisma } from "@/lib/db";
import { getCartLines } from "@/lib/cart";
import { formatRupiah } from "@/lib/format";
import { SITE } from "@/lib/site";
import CheckoutForm from "@/components/site/CheckoutForm";
import { Separator } from "@/components/ui/separator";

export const metadata: Metadata = {
  title: "Checkout",
  description: `Selesaikan pesananmu di ${SITE.name}.`,
};

export default async function CheckoutPage() {
  const session = await auth();
  if (!session?.user) redirect("/login");

  const lines = await getCartLines();
  if (lines.length === 0) redirect("/keranjang");

  const userId = Number(session.user.id);
  const profil = await prisma.user.findUnique({
    where: { id: userId },
    select: { nama: true, alamat: true, no_hp: true },
  });

  const total = lines.reduce((sum, l) => sum + l.subtotal, 0);

  return (
    <div className="mx-auto w-full max-w-6xl px-4 py-10">
      {/* Breadcrumb */}
      <nav className="flex items-center gap-1.5 text-sm text-muted-foreground">
        <Link href="/keranjang" className="hover:text-chili-700">
          Keranjang
        </Link>
        <ChevronRight className="size-3.5" />
        <span className="text-foreground">Checkout</span>
      </nav>

      <h1 className="mt-4 font-display text-3xl font-extrabold tracking-tight text-ink">
        Checkout
      </h1>
      <p className="mt-1 flex items-center gap-1.5 text-sm text-muted-foreground">
        <Lock className="size-3.5" />
        Pesanan dibuat atas nama {session.user.email}
      </p>

      <div className="mt-8 grid items-start gap-8 lg:grid-cols-[1fr_360px]">
        <div className="rounded-2xl border border-border bg-card p-6 shadow-sm">
          <CheckoutForm
            lines={lines.map((l) => ({
              product_id: l.product.id,
              nama: l.product.nama,
              harga: Number(l.product.harga),
              qty: l.qty,
            }))}
            defaultAlamat={profil?.alamat ?? ""}
            namaPenerima={profil?.nama ?? session.user.name ?? "Pelanggan"}
            noHp={profil?.no_hp ?? "-"}
          />
        </div>

        {/* Ringkasan pesanan */}
        <aside className="sticky top-24 rounded-2xl border border-border bg-card p-6 shadow-sm">
          <h2 className="font-display text-lg font-bold text-ink">Ringkasan Pesanan</h2>
          <div className="mt-4 space-y-2.5 text-sm">
            {lines.map((l) => (
              <div key={l.product.id} className="flex justify-between gap-3 text-muted-foreground">
                <span className="truncate">
                  {l.product.nama} <span className="font-mono tabular-nums">×{l.qty}</span>
                </span>
                <span className="shrink-0 font-mono tabular-nums">{formatRupiah(l.subtotal)}</span>
              </div>
            ))}
          </div>
          <Separator className="my-4" />
          <div className="space-y-1.5 text-sm">
            <div className="flex justify-between text-muted-foreground">
              <span>Subtotal</span>
              <span className="font-mono tabular-nums">{formatRupiah(total)}</span>
            </div>
            <div className="flex justify-between text-muted-foreground">
              <span>Ongkos kirim</span>
              <span>Saat verifikasi</span>
            </div>
          </div>
          <Separator className="my-4" />
          <div className="flex items-center justify-between">
            <span className="text-sm font-medium">Total</span>
            <span className="font-mono text-xl font-bold text-chili-700 tabular-nums">
              {formatRupiah(total)}
            </span>
          </div>
          <p className="mt-4 text-xs leading-relaxed text-muted-foreground">
            Pembayaran dilakukan via transfer/QRIS/e-wallet. Unggah bukti bayar setelah pesanan
            dibuat agar segera diverifikasi.
          </p>
        </aside>
      </div>
    </div>
  );
}
