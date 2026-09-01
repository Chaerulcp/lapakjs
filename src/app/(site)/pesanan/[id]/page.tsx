import Image from "next/image";
import Link from "next/link";
import { notFound, redirect } from "next/navigation";
import type { Metadata } from "next";
import { MapPin } from "lucide-react";
import { auth } from "@/auth";
import { prisma } from "@/lib/db";
import { formatRupiah, formatTanggal, imageUrl } from "@/lib/format";
import PaymentConfirmationForm from "@/components/site/PaymentConfirmationForm";
import { metodeLabel, OrderStatusBadge, PaymentStatusBadge } from "@/components/site/StatusBadge";
import { Separator } from "@/components/ui/separator";

type PesananDetailParams = { id: string };

/** Info rekening/kanal pembayaran sesuai metode (nilai dari sistem lama). */
function infoPembayaran(metode: string): string[] {
  switch (metode.toLowerCase()) {
    case "transfer":
      return [
        // TODO: ganti dengan nomor rekening tokomu sendiri.
        "BCA 1234567890 a.n. Pemilik Toko",
        "BNI 0987654321 a.n. Pemilik Toko",
        "Mandiri 2468135790 a.n. Pemilik Toko",
      ];
    case "qris":
      return ["Scan QRIS dengan aplikasi pembayaran apa pun (GoPay, OVO, Dana, m-banking)."];
    case "ewallet":
      return ["Dana / OVO / GoPay / ShopeePay: 0812-3456-789 a.n. Pemilik Toko"];
    default:
      return ["Hubungi kami untuk instruksi pembayaran."];
  }
}

export async function generateMetadata({
  params,
}: {
  params: Promise<PesananDetailParams>;
}): Promise<Metadata> {
  const { id } = await params;
  return { title: `Pesanan #${id.padStart(5, "0")}` };
}

