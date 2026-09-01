import Link from "next/link";
import { Clock, Mail, MapPin, Phone } from "lucide-react";
import { SITE } from "@/lib/site";

export default function Footer() {
  const tahun = new Date().getFullYear();

  return (
    <footer className="mt-16 border-t border-border bg-chili-900 text-chili-50">
      <div className="mx-auto grid w-full max-w-6xl gap-10 px-4 py-12 sm:grid-cols-2 lg:grid-cols-4">
        {/* Brand */}
        <div>
          <div className="flex items-center gap-2">
            <span className="flex size-9 items-center justify-center rounded-xl bg-chili-600 text-lg">
              {SITE.emoji}
            </span>
            <span className="font-display text-lg font-bold text-white">{SITE.name}</span>
          </div>
          <p className="mt-3 font-display text-sm font-semibold text-mango-400">
            {SITE.tagline}
          </p>
          <p className="mt-2 text-sm leading-relaxed text-chili-100/80">
            Temukan produk pilihan dengan kualitas terbaik. Pesan online, bayar mudah,
            dikirim cepat ke seluruh Indonesia.
          </p>
        </div>

        {/* Kontak */}
        <div>
          <h3 className="font-display text-sm font-bold uppercase tracking-wider text-white">
            Kontak
          </h3>
          <ul className="mt-4 space-y-3 text-sm text-chili-100/80">
            <li className="flex items-start gap-2.5">
              <MapPin className="mt-0.5 size-4 shrink-0 text-mango-400" />
              <span>{SITE.contact.address}</span>
            </li>
            <li className="flex items-start gap-2.5">
              <Phone className="mt-0.5 size-4 shrink-0 text-mango-400" />
              <span>{SITE.contact.phone}</span>
            </li>
            <li className="flex items-start gap-2.5">
              <Mail className="mt-0.5 size-4 shrink-0 text-mango-400" />
              <span>{SITE.contact.email}</span>
            </li>
          </ul>
        </div>

        {/* Jam operasional */}
        <div>
          <h3 className="font-display text-sm font-bold uppercase tracking-wider text-white">
            Jam Operasional
          </h3>
          <ul className="mt-4 space-y-3 text-sm text-chili-100/80">
            <li className="flex items-start gap-2.5">
              <Clock className="mt-0.5 size-4 shrink-0 text-mango-400" />
              <span>
                {SITE.hours.map((line) => (
                  <span key={line} className="block">
                    {line}
                  </span>
                ))}
              </span>
            </li>
            <li className="text-chili-100/70">{SITE.hoursNote}</li>
          </ul>
        </div>

        {/* Tautan */}
        <div>
          <h3 className="font-display text-sm font-bold uppercase tracking-wider text-white">
            Jelajahi
          </h3>
          <ul className="mt-4 space-y-2.5 text-sm">
            {[
              { href: "/produk", label: "Semua Produk" },
              { href: "/konten", label: "Konten & Cerita" },
              { href: "/pesanan", label: "Pesanan Saya" },
              { href: "/login", label: "Masuk" },
              { href: "/register", label: "Daftar Akun" },
            ].map((link) => (
              <li key={link.href + link.label}>
                <Link
                  href={link.href}
                  className="text-chili-100/80 transition-colors hover:text-mango-400"
                >
                  {link.label}
                </Link>
              </li>
            ))}
          </ul>
        </div>
      </div>

      <div className="border-t border-white/10">
        <div className="mx-auto flex w-full max-w-6xl flex-col items-center justify-between gap-2 px-4 py-5 text-xs text-chili-100/60 sm:flex-row">
          <p>
            © {tahun} {SITE.name}. {SITE.copyright}
          </p>
          <p>
            Dibangun dengan LapakJS — template toko online open-source.
          </p>
        </div>
      </div>
    </footer>
  );
}
