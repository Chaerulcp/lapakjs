import Link from "next/link";
import { notFound } from "next/navigation";
import type { Metadata } from "next";
import { ChevronRight, MapPin, User } from "lucide-react";
import { prisma } from "@/lib/db";
import { formatRupiah, formatTanggal, imageUrl } from "@/lib/format";
import { metodeLabel, OrderStatusBadge, PaymentStatusBadge } from "@/components/site/StatusBadge";
import { OrderStatusForm } from "@/components/admin/OrderStatusForm";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Separator } from "@/components/ui/separator";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";

export const metadata: Metadata = { title: "Detail Pesanan" };

type DetailParams = { id: string };

export default async function AdminPesananDetailPage({
  params,
}: {
  params: Promise<DetailParams>;
}) {
  const { id } = await params;
  const orderId = Number(id);
  if (!Number.isInteger(orderId) || orderId <= 0) notFound();

  const order = await prisma.order.findUnique({
    where: { id: orderId },
    include: {
      user: { select: { id: true, nama: true, email: true, no_hp: true } },
      items: {
        include: { product: { select: { id: true, nama: true, foto: true } } },
      },
      payments: {
        orderBy: { tanggal: "desc" },
        include: { verifiedBy: { select: { nama: true } } },
      },
    },
  });
  if (!order) notFound();

  return (
    <>
      {/* Breadcrumb */}
      <nav className="flex items-center gap-1.5 text-sm text-muted-foreground">
        <Link href="/admin/pesanan" className="hover:text-chili-700">
          Pesanan
        </Link>
        <ChevronRight className="size-3.5" />
        <span className="text-foreground">Pesanan #{order.id}</span>
      </nav>

      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="font-display text-2xl font-extrabold tracking-tight text-ink">
            Pesanan #{order.id}
          </h1>
          <p className="mt-1 text-sm text-muted-foreground">
            Dibuat {formatTanggal(order.tanggal, true)} •{" "}
            {metodeLabel(order.metode_pembayaran)}
            {order.tanggal_kirim ? ` • Dikirim ${formatTanggal(order.tanggal_kirim)}` : ""}
          </p>
        </div>
        <div className="flex items-center gap-3">
          <OrderStatusBadge status={order.status} />
          <OrderStatusForm orderId={order.id} status={order.status} />
        </div>
      </div>

      <div className="grid items-start gap-4 lg:grid-cols-3">
        {/* Item pesanan */}
        <Card className="lg:col-span-2">
          <CardHeader>
            <CardTitle className="font-display text-base">Item Pesanan</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow className="bg-muted/40">
                  <TableHead>Produk</TableHead>
                  <TableHead className="text-center">Jumlah</TableHead>
                  <TableHead className="text-right">Harga</TableHead>
                  <TableHead className="text-right">Subtotal</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {order.items.map((item) => (
                  <TableRow key={item.id}>
                    <TableCell>
                      <div className="flex items-center gap-3">
                        {/* eslint-disable-next-line @next/next/no-img-element */}
                        <img
                          src={imageUrl(item.product.foto)}
                          alt={`Foto ${item.product.nama}`}
                          className="size-10 rounded-md border border-border object-cover"
                        />
                        <span className="max-w-60 truncate text-sm font-medium">
                          {item.product.nama}
                        </span>
                      </div>
                    </TableCell>
                    <TableCell className="text-center font-mono text-sm tabular-nums">
                      {item.jumlah}
                    </TableCell>
                    <TableCell className="text-right font-mono text-sm tabular-nums">
                      {formatRupiah(item.harga)}
                    </TableCell>
                    <TableCell className="text-right font-mono text-sm font-semibold tabular-nums">
                      {formatRupiah(Number(item.harga) * item.jumlah)}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
            <Separator className="my-4" />
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium">Total Pesanan</span>
              <span className="font-mono text-lg font-bold text-chili-700 tabular-nums">
                {formatRupiah(order.total)}
              </span>
            </div>
          </CardContent>
        </Card>

        {/* Pelanggan & alamat */}
        <div className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-1.5 font-display text-base">
                <User className="size-4 text-chili-600" />
                Pelanggan
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-1 text-sm">
              <p className="font-semibold">{order.user.nama}</p>
              <p className="text-muted-foreground">{order.user.email}</p>
              <p className="font-mono text-muted-foreground tabular-nums">{order.user.no_hp}</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-1.5 font-display text-base">
                <MapPin className="size-4 text-chili-600" />
                Alamat Pengiriman
              </CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-sm leading-relaxed text-muted-foreground">{order.alamat}</p>
            </CardContent>
          </Card>
        </div>
      </div>

      {/* Riwayat pembayaran */}
      <Card>
        <CardHeader className="flex-row items-center justify-between space-y-0">
          <CardTitle className="font-display text-base">Pembayaran</CardTitle>
          <Button asChild variant="ghost" size="sm" className="text-chili-700">
            <Link href="/admin/pembayaran">Kelola Pembayaran →</Link>
          </Button>
        </CardHeader>
        <CardContent>
          {order.payments.length === 0 ? (
            <p className="py-4 text-center text-sm text-muted-foreground">
              Belum ada bukti pembayaran yang diunggah.
            </p>
          ) : (
            <div className="space-y-3">
              {order.payments.map((payment) => (
                <div
                  key={payment.id}
                  className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-border px-4 py-3"
                >
                  <div className="min-w-0 text-sm">
                    <p className="font-semibold">
                      #{payment.id} • {metodeLabel(payment.metode)}
                    </p>
                    <p className="text-xs text-muted-foreground">
                      Diunggah {formatTanggal(payment.tanggal, true)}
                      {payment.verified_at
                        ? ` • Diverifikasi ${payment.verifiedBy?.nama ?? "-"} ${formatTanggal(payment.verified_at, true)}`
                        : ""}
                    </p>
                    {payment.notes ? (
                      <Badge variant="outline" className="mt-1.5 max-w-full truncate font-normal">
                        {payment.notes}
                      </Badge>
                    ) : null}
                  </div>
                  <div className="flex items-center gap-3">
                    {payment.bukti_transfer ? (
                      <Button asChild variant="outline" size="sm">
                        <a
                          href={imageUrl(payment.bukti_transfer, "")}
                          target="_blank"
                          rel="noreferrer"
                        >
                          Lihat Bukti
                        </a>
                      </Button>
                    ) : null}
                    <PaymentStatusBadge status={payment.status} />
                  </div>
                </div>
              ))}
            </div>
          )}
        </CardContent>
      </Card>
    </>
  );
}