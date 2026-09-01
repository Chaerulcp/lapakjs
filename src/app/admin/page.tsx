import Link from "next/link";
import type { Metadata } from "next";
import { Banknote, CircleDollarSign, Receipt, TriangleAlert, Users } from "lucide-react";
import { auth } from "@/auth";
import { prisma } from "@/lib/db";
import { getRecentActivity, ACTIVITY_TYPE_LABEL } from "@/lib/activity";
import { formatRupiah, formatTanggal, ORDER_STATUS_LABEL } from "@/lib/format";
import { OrderStatusBadge } from "@/components/site/StatusBadge";
import { StatCard } from "@/components/admin/StatCard";
import { SalesChart, type SalesPoint } from "@/components/admin/SalesChart";
import { StatusDonutChart, type StatusSlice } from "@/components/admin/StatusDonutChart";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export const metadata: Metadata = { title: "Dashboard" };

/** Warna irisan donat per status pesanan (selaras palette brand). */
const STATUS_COLOR: Record<string, string> = {
  menunggu: "#f7b32b",
  diproses: "#e8590c",
  dikirim: "#e15b45",
  selesai: "#4c8249",
  dibatalkan: "#8a7a68",
};

const DAY_LABEL = new Intl.DateTimeFormat("id-ID", { day: "numeric", month: "short" });

