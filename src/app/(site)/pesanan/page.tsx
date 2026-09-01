import Link from "next/link";
import { redirect } from "next/navigation";
import type { Metadata } from "next";
import { ChevronRight, Package } from "lucide-react";
import { auth } from "@/auth";
import { prisma } from "@/lib/db";
import { formatRupiah, formatTanggal } from "@/lib/format";
import { metodeLabel, OrderStatusBadge, PaymentStatusBadge } from "@/components/site/StatusBadge";
import { Button } from "@/components/ui/button";

export const metadata: Metadata = {
  title: "Pesanan Saya",
  description: "Daftar pesananmu di Sambal Mama Ana.",
};

export default async function PesananPage() {
  const session = await auth();
  if (!session?.user) redirect("/login");

  const orders = await prisma.order.findMany({
    where: { user_id: Number(session.user.id) },
    orderBy: { tanggal: "desc" },
    include: {
      items: { include: { product: { select: { nama: true } } } },
      payments: { orderBy: { tanggal: "desc" } },
    },
  });

  return (
    <div className="mx-auto w-full max-w-4xl px-4 py-10">
      <h1 className="font-display text-3xl font-extrabold tracking-tight text-ink">
        Pesanan Saya
      </h1>
      <p className="mt-1 text-muted-foreground">
        Pantau status pesanan dan unggah bukti pembayaranmu di sini.
      </p>

      {orders.length === 0 ? (
        <div className="mt-10 rounded-2xl border border-dashed border-border bg-card p-12 text-center">
          <Package className="mx-auto size-10 text-muted-foreground/60" />
          <h2 className="mt-4 font-display text-lg font-bold text-ink">Belum ada pesanan</h2>
          <p className="mt-1 text-sm text-muted-foreground">
            Saatnya stok sambal di dapurmu, nih!
          </p>
          <Button asChild size="lg" className="mt-6 h-11 bg-chili-600 px-6 hover:bg-chili-700">
            <Link href="/produk">Belanja Sekarang</Link>
          </Button>
        </div>
      ) : (
        <div className="mt-8 space-y-4">
          {orders.map((order) => {
            const pembayaranTerakhir = order.payments[0];
            return (
              <Link
                key={order.id}
                href={`/pesanan/${order.id}`}
                className="group block rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-chili-200 hover:shadow-md"
              >
                <div className="flex flex-wrap items-center justify-between gap-3">
                  <div>
                    <p className="font-display text-base font-bold text-ink group-hover:text-chili-700">
                      Pesanan #{String(order.id).padStart(5, "0")}
                    </p>
                    <p className="mt-0.5 text-xs text-muted-foreground">
                      {formatTanggal(order.tanggal, true)} · {metodeLabel(order.metode_pembayaran)}
                    </p>
                  </div>
                  <OrderStatusBadge status={order.status} />
                </div>

                <p className="mt-3 line-clamp-1 text-sm text-muted-foreground">
                  {order.items.map((it) => `${it.product.nama} ×${it.jumlah}`).join(", ")}
                </p>

                <div className="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-border pt-4">
                  <div className="flex items-center gap-2">
                    <span className="text-sm text-muted-foreground">
                      {order.items.reduce((s, it) => s + it.jumlah, 0)} item ·
                    </span>
                    <span className="font-mono text-base font-bold text-chili-700 tabular-nums">
                      {formatRupiah(order.total)}
                    </span>
                  </div>
                  <div className="flex items-center gap-3">
                    {pembayaranTerakhir && (
                      <PaymentStatusBadge status={pembayaranTerakhir.status} />
                    )}
                    <span className="flex items-center gap-1 text-sm font-medium text-chili-700">
                      Detail
                      <ChevronRight className="size-4 transition-transform group-hover:translate-x-0.5" />
                    </span>
                  </div>
                </div>
              </Link>
            );
          })}
        </div>
      )}
    </div>
  );
}
