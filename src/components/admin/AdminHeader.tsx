"use client";

import Link from "next/link";
import { ExternalLink, LogOut, ShieldCheck } from "lucide-react";
import { adminLogoutAction } from "@/app/admin/actions";
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
import { Separator } from "@/components/ui/separator";
import { SidebarTrigger } from "@/components/ui/sidebar";

export type AdminHeaderUser = {
  nama: string;
  email: string;
};

/** Bilah atas panel admin: pemicu sidebar, identitas halaman, dan menu akun. */
export function AdminHeader({ user }: { user: AdminHeaderUser }) {
  const initial = user.nama.trim().charAt(0).toUpperCase() || "A";

  return (
    <header className="sticky top-0 z-40 flex h-14 shrink-0 items-center gap-3 border-b border-border bg-background/95 px-4 backdrop-blur supports-[backdrop-filter]:bg-background/80">
      <SidebarTrigger className="-ml-1" />
      <Separator orientation="vertical" className="h-5" />
      <p className="flex items-center gap-1.5 text-sm font-semibold text-ink">
        <ShieldCheck className="size-4 text-chili-600" />
        Panel Admin
      </p>

      <div className="ml-auto flex items-center gap-2">
        <Button asChild variant="ghost" size="sm" className="hidden text-muted-foreground sm:inline-flex">
          <Link href="/" target="_blank">
            <ExternalLink className="size-4" />
            Lihat Situs
          </Link>
        </Button>

        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <button
              type="button"
              aria-label="Menu akun admin"
              className="flex items-center gap-2 rounded-lg border border-border bg-card px-2 py-1.5 transition-colors hover:bg-muted"
            >
              <Avatar className="size-7">
                <AvatarFallback className="bg-chili-600 text-xs font-bold text-white">
                  {initial}
                </AvatarFallback>
              </Avatar>
              <span className="hidden max-w-32 truncate text-sm font-medium sm:block">
                {user.nama}
              </span>
            </button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" className="w-60">
            <DropdownMenuLabel>
              <p className="truncate text-sm font-semibold">{user.nama}</p>
              <p className="truncate text-xs font-normal text-muted-foreground">{user.email}</p>
              <p className="mt-1 inline-flex rounded-md bg-chili-600/10 px-1.5 py-0.5 text-[11px] font-semibold text-chili-700">
                Administrator
              </p>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem asChild>
              <Link href="/" target="_blank">
                <ExternalLink className="size-4" />
                Lihat Situs
              </Link>
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <form action={adminLogoutAction}>
              <button
                type="submit"
                className="flex w-full cursor-pointer items-center gap-2 rounded-sm px-2 py-1.5 text-sm text-destructive transition-colors hover:bg-destructive/10 focus-visible:bg-destructive/10 focus-visible:outline-none"
              >
                <LogOut className="size-4" />
                Keluar
              </button>
            </form>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </header>
  );
}