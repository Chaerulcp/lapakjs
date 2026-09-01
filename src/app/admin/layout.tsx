import { redirect } from "next/navigation";
import type { Metadata } from "next";
import { auth } from "@/auth";
import { AdminHeader } from "@/components/admin/AdminHeader";
import { AdminSidebar } from "@/components/admin/AdminSidebar";
import { SidebarInset, SidebarProvider } from "@/components/ui/sidebar";
import { TooltipProvider } from "@/components/ui/tooltip";

export const metadata: Metadata = {
  title: {
    default: "Admin | Sambal Mama Ana",
    template: "%s | Admin Sambal Mama Ana",
  },
  description: "Panel administrasi toko Sambal Mama Ana.",
  robots: { index: false, follow: false },
};

/**
 * Guard panel admin:
 * - Belum login        → lempar ke /login dengan callbackUrl /admin.
 * - Login bukan admin  → lempar kembali ke beranda.
 * Guard kedua (role) juga dijalankan ulang di setiap server action.
 */
export default async function AdminLayout({ children }: { children: React.ReactNode }) {
  const session = await auth();

  if (!session?.user) {
    redirect("/login?callbackUrl=%2Fadmin");
  }
  if (session.user.role !== "admin") {
    redirect("/");
  }

  const user = {
    nama: session.user.name ?? "Admin",
    email: session.user.email ?? "",
  };

  return (
    <TooltipProvider>
      <SidebarProvider>
        <AdminSidebar user={user} />
        <SidebarInset className="min-h-svh flex-1 bg-muted/30">
          <AdminHeader user={user} />
          <main className="mx-auto w-full max-w-7xl flex-1 space-y-6 p-4 md:p-6">
            {children}
          </main>
        </SidebarInset>
      </SidebarProvider>
    </TooltipProvider>
  );
}