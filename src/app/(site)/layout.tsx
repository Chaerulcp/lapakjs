import { auth } from "@/auth";
import { cartCount } from "@/lib/cart";
import Footer from "@/components/site/Footer";
import Navbar from "@/components/site/Navbar";

export default async function SiteLayout({ children }: { children: React.ReactNode }) {
  const [session, jumlahItem] = await Promise.all([auth(), cartCount()]);

  const user = session?.user
    ? {
        nama: session.user.name ?? "",
        email: session.user.email ?? "",
        role: session.user.role ?? "pelanggan",
      }
    : null;

  return (
    <div className="flex min-h-screen flex-col">
      <Navbar user={user} cartCount={jumlahItem} />
      <main className="flex-1">{children}</main>
      <Footer />
    </div>
  );
}
