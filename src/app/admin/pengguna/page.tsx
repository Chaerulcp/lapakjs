import type { Metadata } from "next";
import { BadgeCheck, CircleX } from "lucide-react";
import type { Prisma } from "@prisma/client";
import { auth } from "@/auth";
import { prisma } from "@/lib/db";
import { formatTanggal } from "@/lib/format";
import { PageHeader } from "@/components/admin/PageHeader";
import { SearchBox } from "@/components/admin/SearchBox";
import { ParamSelect } from "@/components/admin/ParamSelect";
import { RoleBadge, UserStatusBadge } from "@/components/admin/badges";
import { UserRoleForm, UserStatusForm } from "@/components/admin/UserAdminForms";
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

export const metadata: Metadata = { title: "Kelola Pengguna" };

const PER_PAGE = 10;

const ROLE_OPTIONS = [
  { value: "admin", label: "Admin" },
  { value: "reseller", label: "Reseller" },
  { value: "pelanggan", label: "Pelanggan" },
];

type PenggunaSearchParams = { q?: string; role?: string; page?: string };

export default async function AdminPenggunaPage({
  searchParams,
}: {
  searchParams: Promise<PenggunaSearchParams>;
}) {
  const sp = await searchParams;
  const session = await auth();
  const adminId = Number(session?.user?.id ?? 0);

  const q = (sp.q ?? "").trim();
  const rawRole = sp.role ?? "";
  const role = ROLE_OPTIONS.some((o) => o.value === rawRole) ? rawRole : "";
  const page = Math.max(1, Number(sp.page) || 1);

  const where: Prisma.UserWhereInput = {
    ...(role ? { role: role as Prisma.UserWhereInput["role"] } : {}),
    ...(q
      ? { OR: [{ nama: { contains: q } }, { email: { contains: q } }, { no_hp: { contains: q } }] }
      : {}),
  };

  const [total, users] = await Promise.all([
    prisma.user.count({ where }),
    prisma.user.findMany({
      where,
      orderBy: { created_at: "desc" },
      skip: (page - 1) * PER_PAGE,
      take: PER_PAGE,
      select: {
        id: true,
        nama: true,
        email: true,
        no_hp: true,
        role: true,
        status: true,
        is_verified: true,
        created_at: true,
        _count: { select: { orders: true } },
      },
    }),
  ]);

  const totalPages = Math.max(1, Math.ceil(total / PER_PAGE));
  const params = new URLSearchParams();
  if (q) params.set("q", q);
  if (role) params.set("role", role);

  return (
    <>
      <PageHeader
        title="Pengguna"
        description={`${total} akun terdaftar. Ubah role/status langsung dari tabel — perubahan tercatat di log aktivitas.`}
      />

      <div className="flex flex-wrap items-center gap-3">
        <SearchBox placeholder="Cari nama, email, atau no. HP…" />
        <ParamSelect
          param="role"
          value={role}
          options={ROLE_OPTIONS}
          placeholder="Semua Role"
          ariaLabel="Filter role pengguna"
        />
      </div>

      {users.length === 0 ? (
        <EmptyState
          title="Tidak ada pengguna yang cocok"
          description="Ubah filter atau kata kunci pencarian."
        />
      ) : (
        <div className="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
          <Table>
            <TableHeader>
              <TableRow className="bg-muted/40">
                <TableHead>Pengguna</TableHead>
                <TableHead>No. HP</TableHead>
                <TableHead className="text-center">Verifikasi</TableHead>
                <TableHead className="text-center">Pesanan</TableHead>
                <TableHead>Role</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Terdaftar</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {users.map((user) => {
                const isSelf = user.id === adminId;
                return (
                  <TableRow key={user.id}>
                    <TableCell>
                      <p className="max-w-52 truncate font-semibold">
                        {user.nama}
                        {isSelf ? (
                          <span className="ml-1.5 rounded bg-chili-600/10 px-1.5 py-0.5 text-[10px] font-bold text-chili-700 align-middle">
                            ANDA
                          </span>
                        ) : null}
                      </p>
                      <p className="max-w-52 truncate text-xs text-muted-foreground">
                        {user.email}
                      </p>
                    </TableCell>
                    <TableCell className="font-mono text-sm text-muted-foreground tabular-nums">
                      {user.no_hp}
                    </TableCell>
                    <TableCell className="text-center">
                      {user.is_verified ? (
                        <BadgeCheck className="mx-auto size-4.5 text-leaf-600" aria-label="Email terverifikasi" />
                      ) : (
                        <CircleX className="mx-auto size-4.5 text-muted-foreground/60" aria-label="Email belum terverifikasi" />
                      )}
                    </TableCell>
                    <TableCell className="text-center font-mono text-sm tabular-nums">
                      {user._count.orders}
                    </TableCell>
                    <TableCell>
                      <div className="flex flex-col items-start gap-1.5">
                        <RoleBadge role={user.role} />
                        <UserRoleForm userId={user.id} role={user.role} disabled={isSelf} />
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="flex flex-col items-start gap-1.5">
                        <UserStatusBadge status={user.status} />
                        <UserStatusForm userId={user.id} status={user.status} disabled={isSelf} />
                      </div>
                    </TableCell>
                    <TableCell className="text-sm text-muted-foreground">
                      {formatTanggal(user.created_at)}
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
        buildHref={(n) => makePageHref("/admin/pengguna", params, n)}
      />
    </>
  );
}