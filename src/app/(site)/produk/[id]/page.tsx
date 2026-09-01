import Image from "next/image";
import Link from "next/link";
import { notFound } from "next/navigation";
import type { Metadata } from "next";
import { ChevronRight, ShieldCheck, Truck } from "lucide-react";
import { auth } from "@/auth";
import { prisma } from "@/lib/db";
import { formatRupiah, formatTanggal, imageUrl } from "@/lib/format";
import AddToCartForm from "@/components/site/AddToCartForm";
import ProductCard, { kategoriLabel } from "@/components/site/ProductCard";
import RatingStars from "@/components/site/RatingStars";
import { Badge } from "@/components/ui/badge";
import { Separator } from "@/components/ui/separator";

type ProdukDetailParams = { id: string };

async function getProduk(id: number) {
  if (!Number.isInteger(id) || id <= 0) return null;
  return prisma.product.findUnique({ where: { id } });
}

export async function generateMetadata({
  params,
}: {
  params: Promise<ProdukDetailParams>;
}): Promise<Metadata> {
  const { id } = await params;
  const product = await getProduk(Number(id));
  if (!product) return { title: "Produk tidak ditemukan" };
  return { title: product.nama, description: product.deskripsi.slice(0, 155) };
}

export default async function ProdukDetailPage({
  params,
}: {
  params: Promise<ProdukDetailParams>;
}) {
  const { id } = await params;
  const product = await getProduk(Number(id));
  if (!product) notFound();

  const [session, testimonials, related] = await Promise.all([
    auth(),
    prisma.testimonial.findMany({
      where: { product_id: product.id },
      orderBy: { tanggal: "desc" },
      include: { user: { select: { nama: true } } },
    }),
    prisma.product.findMany({
      where: { kategori: product.kategori, id: { not: product.id } },
      take: 4,
    }),
  ]);

  const harga = Number(product.harga);
  const hargaReseller = Number(product.harga_reseller);
  const isReseller = session?.user?.role === "reseller";
  const rataRating =
    testimonials.length > 0
      ? testimonials.reduce((s, t) => s + t.rating, 0) / testimonials.length
      : null;

  return (
    <div className="mx-auto w-full max-w-6xl px-4 py-8">
      {/* Breadcrumb */}
      <nav className="flex items-center gap-1.5 text-sm text-muted-foreground">
        <Link href="/" className="hover:text-chili-700">
          Beranda
        </Link>
        <ChevronRight className="size-3.5" />
        <Link href="/produk" className="hover:text-chili-700">
          Produk
        </Link>
        <ChevronRight className="size-3.5" />
        <span className="truncate text-foreground">{product.nama}</span>
      </nav>

      <div className="mt-6 grid gap-10 lg:grid-cols-2">
        <div className="relative aspect-square overflow-hidden rounded-3xl border border-border bg-muted shadow-sm">
          <Image
            src={imageUrl(product.foto)}
            alt={product.nama}
            fill
            priority
            sizes="(max-width: 1024px) 100vw, 50vw"
            className="object-cover"
          />
        </div>

        {/* Info */}
        <div>
          <Badge variant="secondary" className="bg-chili-50 text-chili-700">
            {kategoriLabel(product.kategori)}
          </Badge>
          <h1 className="mt-3 font-display text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">
            {product.nama}
          </h1>

          {rataRating !== null && (
            <div className="mt-3 flex items-center gap-2 text-sm text-muted-foreground">
              <RatingStars rating={rataRating} />
              <span>
                {rataRating.toFixed(1)} ({testimonials.length} ulasan)
              </span>
            </div>
          )}

          <div className="mt-5 flex items-end gap-3">
            <span className="font-mono text-3xl font-bold text-chili-700 tabular-nums">
              {formatRupiah(isReseller ? hargaReseller : harga)}
            </span>
            {isReseller && (
              <span className="mb-1 text-sm text-muted-foreground line-through">
                {formatRupiah(harga)}
              </span>
            )}
          </div>
          {isReseller ? (
            <p className="mt-1 text-sm font-medium text-leaf-600">
              Harga khusus reseller. Terima kasih sudah jadi bagian keluarga Mama Ana! 💛
            </p>
          ) : (
            <p className="mt-1 text-sm text-muted-foreground">per botol ± 250 ml</p>
          )}

          <Separator className="my-5" />
          <p className="whitespace-pre-line text-sm leading-relaxed text-foreground/90">
            {product.deskripsi}
          </p>

          <div className="mt-7">
            <AddToCartForm productId={product.id} stok={product.stok} />
          </div>

          <div className="mt-7 grid gap-3 text-sm text-muted-foreground sm:grid-cols-2">
            <p className="flex items-center gap-2.5">
              <Truck className="size-4 shrink-0 text-chili-600" />
              Dikirim dari Yogyakarta ke seluruh Indonesia
            </p>
            <p className="flex items-center gap-2.5">
              <ShieldCheck className="size-4 shrink-0 text-chili-600" />
              Tanpa pengawet, tahan 2 minggu di suhu ruang
            </p>
          </div>
        </div>
      </div>
      {/* Testimoni */}
      <section className="mt-16">
        <h2 className="font-display text-2xl font-extrabold tracking-tight text-ink">
          Ulasan Pembeli
        </h2>
        {testimonials.length > 0 ? (
          <div className="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            {testimonials.map((t) => (
              <figure key={t.id} className="rounded-2xl border border-border bg-card p-5 shadow-sm">
                <RatingStars rating={t.rating} size="size-3.5" />
                <blockquote className="mt-3 text-sm leading-relaxed text-foreground/90">
                  “{t.isi}”
                </blockquote>
                <figcaption className="mt-3 text-xs font-semibold text-ink">
                  {t.user.nama} · {formatTanggal(t.tanggal)}
                </figcaption>
              </figure>
            ))}
          </div>
        ) : (
          <p className="mt-5 rounded-2xl border border-dashed border-border bg-card p-8 text-center text-sm text-muted-foreground">
            Belum ada ulasan. Jadilah yang pertama mencicipi! 🌶️
          </p>
        )}
      </section>

      {/* Produk terkait */}
      {related.length > 0 && (
        <section className="mt-16">
          <h2 className="font-display text-2xl font-extrabold tracking-tight text-ink">
            Serupa di Kategori Ini
          </h2>
          <div className="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            {related.map((p) => (
              <ProductCard key={p.id} product={p} />
            ))}
          </div>
        </section>
      )}
    </div>
  );
}
