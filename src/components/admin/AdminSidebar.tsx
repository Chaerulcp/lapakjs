"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import {
  Activity,
  CreditCard,
  LayoutDashboard,
  Newspaper,
  Package,
  Receipt,
  Store,
  Users,
} from "lucide-react";
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarRail,
} from "@/components/ui/sidebar";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";

export type AdminSidebarUser = {
  nama: string;
  email: string;
};

const NAV_ITEMS = [
  { href: "/admin", label: "Dashboard", icon: LayoutDashboard, exact: true },
  { href: "/admin/produk", label: "Produk", icon: Package },
  { href: "/admin/pesanan", label: "Pesanan", icon: Receipt },
  { href: "/admin/pembayaran", label: "Pembayaran", icon: CreditCard },
  { href: "/admin/pengguna", label: "Pengguna", icon: Users },
  { href: "/admin/konten", label: "Konten", icon: Newspaper },
  { href: "/admin/log", label: "Log Aktivitas", icon: Activity },
];

function isActive(pathname: string, href: string, exact?: boolean): boolean {
  if (exact) return pathname === href;
  return pathname === href || pathname.startsWith(href + "/");
}

/** Navigasi samping panel admin (shadcn Sidebar, collapse jadi rel ikon). */
export function AdminSidebar({ user }: { user: AdminSidebarUser }) {
  const pathname = usePathname();
  const initial = user.nama.trim().charAt(0).toUpperCase() || "A";

  return (
    <Sidebar collapsible="icon">
      <SidebarHeader>
        <Link
          href="/admin"
          className="flex items-center gap-2.5 rounded-lg px-2 py-1.5 transition-colors hover:bg-sidebar-accent"
        >
          <span className="flex size-9 shrink-0 items-center justify-center rounded-xl bg-chili-600 text-lg shadow-sm">
            🌶️
          </span>
          <span className="min-w-0 leading-tight">
            <span className="block truncate font-display text-sm font-bold tracking-tight">
              Sambal Mama Ana
            </span>
            <span className="block text-[11px] font-medium text-muted-foreground">
              Panel Admin
            </span>
          </span>
        </Link>
      </SidebarHeader>

      <SidebarContent>
        <SidebarGroup>
          <SidebarGroupLabel>Menu Utama</SidebarGroupLabel>
          <SidebarGroupContent>
            <SidebarMenu>
              {NAV_ITEMS.map((item) => (
                <SidebarMenuItem key={item.href}>
                  <SidebarMenuButton
                    asChild
                    isActive={isActive(pathname, item.href, item.exact)}
                    tooltip={item.label}
                  >
                    <Link href={item.href}>
                      <item.icon className="size-4" />
                      <span>{item.label}</span>
                    </Link>
                  </SidebarMenuButton>
                </SidebarMenuItem>
              ))}
            </SidebarMenu>
          </SidebarGroupContent>
        </SidebarGroup>

        <SidebarGroup>
          <SidebarGroupLabel>Toko</SidebarGroupLabel>
          <SidebarGroupContent>
            <SidebarMenu>
              <SidebarMenuItem>
                <SidebarMenuButton asChild tooltip="Lihat Situs">
                  <Link href="/" target="_blank">
                    <Store className="size-4" />
                    <span>Lihat Situs</span>
                  </Link>
                </SidebarMenuButton>
              </SidebarMenuItem>
            </SidebarMenu>
          </SidebarGroupContent>
        </SidebarGroup>
      </SidebarContent>

      <SidebarFooter>
        <div className="flex items-center gap-2.5 rounded-lg border border-sidebar-border bg-sidebar-accent/40 px-2.5 py-2">
          <Avatar className="size-8 shrink-0">
            <AvatarFallback className="bg-chili-600 text-xs font-bold text-white">
              {initial}
            </AvatarFallback>
          </Avatar>
          <span className="min-w-0 leading-tight">
            <span className="block truncate text-xs font-semibold">{user.nama}</span>
            <span className="block truncate text-[11px] text-muted-foreground">
              {user.email}
            </span>
          </span>
        </div>
      </SidebarFooter>

      <SidebarRail />
    </Sidebar>
  );
}