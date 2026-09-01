import Link from "next/link";
import { SITE } from "@/lib/site";

/**
 * Layout split-screen untuk semua halaman autentikasi:
 * kiri = panel brand (dari src/lib/site.ts), kanan = formulir.
 */
export default function AuthLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="grid min-h-screen lg:grid-cols-[1.1fr_1fr]">
      {/* Panel brand (desktop) */}
      <aside className="relative hidden overflow-hidden bg-chili-800 text-white lg:flex lg:flex-col lg:justify-between lg:p-12">
        <div
          aria-hidden
          className="absolute -right-24 -top-24 size-96 rounded-full bg-ember-500/25 blur-3xl"
        />
        <div
          aria-hidden
          className="absolute -bottom-32 -left-16 size-80 rounded-full bg-mango-500/15 blur-3xl"
        />

        <Link
          href="/"
          className="relative z-10 inline-flex items-center gap-2 font-display text-xl font-bold"
        >
          <span aria-hidden>{SITE.emoji}</span> {SITE.name}
        </Link>

        <div className="relative z-10 max-w-md">
          <p className="font-display text-4xl font-extrabold leading-tight xl:text-5xl">
            {SITE.tagline}.
          </p>
          <p className="mt-4 text-white/80">
            Belanja lebih nyaman dengan akun: pantau status pesananmu, kelola alamat
            pengiriman, dan nikmati harga khusus reseller.
          </p>
          <ul className="mt-8 space-y-3 text-sm text-white/90">
            <li>✅ Produk berkualitas & original</li>
            <li>✅ Dikirim ke seluruh Indonesia</li>
            <li>✅ Harga khusus untuk reseller</li>
          </ul>
        </div>

        <p className="relative z-10 text-sm italic text-white/60">
          “Pelayanannya cepat dan produknya berkualitas.” — pelanggan setia
        </p>
      </aside>

      {/* Panel formulir */}
      <main className="flex min-h-screen flex-col bg-paper">
        <header className="flex items-center justify-between p-6 lg:hidden">
          <Link
            href="/"
            className="inline-flex items-center gap-2 font-display text-lg font-bold text-chili-700"
          >
            <span aria-hidden>{SITE.emoji}</span> {SITE.name}
          </Link>
          <Link href="/" className="text-sm text-muted-foreground hover:text-foreground">
            ← Beranda
          </Link>
        </header>

        <div className="flex flex-1 items-center justify-center px-4 py-10 sm:px-6">
          <div className="w-full max-w-md">{children}</div>
        </div>

        <footer className="p-6 text-center text-xs text-muted-foreground">
          © {new Date().getFullYear()} {SITE.name} — {SITE.tagline}
        </footer>
      </main>
    </div>
  );
}
