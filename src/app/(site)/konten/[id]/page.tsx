import Image from "next/image";
import Link from "next/link";
import { notFound } from "next/navigation";
import type { Metadata } from "next";
import { ChevronRight, Clock } from "lucide-react";
import { prisma } from "@/lib/db";
import { formatTanggal, imageUrl } from "@/lib/format";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";

type KontenDetailParams = { id: string };

async function getKonten(id: number) {
  if (!Number.isInteger(id) || id <= 0) return null;
  return prisma.content.findUnique({ where: { id } });
}

export async function generateMetadata({
  params,
}: {
  params: Promise<KontenDetailParams>;
}): Promise<Metadata> {
  const { id } = await params;
  const content = await getKonten(Number(id));
  if (!content) return { title: "Konten tidak ditemukan" };
  return { title: content.judul, description: content.isi.slice(0, 155) };
}

export default async function KontenDetailPage({
  params,
}: {
  params: Promise<KontenDetailParams>;
}) {
  const { id } = await params;
  const content = await getKonten(Number(id));
  if (!content) notFound();

  const lainnya = await prisma.content.findMany({
    where: { id: { not: content.id } },
    orderBy: { tanggal: "desc" },
    take: 3,
  });

  const paragraf = content.isi
    .split(/\r?\n+/)
    .map((p) => p.trim())
    .filter(Boolean);

  return (
    <div className="mx-auto w-full max-w-3xl px-4 py-10">
      {/* Breadcrumb */}
      <nav className="flex items-center gap-1.5 text-sm text-muted-foreground">
        <Link href="/" className="hover:text-chili-700">
          Beranda
        </Link>
        <ChevronRight className="size-3.5" />
        <Link href="/konten" className="hover:text-chili-700">
          Konten
        </Link>
        <ChevronRight className="size-3.5" />
        <span className="truncate text-foreground">{content.judul}</span>
      </nav>

      <article className="mt-6">
        <header>
          <h1 className="font-display text-3xl font-extrabold leading-tight tracking-tight text-ink sm:text-4xl">
            {content.judul}
          </h1>
          <p className="mt-3 flex items-center gap-2 text-sm text-muted-foreground">
            <Clock className="size-4" />
            {formatTanggal(content.tanggal, true)} · oleh{" "}
            <span className="font-semibold text-ink">{content.penulis}</span>
          </p>
        </header>

        {content.gambar && (
          <div className="relative mt-6 aspect-[16/9] overflow-hidden rounded-2xl border border-border bg-muted">
            <Image
              src={imageUrl(content.gambar)}
              alt={content.judul}
              fill
              priority
              sizes="(max-width: 768px) 100vw, 768px"
              className="object-cover"
            />
          </div>
        )}

        <div className="mt-8 space-y-4 text-base leading-relaxed text-foreground/90">
          {paragraf.map((p, i) => (
            <p key={i}>{p}</p>
          ))}
        </div>

        {content.video && (
          <div className="mt-8">
            <h2 className="font-display text-lg font-bold text-ink">Video</h2>
            <div className="mt-3 overflow-hidden rounded-2xl border border-border bg-ink">
              <video
                controls
                className="aspect-video w-full"
                src={imageUrl(content.video, content.video)}
              />
            </div>
          </div>
        )}
      </article>

      <Separator className="my-10" />

      {lainnya.length > 0 && (
        <section>
          <h2 className="font-display text-xl font-extrabold tracking-tight text-ink">
            Baca Juga
          </h2>
          <div className="mt-5 space-y-3">
            {lainnya.map((c) => (
              <Link
                key={c.id}
                href={`/konten/${c.id}`}
                className="group flex items-center justify-between gap-4 rounded-xl border border-border bg-card p-4 transition-colors hover:border-chili-200 hover:bg-chili-50/50"
              >
                <div className="min-w-0">
                  <p className="truncate font-display text-sm font-bold text-ink group-hover:text-chili-700">
                    {c.judul}
                  </p>
                  <p className="mt-0.5 text-xs text-muted-foreground">
                    {formatTanggal(c.tanggal)} · {c.penulis}
                  </p>
                </div>
                <ChevronRight className="size-4 shrink-0 text-muted-foreground transition-transform group-hover:translate-x-0.5 group-hover:text-chili-600" />
              </Link>
            ))}
          </div>
        </section>
      )}

      <div className="mt-10 flex justify-center">
        <Button asChild variant="outline" className="border-chili-200 hover:bg-chili-50 hover:text-chili-700">
          <Link href="/konten">← Kembali ke Semua Konten</Link>
        </Button>
      </div>
    </div>
  );
}
