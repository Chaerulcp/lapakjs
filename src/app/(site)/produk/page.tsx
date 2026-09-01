import type { Metadata } from "next";
import Link from "next/link";
import { Search, SearchX } from "lucide-react";
import { prisma } from "@/lib/db";
import { SITE } from "@/lib/site";
import ProductCard, { kategoriLabel } from "@/components/site/ProductCard";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { cn } from "@/lib/utils";

export const metadata: Metadata = {
  title: "Produk",
  description: `Katalog lengkap produk ${SITE.name}. Temukan produk favoritmu dengan kualitas terbaik dan harga bersahabat.`,
};

type ProdukSearchParams = {
  q?: string;
  kategori?: string;
};

export default async function ProdukPage({
  searchParams,
}: {
  searchParams: Promise<ProdukSearchParams>;
}) {
  const { q, kategori } = await searchParams;
  const cari = q?.trim() ?? "";

  const [kategoris] = await Promise.all([
    prisma.product.groupBy({
      by: ["kategori"],
      _count: { _all: true },
      orderBy: { kategori: "asc" },
    }),
  ]);

  const products = await prisma.product.findMany({
    where: {
      ...(cari ? { nama: { contains: cari } } : {}),
      ...(kategori ? { kategori } : {}),
    },
    orderBy: { created_at: "asc" },
  });

  function buildHref(next: Partial<ProdukSearchParams>): string {
    const params = new URLSearchParams();
    const nextQ = next.q !== undefined ? next.q : cari;
    const nextKategori = next.kategori !== undefined ? next.kategori : kategori;
    if (nextQ) params.set("q", nextQ);
    if (nextKategori) params.set("kategori", nextKategori);
    const qs = params.toString();
    return qs ? `/produk?${qs}` : "/produk";
  }

  return (
    <div className="mx-auto w-full max-w-6xl px-4 py-10">
      {/* Header */}
      <div className="max-w-2xl">
        <h1 className="font-display text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">
          Semua Produk
        </h1>
        <p className="mt-2 text-muted-foreground">
          Pilih produk favoritmu — kualitas terjaga, harga bersahabat.
        </p>
      </div>

      {/* Pencarian + filter kategori */}
      <div className="mt-8 space-y-4">
        <form action="/produk" method="GET" className="flex max-w-md gap-2">
          {kategori && <input type="hidden" name="kategori" value={kategori} />}
          <Input
            type="search"
            name="q"
            defaultValue={cari}
            placeholder="Cari produk… (mis. sambal bawang, paket hemat)"
            className="h-10"
          />
          <Button type="submit" className="h-10 bg-chili-600 px-4 hover:bg-chili-700">
            <Search className="size-4" />
            Cari
          </Button>
        </form>

        <div className="flex flex-wrap items-center gap-2">
          <a
            href={buildHref({ kategori: undefined })}
            className={cn(
              "rounded-full border px-3.5 py-1.5 text-sm font-medium transition-colors",
              !kategori
                ? "border-chili-600 bg-chili-600 text-white"
                : "border-border bg-card text-foreground/80 hover:border-chili-300 hover:text-chili-700"
            )}
          >
            Semua ({kategoris.reduce((s, k) => s + k._count._all, 0)})
          </a>
          {kategoris.map((k) => (
            <a
              key={k.kategori}
              href={buildHref({ kategori: k.kategori })}
              className={cn(
                "rounded-full border px-3.5 py-1.5 text-sm font-medium transition-colors",
                kategori === k.kategori
                  ? "border-chili-600 bg-chili-600 text-white"
                  : "border-border bg-card text-foreground/80 hover:border-chili-300 hover:text-chili-700"
              )}
            >
              {kategoriLabel(k.kategori)} ({k._count._all})
            </a>
          ))}
        </div>
      </div>

      {/* Hasil */}
      {cari && (
        <p className="mt-6 text-sm text-muted-foreground">
          {products.length} hasil untuk <strong className="text-ink">“{cari}”</strong>
          {kategori ? ` di kategori ${kategoriLabel(kategori)}` : ""}
        </p>
      )}

      {products.length > 0 ? (
        <div className="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          {products.map((product) => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
      ) : (
        <div className="mt-10 rounded-2xl border border-dashed border-border bg-card p-12 text-center">
          <SearchX className="mx-auto size-10 text-muted-foreground" aria-hidden />
          <h2 className="mt-3 font-display text-lg font-bold text-ink">
            Tidak ada produk yang cocok
          </h2>
          <p className="mt-1 text-sm text-muted-foreground">
            Coba kata kunci lain atau lihat semua produk kami.
          </p>
          <Button asChild variant="outline" className="mt-5">
            <Link href="/produk">Lihat Semua Produk</Link>
          </Button>
        </div>
      )}
    </div>
  );
}
