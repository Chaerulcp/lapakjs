import type { Metadata } from "next";
import { Pencil, Plus, Trash2, Video } from "lucide-react";
import { prisma } from "@/lib/db";
import { formatTanggal, imageUrl } from "@/lib/format";
import { deleteContentAction } from "@/app/admin/actions";
import { PageHeader } from "@/components/admin/PageHeader";
import { ContentFormDialog } from "@/components/admin/ContentFormDialog";
import { ConfirmDialog } from "@/components/admin/ConfirmDialog";
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

export const metadata: Metadata = { title: "Kelola Konten" };

const PER_PAGE = 10;

type KontenSearchParams = { page?: string };

export default async function AdminKontenPage({
  searchParams,
}: {
  searchParams: Promise<KontenSearchParams>;
}) {
  const sp = await searchParams;
  const page = Math.max(1, Number(sp.page) || 1);

  const [total, contents] = await Promise.all([
    prisma.content.count(),
    prisma.content.findMany({
      orderBy: { tanggal: "desc" },
      skip: (page - 1) * PER_PAGE,
      take: PER_PAGE,
    }),
  ]);

  const totalPages = Math.max(1, Math.ceil(total / PER_PAGE));
  const params = new URLSearchParams();

  return (
    <>
      <PageHeader
        title="Konten"
        description={`${total} konten (artikel, resep, promo) yang tampil di situs publik.`}
        actions={
          <ContentFormDialog
            trigger={
              <Button className="bg-chili-600 hover:bg-chili-700">
                <Plus className="size-4" />
                Tambah Konten
              </Button>
            }
          />
        }
      />

      {contents.length === 0 ? (
        <EmptyState
          title="Belum ada konten"
          description="Tambahkan artikel, resep, atau promo pertama untuk meramaikan situs."
        />
      ) : (
        <div className="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
          <Table>
            <TableHeader>
              <TableRow className="bg-muted/40">
                <TableHead className="w-16">Gambar</TableHead>
                <TableHead>Judul</TableHead>
                <TableHead>Penulis</TableHead>
                <TableHead>Tanggal</TableHead>
                <TableHead className="text-center">Video</TableHead>
                <TableHead className="text-right">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {contents.map((content) => (
                <TableRow key={content.id}>
                  <TableCell>
                    {content.gambar ? (
                      // eslint-disable-next-line @next/next/no-img-element
                      <img
                        src={imageUrl(content.gambar, "")}
                        alt={`Gambar ${content.judul}`}
                        className="size-12 rounded-lg border border-border object-cover"
                      />
                    ) : (
                      <span className="flex size-12 items-center justify-center rounded-lg border border-dashed border-border text-muted-foreground">
                        <span className="text-base">📝</span>
                      </span>
                    )}
                  </TableCell>
                  <TableCell>
                    <p className="max-w-72 truncate font-semibold">{content.judul}</p>
                    <p className="max-w-72 truncate text-xs text-muted-foreground">
                      {content.isi.slice(0, 80)}
                      {content.isi.length > 80 ? "…" : ""}
                    </p>
                  </TableCell>
                  <TableCell className="text-sm text-muted-foreground">
                    {content.penulis}
                  </TableCell>
                  <TableCell className="text-sm text-muted-foreground">
                    {formatTanggal(content.tanggal)}
                  </TableCell>
                  <TableCell className="text-center">
                    {content.video ? (
                      <a
                        href={content.video}
                        target="_blank"
                        rel="noreferrer"
                        aria-label={`Video untuk ${content.judul}`}
                        className="inline-flex size-7 items-center justify-center rounded-md bg-chili-600/10 text-chili-700 transition-colors hover:bg-chili-600/20"
                      >
                        <Video className="size-4" />
                      </a>
                    ) : (
                      <span className="text-muted-foreground/60">—</span>
                    )}
                  </TableCell>
                  <TableCell>
                    <div className="flex items-center justify-end gap-1">
                      <ContentFormDialog
                        initial={{
                          id: content.id,
                          judul: content.judul,
                          penulis: content.penulis,
                          isi: content.isi,
                          gambar: content.gambar,
                          video: content.video,
                        }}
                        trigger={
                          <Button variant="ghost" size="icon" className="size-8" aria-label={`Ubah ${content.judul}`}>
                            <Pencil className="size-4" />
                          </Button>
                        }
                      />
                      <ConfirmDialog
                        title={`Hapus "${content.judul}"?`}
                        description="Konten akan hilang dari situs publik. Tindakan ini tidak dapat dibatalkan."
                        confirmLabel="Hapus"
                        action={deleteContentAction}
                        fields={{ id: content.id }}
                        trigger={
                          <Button
                            variant="ghost"
                            size="icon"
                            className="size-8 text-destructive hover:bg-destructive/10 hover:text-destructive"
                            aria-label={`Hapus ${content.judul}`}
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
        buildHref={(n) => makePageHref("/admin/konten", params, n)}
      />
    </>
  );
}