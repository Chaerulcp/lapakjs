import type { Metadata } from "next";
import { Pencil, Plus, Trash2 } from "lucide-react";
import { prisma } from "@/lib/db";
import { formatRupiah, imageUrl } from "@/lib/format";
import { PageHeader } from "@/components/admin/PageHeader";
import { ProductFormDialog } from "@/components/admin/ProductFormDialog";
import { ConfirmDialog } from "@/components/admin/ConfirmDialog";
import { deleteProductAction } from "@/app/admin/actions";
import { SearchBox } from "@/components/admin/SearchBox";
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
import { cn } from "@/lib/utils";

export const metadata: Metadata = { title: "Kelola Produk" };

const PER_PAGE = 10;

type ProdukSearchParams = { q?: string; page?: string };

export default async function AdminProdukPage({
  searchParams,
}: {
  searchParams: Promise<ProdukSearchParams>;
}) {
  const sp = await searchParams;
  const q = (sp.q ?? "").trim();
  const page = Math.max(1, Number(sp.page) || 1);

  const where = q
    ? { OR: [{ nama: { contains: q } }, { kategori: { contains: q } }] }
    : {};

  const [total, products] = await Promise.all([
    prisma.product.count({ where }),
    prisma.product.findMany({
      where,
      orderBy: { created_at: "desc" },
      skip: (page - 1) * PER_PAGE,
      take: PER_PAGE,
    }),
  ]);

  const totalPages = Math.max(1, Math.ceil(total / PER_PAGE));
  const params = new URLSearchParams();
  if (q) params.set("q", q);

  return (
    <>
      <PageHeader
        title="Produk"
        description={`${total} produk di katalog Sambal Mama Ana.`}
        actions={
          <ProductFormDialog
            trigger={
              <Button className="bg-chili-600 hover:bg-chili-700">
                <Plus className="size-4" />
                Tambah Produk
              </Button>
            }
          />
        }
      />

      <div className="flex flex-wrap items-center gap-3">
        <SearchBox placeholder="Cari nama atau kategori…" />
        {q ? <p className="text-sm text-muted-foreground">Hasil untuk “{q}”.</p> : null}
      </div>

      {products.length === 0 ? (
        <EmptyState
          title={q ? "Tidak ada produk yang cocok" : "Belum ada produk"}
          description={
            q
              ? "Coba kata kunci lain atau kosongkan pencarian."
              : "Tambahkan produk pertama untuk mulai berjualan."
          }
        />
      ) : (
        <div className="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
          <Table>
            <TableHeader>
              <TableRow className="bg-muted/40">
                <TableHead className="w-16">Foto</TableHead>
                <TableHead>Nama</TableHead>
                <TableHead>Kategori</TableHead>
                <TableHead className="text-right">Harga</TableHead>
                <TableHead className="text-right">Harga Reseller</TableHead>
                <TableHead className="text-center">Stok</TableHead>
                <TableHead className="text-right">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {products.map((p) => (
                <TableRow key={p.id}>
                  <TableCell>
                    {/* eslint-disable-next-line @next/next/no-img-element */}
                    <img
                      src={imageUrl(p.foto)}
                      alt={`Foto ${p.nama}`}
                      className="size-12 rounded-lg border border-border object-cover"
                    />
                  </TableCell>
                  <TableCell>
                    <p className="max-w-56 truncate font-semibold">{p.nama}</p>
                    <p className="text-xs text-muted-foreground">ID #{p.id}</p>
                  </TableCell>
                  <TableCell className="text-sm text-muted-foreground">{p.kategori}</TableCell>
                  <TableCell className="text-right font-mono text-sm tabular-nums">
                    {formatRupiah(p.harga)}
                  </TableCell>
                  <TableCell className="text-right font-mono text-sm text-muted-foreground tabular-nums">
                    {formatRupiah(p.harga_reseller)}
                  </TableCell>
                  <TableCell className="text-center">
                    <span
                      className={cn(
                        "inline-flex min-w-10 justify-center rounded-md px-2 py-0.5 font-mono text-xs font-bold tabular-nums",
                        p.stok === 0
                          ? "bg-destructive/10 text-destructive"
                          : p.stok <= 10
                            ? "bg-mango-400/25 text-amber-700"
                            : "bg-leaf-500/10 text-leaf-600"
                      )}
                    >
                      {p.stok}
                    </span>
                  </TableCell>
                  <TableCell>
                    <div className="flex items-center justify-end gap-1">
                      <ProductFormDialog
                        initial={{
                          id: p.id,
                          nama: p.nama,
                          kategori: p.kategori,
                          deskripsi: p.deskripsi,
                          harga: Number(p.harga),
                          harga_reseller: Number(p.harga_reseller),
                          stok: p.stok,
                          foto: p.foto,
                        }}
                        trigger={
                          <Button
                            variant="ghost"
                            size="icon"
                            className="size-8"
                            aria-label={`Ubah ${p.nama}`}
                          >
                            <Pencil className="size-4" />
                          </Button>
                        }
                      />
                      <ConfirmDialog
                        title={`Hapus "${p.nama}"?`}
                        description="Tindakan ini permanen. Produk yang sudah dipakai pesanan/testimoni tidak dapat dihapus."
                        confirmLabel="Hapus"
                        action={deleteProductAction}
                        fields={{ id: p.id }}
                        trigger={
                          <Button
                            variant="ghost"
                            size="icon"
                            className="size-8 text-destructive hover:bg-destructive/10 hover:text-destructive"
                            aria-label={`Hapus ${p.nama}`}
                          >
                            <Trash2 className="size-4" />
                          </Button>
                        }
                      />
                    </div>
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
        buildHref={(n) => makePageHref("/admin/produk", params, n)}
      />
    </>
  );
}