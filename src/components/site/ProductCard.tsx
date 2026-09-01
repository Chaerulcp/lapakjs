import Image from "next/image";
import Link from "next/link";
import type { Product } from "@prisma/client";
import { Badge } from "@/components/ui/badge";
import { imageUrl, formatRupiah } from "@/lib/format";

/** Label ramah untuk slug kategori dari DB lama. */
export function kategoriLabel(kategori: string): string {
  const map: Record<string, string> = {
    "sambal-bawang": "Sambal Bawang",
    "sambal-terasi": "Sambal Terasi",
    "sambal-ijo": "Sambal Ijo",
    "sambal-cumi": "Sambal Cumi",
    "sambal-goreng": "Sambal Goreng",
    paket: "Paket Hemat",
  };
  if (map[kategori]) return map[kategori]!;
  return kategori
    .split("-")
    .map((s) => s.charAt(0).toUpperCase() + s.slice(1))
    .join(" ");
}

/** Kartu produk untuk grid katalog & halaman terkait. */
export default function ProductCard({ product }: { product: Product }) {
  const harga = Number(product.harga);
  const habis = product.stok <= 0;

  return (
    <Link
      href={`/produk/${product.id}`}
      className="group flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all hover:-translate-y-0.5 hover:border-chili-200 hover:shadow-lg"
    >
      <div className="relative aspect-square w-full overflow-hidden bg-muted">
        <Image
          src={imageUrl(product.foto)}
          alt={product.nama}
          fill
          sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw"
          className="object-cover transition-transform duration-300 group-hover:scale-105"
        />
        <div className="absolute left-2.5 top-2.5 flex gap-1.5">
          <Badge variant="secondary" className="bg-white/90 text-chili-700 backdrop-blur">
            {kategoriLabel(product.kategori)}
          </Badge>
        </div>
        {habis && (
          <div className="absolute inset-0 flex items-center justify-center bg-ink/45">
            <Badge className="bg-ink/80 text-white">Stok Habis</Badge>
          </div>
        )}
      </div>
      <div className="flex flex-1 flex-col gap-1.5 p-4">
        <h3 className="font-display text-base font-bold leading-snug text-ink transition-colors group-hover:text-chili-700">
          {product.nama}
        </h3>
        <p className="line-clamp-2 text-sm text-muted-foreground">{product.deskripsi}</p>
        <div className="mt-auto flex items-end justify-between pt-3">
          <span className="font-mono text-lg font-bold text-chili-700 tabular-nums">
            {formatRupiah(harga)}
          </span>
          {!habis && (
            <span className="text-xs text-muted-foreground">Stok {product.stok}</span>
          )}
        </div>
      </div>
    </Link>
  );
}
