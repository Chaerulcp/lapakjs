"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useState } from "react";
import { LayoutDashboard, LogOut, Menu, Package, ShoppingCart } from "lucide-react";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from "@/components/ui/sheet";
import { logoutAction } from "@/app/(site)/actions";
import { SITE } from "@/lib/site";
import { cn } from "@/lib/utils";

export type NavbarUser = {
  nama: string;
  email: string;
  role: "admin" | "reseller" | "pelanggan";
};

type NavbarProps = {
  user: NavbarUser | null;
  cartCount: number;
};

const NAV_LINKS = [
  { href: "/", label: "Beranda" },
  { href: "/produk", label: "Produk" },
  { href: "/konten", label: "Konten" },
];

function isActive(pathname: string, href: string): boolean {
  if (href === "/") return pathname === "/";
  return pathname === href || pathname.startsWith(href + "/");
}

function CartLink({ cartCount }: { cartCount: number }) {
  const pathname = usePathname();
  return (
    <Link
      href="/keranjang"
      aria-label={`Keranjang, ${cartCount} item`}
      className={cn(
        "relative inline-flex size-9 items-center justify-center rounded-lg text-foreground transition-colors hover:bg-muted",
        isActive(pathname, "/keranjang") && "bg-muted text-chili-600"
      )}
    >
      <ShoppingCart className="size-5" />
      {cartCount > 0 && (
        <span className="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-ember-500 px-1 font-mono text-[11px] font-bold text-white tabular-nums">
          {cartCount > 99 ? "99+" : cartCount}
        </span>
      )}
    </Link>
  );
}

export default function Navbar({ user, cartCount }: NavbarProps) {
  const pathname = usePathname();
  const [mobileOpen, setMobileOpen] = useState(false);

  const links = user ? [...NAV_LINKS, { href: "/pesanan", label: "Pesanan" }] : NAV_LINKS;
  const initial = (user?.nama ?? "?").trim().charAt(0).toUpperCase() || "?";

  return (
    <header className="sticky top-0 z-50 border-b border-border bg-paper/90 backdrop-blur supports-[backdrop-filter]:bg-paper/75">
      <div className="mx-auto flex h-16 w-full max-w-6xl items-center justify-between gap-3 px-4">
        <Link href="/" className="flex items-center gap-2" onClick={() => setMobileOpen(false)}>
          <span className="flex size-9 items-center justify-center rounded-xl bg-chili-600 text-lg shadow-sm">
            {SITE.emoji}
          </span>
          <span className="leading-tight">
            <span className="block font-display text-base font-bold tracking-tight text-ink">
              {SITE.name}
            </span>
            <span className="hidden text-[11px] font-medium text-muted-foreground sm:block">
              {SITE.tagline}
            </span>
          </span>
        </Link>

        {/* Nav desktop */}
        <nav className="hidden items-center gap-1 md:flex">
          {links.map((link) => (
            <Link
              key={link.href}
              href={link.href}
              className={cn(
                "rounded-lg px-3 py-2 text-sm font-medium transition-colors hover:bg-muted hover:text-chili-700",
                isActive(pathname, link.href) ? "bg-chili-50 text-chili-700" : "text-foreground/80"
              )}
            >
              {link.label}
            </Link>
          ))}
        </nav>

        <div className="flex items-center gap-2">
          <CartLink cartCount={cartCount} />
          {user ? (
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <button
                  type="button"
                  aria-label="Menu akun"
                  className="flex items-center gap-2 rounded-full outline-none transition-shadow hover:ring-2 hover:ring-ring/40 focus-visible:ring-2 focus-visible:ring-ring"
                >
                  <Avatar className="size-9">
                    <AvatarFallback className="bg-chili-600 font-display text-sm font-bold text-white">
                      {initial}
                    </AvatarFallback>
                  </Avatar>
                </button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" className="w-56">
                <DropdownMenuLabel className="font-normal">
                  <span className="block truncate text-sm font-semibold">{user.nama}</span>
                  <span className="block truncate text-xs text-muted-foreground">{user.email}</span>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                  <Link href="/pesanan">
                    <Package className="size-4" />
                    Pesanan Saya
                  </Link>
                </DropdownMenuItem>
                {user.role === "admin" && (
                  <DropdownMenuItem asChild>
                    <Link href="/admin">
                      <LayoutDashboard className="size-4" />
                      Admin Panel
                    </Link>
                  </DropdownMenuItem>
                )}
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild className="text-destructive focus:text-destructive">
                  <button type="submit" form="navbar-logout-form" className="w-full cursor-pointer">
                    <LogOut className="size-4" />
                    Keluar
                  </button>
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          ) : (
            <div className="hidden items-center gap-2 md:flex">
              <Button asChild variant="outline" size="sm" className="h-9 px-3">
                <Link href="/login">Masuk</Link>
              </Button>
              <Button asChild size="sm" className="h-9 bg-chili-600 px-3 hover:bg-chili-700">
                <Link href="/register">Daftar</Link>
              </Button>
            </div>
          )}
          {/* Nav mobile */}
          <Sheet open={mobileOpen} onOpenChange={setMobileOpen}>
            <SheetTrigger asChild>
              <Button variant="ghost" size="icon" className="size-9 md:hidden" aria-label="Buka menu">
                <Menu className="size-5" />
              </Button>
            </SheetTrigger>
            <SheetContent side="right" className="flex w-72 flex-col bg-paper">
              <SheetHeader>
                <SheetTitle className="font-display text-left">
                  {SITE.emoji} {SITE.name}
                </SheetTitle>
              </SheetHeader>
              <nav className="flex flex-col gap-1 px-4">
                {links.map((link) => (
                  <Link
                    key={link.href}
                    href={link.href}
                    onClick={() => setMobileOpen(false)}
                    className={cn(
                      "rounded-lg px-3 py-2.5 text-sm font-medium transition-colors hover:bg-muted",
                      isActive(pathname, link.href) ? "bg-chili-50 text-chili-700" : "text-foreground/80"
                    )}
                  >
                    {link.label}
                  </Link>
                ))}
              </nav>
              <div className="mt-auto border-t border-border px-4 pt-4">
                {user ? (
                  <div className="space-y-3 pb-4">
                    <p className="text-sm font-semibold">{user.nama}</p>
                    {user.role === "admin" && (
                      <Button asChild variant="outline" className="w-full justify-start">
                        <Link href="/admin" onClick={() => setMobileOpen(false)}>
                          <LayoutDashboard className="size-4" />
                          Admin Panel
                        </Link>
                      </Button>
                    )}
                    <form action={logoutAction}>
                      <Button type="submit" variant="destructive" className="w-full justify-start">
                        <LogOut className="size-4" />
                        Keluar
                      </Button>
                    </form>
                  </div>
                ) : (
                  <div className="grid grid-cols-2 gap-2 pb-4">
                    <Button asChild variant="outline">
                      <Link href="/login" onClick={() => setMobileOpen(false)}>
                        Masuk
                      </Link>
                    </Button>
                    <Button asChild className="bg-chili-600 hover:bg-chili-700">
                      <Link href="/register" onClick={() => setMobileOpen(false)}>
                        Daftar
                      </Link>
                    </Button>
                  </div>
                )}
              </div>
            </SheetContent>
          </Sheet>
        </div>
      </div>

      <form action={logoutAction} id="navbar-logout-form" className="hidden" />
    </header>
  );
}
