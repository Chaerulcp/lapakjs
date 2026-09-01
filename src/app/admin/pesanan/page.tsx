import Link from "next/link";
import type { Metadata } from "next";
import { Eye } from "lucide-react";
import type { Prisma } from "@prisma/client";
import { prisma } from "@/lib/db";
import { formatRupiah, formatTanggal, ORDER_STATUS_LABEL } from "@/lib/format";
import { metodeLabel, OrderStatusBadge } from "@/components/site/StatusBadge";
import { PageHeader } from "@/components/admin/PageHeader";
import { SearchBox } from "@/components/admin/SearchBox";
import { ParamSelect } from "@/components/admin/ParamSelect";
import { OrderStatusForm } from "@/components/admin/OrderStatusForm";
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

export const metadata: Metadata = { title: "Kelola Pesanan" };

const PER_PAGE = 10;

const STATUS_OPTIONS = Object.entries(ORDER_STATUS_LABEL).map(([value, label]) => ({
  value,
  label,
}));

type PesananSearchParams = { q?: string; status?: string; page?: string };

export default async function AdminPesananPage({
  searchParams,
}: {
  searchParams: Promise<PesananSearchParams>;
}) {
  const sp = await searchParams;
  const q = (sp.q ?? "").trim();
  const rawStatus = sp.status ?? "";
  const status = STATUS_OPTIONS.some((o) => o.value === rawStatus) ? rawStatus : "";
  const page = Math.max(1, Number(sp.page) || 1);

  const where: Prisma.OrderWhereInput = {
    ...(status ? { status: status as Prisma.OrderWhereInput["status"] } : {}),
    ...(q
      ? {
          OR: [
            ...(q.match(/^\d+$/) ? [{ id: Number(q) }] : []),
            { user: { nama: { contains: q } } },
            { user: { email: { contains: q } } },
          ],
        }
      : {}),
  };

  const [total, orders] = await Promise.all([
    prisma.order.count({ where }),
    prisma.order.findMany({
      where,
      orderBy: { tanggal: "desc" },
      skip: (page - 1) * PER_PAGE,
      take: PER_PAGE,
      include: {
        user: { select: { nama: true, email: true } },
        items: { select: { jumlah: true } },
      },
    }),
  ]);

  const totalPages = Math.max(1, Math.ceil(total / PER_PAGE));
  const params = new URLSearchParams();
  if (q) params.set("q", q);
  if (status) params.set("status", status);

  return (
    <>
      <PageHeader
        title="Pesanan"
        description={`${total} pesanan tercatat. Ubah status langsung dari tabel atau buka detail untuk melihat item.`}
      />

      <div className="flex flex-wrap items-center gap-3">
        <SearchBox placeholder="Cari #ID, nama, atau email…" />
        <ParamSelect
          param="status"
          value={status}
          options={STATUS_OPTIONS}
          placeholder="Semua Status"
          ariaLabel="Filter status pesanan"
        />
      </div>

      {orders.length === 0 ? (
        <EmptyState
          title="Tidak ada pesanan"
          description="Ubah filter atau kata kunci pencarian untuk melihat pesanan lain."
        />
      ) : (
        <div className="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
          <Table>
            <TableHeader>
              <TableRow className="bg-muted/40">
                <TableHead>Pesanan</TableHead>
                <TableHead>Pelanggan</TableHead>
                <TableHead className="text-center">Item</TableHead>
                <TableHead className="text-right">Total</TableHead>
                <TableHead>Metode</TableHead>
                <TableHead>Status</TableHead>
                <TableHead className="text-right">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {orders.map((order) => {
                const jumlahItem = order.items.reduce((sum, i) => sum + i.jumlah, 0);
                return (
                  <TableRow key={order.id}>
                    <TableCell>
                      <p className="font-semibold">#{order.id}</p>
                      <p className="text-xs text-muted-foreground">
                        {formatTanggal(order.tanggal, true)}
                      </p>
                    </TableCell>
                    <TableCell>
                      <p className="max-w-44 truncate text-sm font-medium">{order.user.nama}</p>
                      <p className="max-w-44 truncate text-xs text-muted-foreground">
                        {order.user.email}
                      </p>
                    </TableCell>
                    <TableCell className="text-center font-mono text-sm tabular-nums">
                      {jumlahItem}
                    </TableCell>
                    <TableCell className="text-right font-mono text-sm font-semibold tabular-nums">
                      {formatRupiah(order.total)}
                    </TableCell>
                    <TableCell className="text-sm text-muted-foreground">
                      {metodeLabel(order.metode_pembayaran)}
                    </TableCell>
                    <TableCell>
                      <div className="flex flex-col items-start gap-1.5">
                        <OrderStatusBadge status={order.status} />
                        <OrderStatusForm orderId={order.id} status={order.status} />
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="flex justify-end">
                        <Button asChild variant="ghost" size="icon" className="size-8" aria-label={`Detail pesanan #${order.id}`}>
                          <Link href={`/admin/pesanan/${order.id}`}>
                            <Eye className="size-4" />
                          </Link>
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                );
              })}
            </TableBody>
          </Table>
        </div>
      )}

      <PaginationBar
        page={page}
        totalPages={totalPages}
        buildHref={(n) => makePageHref("/admin/pesanan", params, n)}
      />
    </>
  );
}