export default async function AdminDashboardPage() {
  const session = await auth();
  const now = new Date();
  const since = new Date(now);
  since.setDate(now.getDate() - 13);
  since.setHours(0, 0, 0, 0);

  const [summary, pendingPayments, usersCount, productsCount, orders14, statusGroups, recentOrders, recentActivity, lowStock] =
    await Promise.all([
      // Pendapatan & jumlah pesanan seumur hidup (di luar pesanan dibatalkan).
      prisma.order.aggregate({
        where: { status: { not: "dibatalkan" } },
        _count: { _all: true },
        _sum: { total: true },
      }),
      prisma.payment.count({ where: { status: "menunggu" } }),
      prisma.user.count(),
      prisma.product.count(),
      prisma.order.findMany({
        where: { tanggal: { gte: since }, status: { not: "dibatalkan" } },
        select: { tanggal: true, total: true },
      }),
      prisma.order.groupBy({ by: ["status"], _count: { _all: true } }),
      prisma.order.findMany({
        take: 5,
        orderBy: { tanggal: "desc" },
        include: { user: { select: { nama: true, email: true } } },
      }),
      getRecentActivity(8),
      prisma.product.findMany({
        where: { stok: { lte: 10 } },
        orderBy: { stok: "asc" },
        take: 6,
        select: { id: true, nama: true, stok: true },
      }),
    ]);

  const pendapatan = Number(summary._sum.total ?? 0);

  // ---- Seri grafik pendapatan 14 hari ----
  const byDay = new Map<string, { total: number; pesanan: number }>();
  for (const o of orders14) {
    const key = o.tanggal.toISOString().slice(0, 10);
    const cur = byDay.get(key) ?? { total: 0, pesanan: 0 };
    cur.total += Number(o.total);
    cur.pesanan += 1;
    byDay.set(key, cur);
  }
  const sales: SalesPoint[] = [];
  for (let i = 13; i >= 0; i--) {
    const d = new Date(now);
    d.setDate(now.getDate() - i);
    const key = d.toISOString().slice(0, 10);
    const hit = byDay.get(key);
    sales.push({
      label: DAY_LABEL.format(d),
      total: hit?.total ?? 0,
      pesanan: hit?.pesanan ?? 0,
    });
  }

  // ---- Irisan donat status pesanan ----
  const slices: StatusSlice[] = statusGroups
    .map((g) => ({
      status: g.status,
      label: ORDER_STATUS_LABEL[g.status] ?? g.status,
      jumlah: g._count._all,
      color: STATUS_COLOR[g.status] ?? "#7a6a5a",
    }))
    .sort((a, b) => b.jumlah - a.jumlah);

  const adminName = session?.user?.name ?? "Admin";

  return (
    <>
      <div>
        <h1 className="font-display text-2xl font-extrabold tracking-tight text-ink">
          Halo, {adminName} 👋
        </h1>
        <p className="mt-1 text-sm text-muted-foreground">
          Ringkasan toko hari ini, {formatTanggal(now)}. Semua angka diambil langsung dari
          database.
        </p>
      </div>

      {/* Kartu metrik */}
      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard
          label="Pendapatan"
          value={formatRupiah(pendapatan)}
          hint="Total pesanan non-dibatalkan"
          icon={CircleDollarSign}
          tone="chili"
        />
        <StatCard
          label="Total Pesanan"
          value={String(summary._count._all)}
          hint={`${orders14.length} dalam 14 hari terakhir`}
          icon={Receipt}
          tone="ember"
        />
        <StatCard
          label="Pembayaran Menunggu"
          value={String(pendingPayments)}
          hint="Perlu diverifikasi"
          icon={Banknote}
          tone="mango"
        />
        <StatCard
          label="Pengguna"
          value={String(usersCount)}
          hint={`${productsCount} produk terdaftar`}
          icon={Users}
          tone="leaf"
        />
      </div>

      {/* Grafik */}
      <div className="grid gap-4 lg:grid-cols-3">
        <Card className="lg:col-span-2">
          <CardHeader>
            <CardTitle className="font-display text-base">Pendapatan 14 Hari Terakhir</CardTitle>
          </CardHeader>
          <CardContent>
            <SalesChart data={sales} />
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="font-display text-base">Pesanan per Status</CardTitle>
          </CardHeader>
          <CardContent>
            {slices.length > 0 ? (
              <StatusDonutChart data={slices} />
            ) : (
              <p className="py-10 text-center text-sm text-muted-foreground">
                Belum ada pesanan.
              </p>
            )}
          </CardContent>
        </Card>
      </div>

      {/* Baris bawah: pesanan terbaru, aktivitas, stok menipis */}
      <div className="grid gap-4 lg:grid-cols-3">
        <Card>
          <CardHeader className="flex-row items-center justify-between space-y-0">
            <CardTitle className="font-display text-base">Pesanan Terbaru</CardTitle>
            <Button asChild variant="ghost" size="sm" className="text-chili-700">
              <Link href="/admin/pesanan">Semua →</Link>
            </Button>
          </CardHeader>
          <CardContent className="space-y-3">
            {recentOrders.length === 0 ? (
              <p className="py-6 text-center text-sm text-muted-foreground">Belum ada pesanan.</p>
            ) : (
              recentOrders.map((order) => (
                <Link
                  key={order.id}
                  href={`/admin/pesanan/${order.id}`}
                  className="flex items-center justify-between gap-3 rounded-lg border border-border px-3 py-2 transition-colors hover:bg-muted/60"
                >
                  <span className="min-w-0">
                    <span className="block truncate text-sm font-semibold">
                      #{order.id} • {order.user.nama}
                    </span>
                    <span className="block text-xs text-muted-foreground">
                      {formatTanggal(order.tanggal, true)}
                    </span>
                  </span>
                  <span className="flex shrink-0 flex-col items-end gap-1">
                    <span className="font-mono text-xs font-bold tabular-nums">
                      {formatRupiah(order.total)}
                    </span>
                    <OrderStatusBadge status={order.status} />
                  </span>
                </Link>
              ))
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex-row items-center justify-between space-y-0">
            <CardTitle className="font-display text-base">Aktivitas Terbaru</CardTitle>
            <Button asChild variant="ghost" size="sm" className="text-chili-700">
              <Link href="/admin/log">Semua →</Link>
            </Button>
          </CardHeader>
          <CardContent>
            {recentActivity.length === 0 ? (
              <p className="py-6 text-center text-sm text-muted-foreground">Belum ada aktivitas.</p>
            ) : (
              <ul className="space-y-2.5">
                {recentActivity.map((log) => (
                  <li key={log.id} className="flex items-start gap-2.5 text-xs">
                    <span className="mt-1 size-1.5 shrink-0 rounded-full bg-chili-500" />
                    <span className="min-w-0">
                      <span className="block font-semibold text-foreground">
                        {ACTIVITY_TYPE_LABEL[log.activity_type] ?? log.activity_type}
                        <span className="font-normal text-muted-foreground">
                          {" "}
                          • {log.user.nama}
                        </span>
                      </span>
                      <span className="mt-0.5 line-clamp-2 block text-muted-foreground">
                        {log.description}
                      </span>
                      <span className="mt-0.5 block text-[11px] text-muted-foreground/80">
                        {formatTanggal(log.created_at, true)}
                      </span>
                    </span>
                  </li>
                ))}
              </ul>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex-row items-center justify-between space-y-0">
            <CardTitle className="flex items-center gap-1.5 font-display text-base">
              <TriangleAlert className="size-4 text-mango-500" />
              Stok Menipis
            </CardTitle>
            <Button asChild variant="ghost" size="sm" className="text-chili-700">
              <Link href="/admin/produk">Kelola →</Link>
            </Button>
          </CardHeader>
          <CardContent>
            {lowStock.length === 0 ? (
              <p className="py-6 text-center text-sm text-muted-foreground">
                Semua stok aman. Mantap!
              </p>
            ) : (
              <ul className="space-y-2">
                {lowStock.map((p) => (
                  <li
                    key={p.id}
                    className="flex items-center justify-between gap-3 rounded-lg border border-border px-3 py-2 text-sm"
                  >
                    <span className="truncate">{p.nama}</span>
                    <span
                      className={
                        p.stok === 0
                          ? "shrink-0 rounded-md bg-destructive/10 px-2 py-0.5 font-mono text-xs font-bold text-destructive tabular-nums"
                          : "shrink-0 rounded-md bg-mango-400/25 px-2 py-0.5 font-mono text-xs font-bold text-amber-700 tabular-nums"
                      }
                    >
                      sisa {p.stok}
                    </span>
                  </li>
                ))}
              </ul>
            )}
          </CardContent>
        </Card>
      </div>
    </>
  );
}