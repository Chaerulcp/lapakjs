import { Badge } from "@/components/ui/badge";
import { ACTIVITY_TYPE_LABEL } from "@/lib/activity";
import { cn } from "@/lib/utils";

/** Badge role pengguna (admin/reseller/pelanggan). */
export function RoleBadge({ role, className }: { role: string; className?: string }) {
  const style: Record<string, string> = {
    admin: "bg-chili-600/10 text-chili-700 border-chili-600/30",
    reseller: "bg-ember-600/10 text-ember-600 border-ember-600/30",
    pelanggan: "bg-muted text-muted-foreground border-border",
  };
  const label: Record<string, string> = {
    admin: "Admin",
    reseller: "Reseller",
    pelanggan: "Pelanggan",
  };
  return (
    <Badge variant="outline" className={cn("capitalize", style[role] ?? "", className)}>
      {label[role] ?? role}
    </Badge>
  );
}

/** Badge status akun (active/inactive). */
export function UserStatusBadge({ status, className }: { status: string; className?: string }) {
  const aktif = status === "active";
  return (
    <Badge
      variant="outline"
      className={cn(
        aktif
          ? "bg-leaf-500/15 text-leaf-600 border-leaf-500/40"
          : "bg-destructive/10 text-destructive border-destructive/30",
        className
      )}
    >
      {aktif ? "Aktif" : "Nonaktif"}
    </Badge>
  );
}

/** Badge tipe aktivitas untuk halaman log. */
export function ActivityTypeBadge({ type, className }: { type: string; className?: string }) {
  const isAdmin = type.startsWith("admin_");
  return (
    <Badge
      variant="outline"
      className={cn(
        isAdmin
          ? "bg-chili-50 text-chili-700 border-chili-200"
          : "bg-secondary text-secondary-foreground border-border",
        "whitespace-nowrap",
        className
      )}
    >
      {ACTIVITY_TYPE_LABEL[type] ?? type}
    </Badge>
  );
}