import Image from "next/image";
import Link from "next/link";
import type { Metadata } from "next";
import { ArrowRight, Flame, Leaf, MapPin, ShoppingBag, Sun } from "lucide-react";
import { prisma } from "@/lib/db";
import { formatRupiah, formatTanggal, imageUrl } from "@/lib/format";
import ProductCard from "@/components/site/ProductCard";
import RatingStars from "@/components/site/RatingStars";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";

export const metadata: Metadata = {
  title: "Beranda",
  description:
    "Sambal rumahan asli buatan Mama Ana — pedasnya bikin nagih. Tanpa pengawet, segar setiap hari, dikirim ke seluruh Indonesia.",
};

const TICKER_WORDS = [
  "PEDAS NAMPOL",
  "TANPA PENGAWET",
  "SEGAR SETIAP HARI",
  "BUATAN RUMAHAN",
  "CABAI PILIHAN",
  "RESEP WARISAN",
  "KIRIM SE-INDONESIA",
];

export default async function HomePage() {
  const [products, contents, testimonials] = await Promise.all([
    prisma.product.findMany({
      orderBy: { created_at: "asc" },
      include: { _count: { select: { orderItems: true } } },
    }),
    prisma.content.findMany({ orderBy: { tanggal: "desc" }, take: 3 }),
    prisma.testimonial.findMany({
      orderBy: { tanggal: "desc" },
      take: 3,
      include: { user: { select: { nama: true } } },
    }),
  ]);

  const unggulan = [...products]
    .sort(
      (a, b) =>
        b._count.orderItems - a._count.orderItems || Number(b.harga) - Number(a.harga)
    )
    .slice(0, 4);
  const heroImages = products.filter((p) => p.foto).slice(0, 3);

  return (
    <div>
      {/* ===== Hero ===== */}
      <section className="relative overflow-hidden">
        <div
          aria-hidden
          className="pointer-events-none absolute -right-24 -top-24 size-96 rounded-full bg-chili-100 blur-3xl"
        />
        <div className="mx-auto grid w-full max-w-6xl items-center gap-10 px-4 pb-14 pt-12 lg:grid-cols-2 lg:gap-14 lg:pt-16">
          <div className="relative">
            <span className="inline-flex items-center gap-1.5 rounded-full border border-chili-200 bg-chili-50 px-3 py-1 text-xs font-semibold text-chili-700">
              <Flame className="size-3.5" />
              Sambal rumahan asli, dibuat segar setiap hari
            </span>
            <h1 className="mt-4 font-display text-4xl font-extrabold leading-[1.05] tracking-tight text-ink sm:text-5xl lg:text-6xl">
              Pedasnya
              <br />
              <span className="text-chili-600">Bikin Nagih</span> 🌶️
            </h1>
            <p className="mt-5 max-w-md text-base leading-relaxed text-muted-foreground sm:text-lg">
              Sambal bawang, terasi, ijo, dan cumi dari dapur Mama Ana. Cabai pilihan, tanpa
              pengawet, digoreng segar setiap pagi — sekali coba, pasti nambah.
            </p>
            <div className="mt-7 flex flex-wrap items-center gap-3">
              <Button asChild size="lg" className="h-12 bg-chili-600 px-7 text-base hover:bg-chili-700">
                <Link href="/produk">
                  <ShoppingBag className="size-5" />
                  Belanja Sekarang
                </Link>
              </Button>
              <Button
                asChild
                variant="outline"
                size="lg"
                className="h-12 border-chili-200 px-7 text-base hover:bg-chili-50 hover:text-chili-700"
              >
                <Link href="/produk#unggulan">Lihat Produk</Link>
              </Button>
            </div>
            <div className="mt-8 flex items-center gap-6 text-sm text-muted-foreground">
              <span>
                <strong className="font-mono text-lg font-bold text-ink tabular-nums">
                  {products.length}
                </strong>{" "}
                varian sambal
              </span>
              <Separator orientation="vertical" className="h-8" />
              <span>
                <strong className="font-mono text-lg font-bold text-ink tabular-nums">100%</strong>{" "}
                bahan segar
              </span>
              <Separator orientation="vertical" className="hidden h-8 sm:block" />
              <span className="hidden items-center gap-1.5 sm:flex">
                <MapPin className="size-4 text-chili-600" />
                Kirim se-Indonesia
              </span>
            </div>
          </div>
          {/* Visual dari gambar produk asli DB */}
          <div className="relative mx-auto w-full max-w-md lg:max-w-none">
            <div className="grid grid-cols-2 gap-4">
              {heroImages[0] && (
                <div className="relative col-span-2 aspect-[4/3] overflow-hidden rounded-3xl border border-border shadow-lg">
                  <Image
                    src={imageUrl(heroImages[0].foto)}
                    alt={heroImages[0].nama}
                    fill
                    priority
                    sizes="(max-width: 1024px) 100vw, 50vw"
                    className="object-cover"
                  />
                  <span className="absolute bottom-3 left-3 rounded-full bg-ink/70 px-3 py-1 text-xs font-semibold text-white backdrop-blur">
                    {heroImages[0].nama} — {formatRupiah(heroImages[0].harga)}
                  </span>
                </div>
              )}
              {heroImages.slice(1, 3).map((p, i) => (
                <div
                  key={p.id}
                  className={`relative aspect-square overflow-hidden rounded-2xl border border-border shadow-md ${
                    i === 0 ? "translate-y-3" : "-translate-y-3"
                  }`}
                >
                  <Image
                    src={imageUrl(p.foto)}
                    alt={p.nama}
                    fill
                    sizes="(max-width: 1024px) 45vw, 22vw"
                    className="object-cover"
                  />
                </div>
              ))}
            </div>
          </div>
        </div>
        {/* ===== Ticker marquee ===== */}
        <div className="overflow-hidden border-y-2 border-ink bg-chili-600 py-2.5 text-primary-foreground">
          <div className="animate-marquee flex w-max">
            {[0, 1].map((dup) => (
              <div key={dup} aria-hidden={dup === 1} className="flex shrink-0 items-center">
                {TICKER_WORDS.map((word) => (
                  <span
                    key={`${dup}-${word}`}
                    className="mx-4 flex items-center gap-8 font-display text-sm font-bold tracking-widest"
                  >
                    {word} <span className="text-mango-400">•</span>
                  </span>
                ))}
              </div>
            ))}
          </div>
        </div>
      </section>
      {/* ===== Keunggulan ===== */}
      <section className="mx-auto w-full max-w-6xl px-4 py-14">
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          {[
            {
              icon: Flame,
              judul: "Pedas Nampol",
              isi: "Cabai rawit merah pilihan, level pedasnya jujur.",
            },
            {
              icon: Leaf,
              judul: "Tanpa Pengawet",
              isi: "Bahan dapur asli, tanpa pewarna dan pengawet.",
            },
            {
              icon: Sun,
              judul: "Segar Setiap Hari",
              isi: "Digoreng setiap pagi, dikirim di hari yang sama.",
            },
            {
              icon: ShoppingBag,
              judul: "Buatan Rumahan",
              isi: "Resep warisan keluarga dari dapur Mama Ana.",
            },
          ].map((item) => {
            const Icon = item.icon;
            return (
              <div
                key={item.judul}
                className="flex items-start gap-3.5 rounded-2xl border border-border bg-card p-5 shadow-sm"
              >
                <span className="flex size-11 shrink-0 items-center justify-center rounded-xl bg-chili-50 text-chili-600">
                  <Icon className="size-5" />
                </span>
                <div>
                  <h3 className="font-display text-sm font-bold text-ink">{item.judul}</h3>
                  <p className="mt-1 text-sm leading-relaxed text-muted-foreground">{item.isi}</p>
                </div>
              </div>
            );
          })}
        </div>
      </section>

      {/* ===== Produk unggulan ===== */}
      <section id="unggulan" className="bg-muted/50 py-14">
        <div className="mx-auto w-full max-w-6xl px-4">
          <div className="flex flex-wrap items-end justify-between gap-4">
            <div>
              <p className="text-sm font-semibold uppercase tracking-wider text-chili-600">
                Paling laris
              </p>
              <h2 className="mt-1 font-display text-3xl font-extrabold tracking-tight text-ink">
                Produk Unggulan
              </h2>
            </div>
            <Button asChild variant="ghost" className="text-chili-700 hover:bg-chili-50 hover:text-chili-800">
              <Link href="/produk">
                Lihat Semua Produk
                <ArrowRight className="size-4" />
              </Link>
            </Button>
          </div>
          {unggulan.length > 0 ? (
            <div className="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
              {unggulan.map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>
          ) : (
            <p className="mt-8 rounded-2xl border border-dashed border-border bg-card p-10 text-center text-muted-foreground">
              Katalog sedang disiapkan. Cek lagi sebentar lagi ya! 🌶️
            </p>
          )}
        </div>
      </section>
      {/* ===== Testimoni ===== */}
      {testimonials.length > 0 && (
        <section className="mx-auto w-full max-w-6xl px-4 py-14">
          <p className="text-sm font-semibold uppercase tracking-wider text-chili-600">
            Kata mereka
          </p>
          <h2 className="mt-1 font-display text-3xl font-extrabold tracking-tight text-ink">
            Testimoni Pelanggan
          </h2>
          <div className="mt-8 grid gap-5 md:grid-cols-3">
            {testimonials.map((t) => (
              <figure
                key={t.id}
                className="flex flex-col rounded-2xl border border-border bg-card p-6 shadow-sm"
              >
                <RatingStars rating={t.rating} />
                <blockquote className="mt-3 flex-1 text-sm leading-relaxed text-foreground/90">
                  “{t.isi}”
                </blockquote>
                <figcaption className="mt-4 text-sm font-semibold text-ink">
                  {t.user.nama}
                </figcaption>
              </figure>
            ))}
          </div>
        </section>
      )}

      {/* ===== Konten terbaru ===== */}
      {contents.length > 0 && (
        <section className="bg-muted/50 py-14">
          <div className="mx-auto w-full max-w-6xl px-4">
            <div className="flex flex-wrap items-end justify-between gap-4">
              <div>
                <p className="text-sm font-semibold uppercase tracking-wider text-chili-600">
                  Dari dapur kami
                </p>
                <h2 className="mt-1 font-display text-3xl font-extrabold tracking-tight text-ink">
                  Konten Terbaru
                </h2>
              </div>
              <Button asChild variant="ghost" className="text-chili-700 hover:bg-chili-50 hover:text-chili-800">
                <Link href="/konten">
                  Semua Konten
                  <ArrowRight className="size-4" />
                </Link>
              </Button>
            </div>
            <div className="mt-8 grid gap-5 md:grid-cols-3">
              {contents.map((c) => (
                <Link
                  key={c.id}
                  href={`/konten/${c.id}`}
                  className="group flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-lg"
                >
                  <div className="relative aspect-[16/9] overflow-hidden bg-muted">
                    <Image
                      src={imageUrl(c.gambar)}
                      alt={c.judul}
                      fill
                      sizes="(max-width: 768px) 100vw, 33vw"
                      className="object-cover transition-transform duration-300 group-hover:scale-105"
                    />
                  </div>
                  <div className="flex flex-1 flex-col p-5">
                    <h3 className="font-display text-base font-bold leading-snug text-ink group-hover:text-chili-700">
                      {c.judul}
                    </h3>
                    <p className="mt-2 line-clamp-2 text-sm text-muted-foreground">{c.isi}</p>
                    <p className="mt-auto pt-4 text-xs text-muted-foreground">
                      {c.penulis} · {formatTanggal(c.tanggal)}
                    </p>
                  </div>
                </Link>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* ===== CTA akhir ===== */}
      <section className="mx-auto w-full max-w-6xl px-4 pt-14">
        <div className="relative overflow-hidden rounded-3xl bg-chili-700 px-6 py-12 text-center sm:px-12">
          <div
            aria-hidden
            className="pointer-events-none absolute -left-16 -top-16 size-64 rounded-full bg-ember-500/30 blur-3xl"
          />
          <div
            aria-hidden
            className="pointer-events-none absolute -bottom-20 -right-10 size-72 rounded-full bg-mango-400/20 blur-3xl"
          />
          <h2 className="relative font-display text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
            Siap mencicipi pedasnya? 🌶️
          </h2>
          <p className="relative mx-auto mt-3 max-w-xl text-chili-100">
            Pesan sekarang sebelum jam 14.00 dan sambalmu dikirim hari itu juga. Awas, bisa bikin
            nambah nasi!
          </p>
          <div className="relative mt-7 flex justify-center">
            <Button asChild size="lg" className="h-12 bg-mango-500 px-8 text-base text-ink hover:bg-mango-400">
              <Link href="/produk">
                Belanja Sekarang
                <ArrowRight className="size-5" />
              </Link>
            </Button>
          </div>
        </div>
      </section>
    </div>
  );
}
