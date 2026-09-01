import Link from "next/link";
import { Button } from "@/components/ui/button";

export default function NotFound() {
  return (
    <main className="flex min-h-screen flex-col items-center justify-center gap-5 px-6 py-16 text-center">
      <span className="text-6xl" aria-hidden>
        🌶️
      </span>
      <p className="font-heading text-xs font-semibold uppercase tracking-[0.2em] text-primary">
        Error 404
      </p>
      <h1 className="font-heading text-4xl font-bold tracking-tight sm:text-5xl">
        Halaman tidak ditemukan
      </h1>
      <p className="max-w-md text-muted-foreground">
        Waduh, halaman yang kamu cari sepertinya sudah pindah atau tidak ada.
        Yuk kembali ke beranda atau lihat-lihat sambal kami dulu.
      </p>
      <div className="mt-2 flex flex-wrap items-center justify-center gap-3">
        <Button asChild size="lg">
          <Link href="/">Kembali ke Beranda</Link>
        </Button>
        <Button asChild size="lg" variant="outline">
          <Link href="/produk">Lihat Produk</Link>
        </Button>
      </div>
    </main>
  );
}