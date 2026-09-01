import type { Metadata } from "next";
import type { Prisma } from "@prisma/client";
import { prisma } from "@/lib/db";
import { ACTIVITY_TYPES, ACTIVITY_TYPE_LABEL } from "@/lib/activity";
import { formatTanggal } from "@/lib/format";
import { PageHeader } from "@/components/admin/PageHeader";
import { SearchBox } from "@/components/admin/SearchBox";
import { ParamSelect } from "@/components/admin/ParamSelect";
import { ActivityTypeBadge, RoleBadge } from "@/components/admin/badges";
import { EmptyState } from "@/components/admin/EmptyState";
import { PaginationBar, makePageHref } from "@/components/admin/PaginationBar";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";

export const metadata: Metadata = { title: "Log Aktivitas" };

const PER_PAGE = 20;

const TYPE_OPTIONS = Object.values(ACTIVITY_TYPES).map((value) => ({
  value,
  label: ACTIVITY_TYPE_LABEL[value] ?? value,
}));

type LogSearchParams = { q?: string; tipe?: string; page?: string };

export default async function AdminLogPage({
  searchParams,
}: {
  searchParams: Promise<LogSearchParams>;
}) {
  const sp = await searchParams;
  const q = (sp.q ?? "").trim();
  const rawTipe = sp.tipe ?? "";
  const tipe = TYPE_OPTIONS.some((o) => o.value === rawTipe) ? rawTipe : "";
  const page = Math.max(1, Number(sp.page) || 1);

  const where: Prisma.ActivityLogWhereInput = {
    ...(tipe ? { activity_type: tipe } : {}),
    ...(q
      ? {
          OR: [
            { description: { contains: q } },
            { user: { nama: { contains: q } } },
            { user: { email: { contains: q } } },
          ],
        }
      : {}),
  };

  const [total, logs] = await Promise.all([
    prisma.activityLog.count({ where }),
    prisma.activityLog.findMany({
      where,
      orderBy: { created_at: "desc" },
      skip: (page - 1) * PER_PAGE,
      take: PER_PAGE,
      include: {
        user: { select: { id: true, nama: true, email: true, role: true } },
      },
    }),
  ]);

  const totalPages = Math.max(1, Math.ceil(total / PER_PAGE));
  const params = new URLSearchParams();
  if (q) params.set("q", q);
  if (tipe) params.set("tipe", tipe);

  return (
    <>
      <PageHeader
        title="Log Aktivitas"
        description={`${total} aktivitas tercatat — jejak audit pengguna dan admin. Log bersifat hanya-baca.`}
      />

      <div className="flex flex-wrap items-center gap-3">
        <SearchBox placeholder="Cari deskripsi, nama, atau email…" />
        <ParamSelect
          param="tipe"
          value={tipe}
          options={TYPE_OPTIONS}
          placeholder="Semua Tipe"
          ariaLabel="Filter tipe aktivitas"
        />
      </div>

      {logs.length === 0 ? (
        <EmptyState
          title="Tidak ada aktivitas yang cocok"
          description="Ubah filter atau kata kunci pencarian."
        />
      ) : (
        <div className="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
          <Table>
            <TableHeader>
              <TableRow className="bg-muted/40">
                <TableHead className="w-40">Waktu</TableHead>
                <TableHead>Pengguna</TableHead>
                <TableHead>Tipe</TableHead>
                <TableHead>Deskripsi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {logs.map((log) => (
                <TableRow key={log.id}>
                  <TableCell className="whitespace-nowrap font-mono text-xs text-muted-foreground tabular-nums">
                    {formatTanggal(log.created_at, true)}
                  </TableCell>
                  <TableCell>
                    <div className="flex items-center gap-2">
                      <div className="min-w-0">
                        <p className="max-w-40 truncate text-sm font-medium">{log.user.nama}</p>
                        <p className="max-w-40 truncate text-xs text-muted-foreground">
                          {log.user.email}
                        </p>
                      </div>
                      <RoleBadge role={log.user.role} />
                    </div>
                  </TableCell>
                  <TableCell>
                    <ActivityTypeBadge type={log.activity_type} />
                  </TableCell>
                  <TableCell className="max-w-md text-sm text-muted-foreground">
                    {log.description}
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
        buildHref={(n) => makePageHref("/admin/log", params, n)}
      />
    </>
  );
}