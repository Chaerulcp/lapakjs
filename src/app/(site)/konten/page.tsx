import Image from "next/image";
import Link from "next/link";
import type { Metadata } from "next";
import { ArrowRight } from "lucide-react";
import { prisma } from "@/lib/db";
import { formatTanggal, imageUrl } from "@/lib/format";
import { Button } from "@/components/ui/button";

export const metadata: Metadata = {
  title: "Konten",
  description:
    "Cerita, resep, dan tips dari dapur Sambal Mama Ana — dari rahasia pedas sampai cara pesan.",
};

export default async function KontenPage() {
  const contents = await prisma.content.findMany({
    orderBy: { tanggal: "desc" },
  });

  return (
    <div className="mx-auto w-full max-w-6xl px-4 py-10">
      <div className="max-w-2xl">
        <h1 className="font-display text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">
          Konten & Cerita
        </h1>
        <p className="mt-2 text-muted-foreground">
          Rahasia dapur, cerita sambal, dan panduan belanja dari tim Mama Ana.
        </p>
      </div>

      {contents.length > 0 ? (
        <div className="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          {contents.map((c) => (
            <Link
              key={c.id}
              href={`/konten/${c.id}`}
              className="group flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all hover:-translate-y-0.5 hover:border-chili-200 hover:shadow-lg"
            >
              <div className="relative aspect-[16/9] overflow-hidden bg-muted">
                <Image
                  src={imageUrl(c.gambar)}
                  alt={c.judul}
                  fill
                  sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
                  className="object-cover transition-transform duration-300 group-hover:scale-105"
                />
              </div>
              <div className="flex flex-1 flex-col p-5">
                <p className="text-xs text-muted-foreground">
                  {formatTanggal(c.tanggal)} · {c.penulis}
                </p>
                <h2 className="mt-1.5 font-display text-base font-bold leading-snug text-ink group-hover:text-chili-700">
                  {c.judul}
                </h2>
                <p className="mt-2 line-clamp-3 text-sm text-muted-foreground">{c.isi}</p>
                <span className="mt-auto flex items-center gap-1 pt-4 text-sm font-semibold text-chili-700">
                  Baca selengkapnya
                  <ArrowRight className="size-4 transition-transform group-hover:translate-x-0.5" />
                </span>
              </div>
            </Link>
          ))}
        </div>
      ) : (
        <div className="mt-10 rounded-2xl border border-dashed border-border bg-card p-12 text-center">
          <p className="text-4xl">📖</p>
          <h2 className="mt-3 font-display text-lg font-bold text-ink">Belum ada konten</h2>
          <p className="mt-1 text-sm text-muted-foreground">
            Cerita dari dapur Mama Ana sedang dimasak. Tunggu sebentar lagi ya!
          </p>
          <Button asChild variant="outline" className="mt-5">
            <Link href="/produk">Lihat Produk Dulu</Link>
          </Button>
        </div>
      )}
    </div>
  );
}