export default async function PesananDetailPage({
  params,
}: {
  params: Promise<PesananDetailParams>;
}) {
  const { id } = await params;
  const orderId = Number(id);

  const session = await auth();
  if (!session?.user) redirect("/login");

  if (!Number.isInteger(orderId) || orderId <= 0) notFound();
  const order = await prisma.order.findUnique({
    where: { id: orderId },
    include: {
      items: { include: { product: true } },
      payments: { orderBy: { tanggal: "desc" } },
    },
  });
  if (!order) notFound();

  const isOwner = order.user_id === Number(session.user.id);
  const isAdmin = session.user.role === "admin";
  if (!isOwner && !isAdmin) notFound();

  const pembayaranTerakhir = order.payments[0];
  const bolehKonfirmasi =
    order.status !== "dibatalkan" &&
    order.status !== "selesai" &&
    (!pembayaranTerakhir ||
      pembayaranTerakhir.status === "menunggu" ||
      pembayaranTerakhir.status === "gagal");

  return (
    <div className="mx-auto w-full max-w-5xl px-4 py-10">
      {/* Header */}
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div>
          <Link href="/pesanan" className="text-sm text-muted-foreground hover:text-chili-700">
            ← Semua Pesanan
          </Link>
          <h1 className="mt-1 font-display text-3xl font-extrabold tracking-tight text-ink">
            Pesanan #{String(order.id).padStart(5, "0")}
          </h1>
          <p className="mt-1 text-sm text-muted-foreground">
            {formatTanggal(order.tanggal, true)} · {metodeLabel(order.metode_pembayaran)}
          </p>
        </div>
        <OrderStatusBadge status={order.status} className="px-3 py-1 text-sm" />
      </div>

      <div className="mt-8 grid items-start gap-8 lg:grid-cols-[1fr_360px]">
        {/* Item + alamat */}
        <div className="space-y-6">
          <div className="rounded-2xl border border-border bg-card p-6 shadow-sm">
            <h2 className="font-display text-lg font-bold text-ink">Item Pesanan</h2>
            <div className="mt-4 divide-y divide-border">
              {order.items.map((item) => (
                <div key={item.id} className="flex items-center gap-4 py-3.5">
                  <div className="relative size-14 shrink-0 overflow-hidden rounded-lg border border-border bg-muted">
                    <Image
                      src={imageUrl(item.product.foto)}
                      alt={item.product.nama}
                      fill
                      sizes="56px"
                      className="object-cover"
                    />
                  </div>
                  <div className="min-w-0 flex-1">
                    <Link
                      href={`/produk/${item.product_id}`}
                      className="block truncate text-sm font-semibold text-ink hover:text-chili-700"
                    >
                      {item.product.nama}
                    </Link>
                    <p className="mt-0.5 font-mono text-xs text-muted-foreground tabular-nums">
                      {formatRupiah(item.harga)} × {item.jumlah}
                    </p>
                  </div>
                  <span className="font-mono text-sm font-bold text-ink tabular-nums">
                    {formatRupiah(Number(item.harga) * item.jumlah)}
                  </span>
                </div>
              ))}
            </div>
            <Separator className="my-4" />
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium">Total Pesanan</span>
              <span className="font-mono text-xl font-bold text-chili-700 tabular-nums">
                {formatRupiah(order.total)}
              </span>
            </div>
          </div>

          <div className="rounded-2xl border border-border bg-card p-6 shadow-sm">
            <h2 className="flex items-center gap-2 font-display text-lg font-bold text-ink">
              <MapPin className="size-4 text-chili-600" />
              Alamat Pengiriman
            </h2>
            <p className="mt-3 whitespace-pre-line text-sm leading-relaxed text-foreground/90">
              {order.alamat}
            </p>
            {order.tanggal_kirim && (
              <p className="mt-3 text-xs text-muted-foreground">
                Tanggal kirim: {formatTanggal(order.tanggal_kirim)}
              </p>
            )}
          </div>
        </div>
        {/* Pembayaran */}
        <aside className="space-y-6">
          <div className="rounded-2xl border border-border bg-card p-6 shadow-sm">
            <h2 className="font-display text-lg font-bold text-ink">Pembayaran</h2>

            {bolehKonfirmasi && (
              <div className="mt-4 rounded-xl border border-mango-400/50 bg-mango-300/20 p-4">
                <p className="text-sm font-semibold text-ink">
                  Total yang harus dibayar
                </p>
                <p className="mt-1 font-mono text-2xl font-bold text-chili-700 tabular-nums">
                  {formatRupiah(order.total)}
                </p>
                <p className="mt-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                  Cara bayar ({metodeLabel(order.metode_pembayaran)})
                </p>
                <ul className="mt-1.5 list-disc space-y-1 pl-4 text-xs leading-relaxed text-muted-foreground">
                  {infoPembayaran(order.metode_pembayaran).map((info) => (
                    <li key={info}>{info}</li>
                  ))}
                </ul>
              </div>
            )}

            {/* Riwayat pembayaran */}
            {order.payments.length > 0 && (
              <div className="mt-4 space-y-3">
                {order.payments.map((p) => (
                  <div key={p.id} className="rounded-xl border border-border p-3.5">
                    <div className="flex items-center justify-between gap-2">
                      <span className="text-sm font-semibold text-ink">{p.metode}</span>
                      <PaymentStatusBadge status={p.status} />
                    </div>
                    <p className="mt-1 text-xs text-muted-foreground">
                      Dikirim {formatTanggal(p.tanggal, true)}
                    </p>
                    {p.bukti_transfer && (
                      <a
                        href={imageUrl(p.bukti_transfer, "")}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="mt-2 inline-block text-xs font-medium text-chili-700 underline underline-offset-2 hover:text-chili-800"
                      >
                        Lihat bukti pembayaran
                      </a>
                    )}
                    {p.notes && (
                      <p className="mt-2 rounded-lg bg-muted px-2.5 py-1.5 text-xs text-muted-foreground">
                        Catatan admin: {p.notes}
                      </p>
                    )}
                  </div>
                ))}
              </div>
            )}

            {/* Form konfirmasi */}
            {bolehKonfirmasi ? (
              <div className="mt-5 border-t border-border pt-5">
                <h3 className="text-sm font-semibold text-ink">Konfirmasi Pembayaran</h3>
                <p className="mt-1 text-xs text-muted-foreground">
                  Sudah transfer? Unggah buktinya di bawah ini.
                </p>
                <div className="mt-3">
                  <PaymentConfirmationForm
                    orderId={order.id}
                    orderMetode={order.metode_pembayaran}
                  />
                </div>
              </div>
            ) : order.status === "dibatalkan" ? (
              <p className="mt-4 rounded-xl bg-muted p-3.5 text-sm text-muted-foreground">
                Pesanan ini dibatalkan. Kalau mau pesan lagi, silakan belanja ulang.
              </p>
            ) : (
              <p className="mt-4 rounded-xl bg-leaf-500/10 p-3.5 text-sm text-leaf-600">
                Pembayaran sudah beres. Terima kasih!
              </p>
            )}
          </div>

          <div className="rounded-2xl border border-border bg-muted/50 p-5 text-xs leading-relaxed text-muted-foreground">
            Butuh bantuan? Hubungi WhatsApp kami di 0812-3456-789 (Senin–Sabtu, 08.00–17.00 WIB).
          </div>
        </aside>
      </div>
    </div>
  );
}
