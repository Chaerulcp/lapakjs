import Link from "next/link";
import type { Metadata } from "next";
import { BadgeCheck, ExternalLink, XCircle } from "lucide-react";
import type { Prisma } from "@prisma/client";
import { prisma } from "@/lib/db";
import { formatRupiah, formatTanggal, imageUrl, PAYMENT_STATUS_LABEL } from "@/lib/format";
import { metodeLabel, PaymentStatusBadge } from "@/components/site/StatusBadge";
import { PageHeader } from "@/components/admin/PageHeader";
import { ParamSelect } from "@/components/admin/ParamSelect";
import { PaymentVerifyDialog } from "@/components/admin/PaymentVerifyDialog";
import { EmptyState } from "@/components/admin/EmptyState";
import { PaginationBar, makePageHref } from "@/components/admin/PaginationBar";
import { Button } from "@/components/ui/button";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";

export const metadata: Metadata = { title: "Verifikasi Pembayaran" };

const PER_PAGE = 10;

const STATUS_OPTIONS = Object.entries(PAYMENT_STATUS_LABEL).map(([value, label]) => ({
  value,
  label,
}));

type PembayaranSearchParams = { status?: string; page?: string };

export default async function AdminPembayaranPage({
  searchParams,
}: {
  searchParams: Promise<PembayaranSearchParams>;
}) {
  const sp = await searchParams;
  const rawStatus = sp.status ?? "";
  const status = STATUS_OPTIONS.some((o) => o.value === rawStatus) ? rawStatus : "";
  const page = Math.max(1, Number(sp.page) || 1);

  const where: Prisma.PaymentWhereInput = {
    ...(status ? { status: status as Prisma.PaymentWhereInput["status"] } : {}),
  };

  const [total, menungguCount, payments] = await Promise.all([
    prisma.payment.count({ where }),
    prisma.payment.count({ where: { status: "menunggu" } }),
    prisma.payment.findMany({
      where,
      // "menunggu" lebih dulu (desc alfabetis), lalu bukti terbaru.
      orderBy: [{ status: "desc" }, { tanggal: "desc" }],
      skip: (page - 1) * PER_PAGE,
      take: PER_PAGE,
      include: {
        order: {
          select: {
            id: true,
            total: true,
            user: { select: { nama: true, email: true } },
          },
        },
        verifiedBy: { select: { nama: true } },
      },
    }),
  ]);

  const totalPages = Math.max(1, Math.ceil(total / PER_PAGE));
  const params = new URLSearchParams();
  if (status) params.set("status", status);

  return (
    <>
      <PageHeader
        title="Pembayaran"
        description={`${menungguCount} pembayaran menunggu verifikasi. Konfirmasi akan otomatis memproses pesanan terkait.`}
      />

      <div className="flex flex-wrap items-center gap-3">
        <ParamSelect
          param="status"
          value={status}
          options={STATUS_OPTIONS}
          placeholder="Semua Status"
          ariaLabel="Filter status pembayaran"
        />
      </div>

      {payments.length === 0 ? (
        <EmptyState
          title="Tidak ada pembayaran"
          description="Ubah filter status untuk melihat pembayaran lain."
        />
      ) : (
        <div className="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
          <Table>
            <TableHeader>
              <TableRow className="bg-muted/40">
                <TableHead>Pembayaran</TableHead>
                <TableHead>Pesanan</TableHead>
                <TableHead>Pelanggan</TableHead>
                <TableHead className="text-right">Total</TableHead>
                <TableHead>Bukti</TableHead>
                <TableHead>Status</TableHead>
                <TableHead className="text-right">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {payments.map((payment) => (
                <TableRow key={payment.id}>
                  <TableCell>
                    <p className="font-semibold">#{payment.id}</p>
                    <p className="text-xs text-muted-foreground">
                      {formatTanggal(payment.tanggal, true)}
                    </p>
                    <p className="text-xs text-muted-foreground">
                      {metodeLabel(payment.metode)}
                    </p>
                  </TableCell>
                  <TableCell>
                    <Link
                      href={`/admin/pesanan/${payment.order.id}`}
                      className="text-sm font-medium text-chili-700 hover:underline"
                    >
                      #{payment.order.id}
                    </Link>
                  </TableCell>
                  <TableCell>
                    <p className="max-w-40 truncate text-sm font-medium">
                      {payment.order.user.nama}
                    </p>
                    <p className="max-w-40 truncate text-xs text-muted-foreground">
                      {payment.order.user.email}
                    </p>
                  </TableCell>
                  <TableCell className="text-right font-mono text-sm font-semibold tabular-nums">
                    {formatRupiah(payment.order.total)}
                  </TableCell>
                  <TableCell>
                    {payment.bukti_transfer ? (
                      <a
                        href={imageUrl(payment.bukti_transfer, "")}
                        target="_blank"
                        rel="noreferrer"
                        className="inline-flex items-center gap-1 text-sm font-medium text-chili-700 hover:underline"
                      >
                        <ExternalLink className="size-3.5" />
                        Lihat Bukti
                      </a>
                    ) : (
                      <span className="text-sm text-muted-foreground">—</span>
                    )}
                  </TableCell>
                  <TableCell>
                    <div className="flex flex-col items-start gap-1">
                      <PaymentStatusBadge status={payment.status} />
                      {payment.verified_at ? (
                        <p className="text-[11px] text-muted-foreground">
                          oleh {payment.verifiedBy?.nama ?? "-"} •{" "}
                          {formatTanggal(payment.verified_at)}
                        </p>
                      ) : null}
                      {payment.notes ? (
                        <p className="max-w-36 truncate text-[11px] text-muted-foreground" title={payment.notes}>
                          “{payment.notes}”
                        </p>
                      ) : null}
                    </div>
                  </TableCell>
                  <TableCell>
                    {payment.status === "menunggu" ? (
                      <div className="flex items-center justify-end gap-1.5">
                        <PaymentVerifyDialog
                          paymentId={payment.id}
                          orderId={payment.order.id}
                          orderTotal={Number(payment.order.total)}
                          keputusan="konfirmasi"
                          trigger={
                            <Button size="sm" variant="outline" className="h-8 border-leaf-500/50 text-leaf-600 hover:bg-leaf-500/10 hover:text-leaf-600">
                              <BadgeCheck className="size-4" />
                              Konfirmasi
                            </Button>
                          }
                        />
                        <PaymentVerifyDialog
                          paymentId={payment.id}
                          orderId={payment.order.id}
                          orderTotal={Number(payment.order.total)}
                          keputusan="gagal"
                          trigger={
                            <Button size="sm" variant="outline" className="h-8 border-destructive/40 text-destructive hover:bg-destructive/10 hover:text-destructive">
                              <XCircle className="size-4" />
                              Tolak
                            </Button>
                          }
                        />
                      </div>
                    ) : (
                      <p className="text-right text-xs text-muted-foreground">
                        Sudah diverifikasi
                      </p>
                    )}
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </div>
      )}

      <PaginationBar
        page={page}
        totalPages={totalPages}
        buildHref={(n) => makePageHref("/admin/pembayaran", params, n)}
      />
    </>
  );
}