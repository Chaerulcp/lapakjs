import { Badge } from "@/components/ui/badge";
import { ORDER_STATUS_LABEL, PAYMENT_STATUS_LABEL } from "@/lib/format";
import { cn } from "@/lib/utils";

const ORDER_STATUS_CLASS: Record<string, string> = {
  menunggu: "bg-mango-300/40 text-amber-800 border-mango-400/50",
  diproses: "bg-ember-400/15 text-ember-600 border-ember-400/40",
  dikirim: "bg-chili-100 text-chili-700 border-chili-200",
  selesai: "bg-leaf-500/15 text-leaf-600 border-leaf-500/40",
  dibatalkan: "bg-destructive/10 text-destructive border-destructive/30",
};

const PAYMENT_STATUS_CLASS: Record<string, string> = {
  menunggu: "bg-mango-300/40 text-amber-800 border-mango-400/50",
  dikonfirmasi: "bg-leaf-500/15 text-leaf-600 border-leaf-500/40",
  gagal: "bg-destructive/10 text-destructive border-destructive/30",
};

/** Badge status pesanan (menunggu, diproses, dikirim, selesai, dibatalkan). */
export function OrderStatusBadge({ status, className }: { status: string; className?: string }) {
  return (
    <Badge variant="outline" className={cn(ORDER_STATUS_CLASS[status] ?? "", className)}>
      {ORDER_STATUS_LABEL[status] ?? status}
    </Badge>
  );
}

/** Badge status pembayaran (menunggu, dikonfirmasi, gagal). */
export function PaymentStatusBadge({ status, className }: { status: string; className?: string }) {
  return (
    <Badge variant="outline" className={cn(PAYMENT_STATUS_CLASS[status] ?? "", className)}>
      {PAYMENT_STATUS_LABEL[status] ?? status}
    </Badge>
  );
}

/** Label metode pembayaran dari nilai DB lama (transfer, qris, ewallet). */
export function metodeLabel(metode: string): string {
  const map: Record<string, string> = {
    transfer: "Transfer Bank",
    qris: "QRIS",
    ewallet: "E-Wallet",
  };
  return map[metode.toLowerCase()] ?? metode;
}
