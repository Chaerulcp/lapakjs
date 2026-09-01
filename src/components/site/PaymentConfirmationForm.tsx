"use client";

import { useRef, useState } from "react";
import { useRouter } from "next/navigation";
import { CircleAlert, CircleCheck, Upload } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { cn } from "@/lib/utils";

/** Kanal pembayaran sesuai metode pesanan (nilai DB lama: transfer/qris/ewallet). */
const KANAL_PER_METODE: Record<string, string[]> = {
  transfer: ["BCA", "BNI", "Mandiri"],
  qris: ["QRIS"],
  ewallet: ["Dana", "OVO", "GoPay", "ShopeePay"],
};

/** Form unggah bukti pembayaran → POST /api/payments (FormData). */
export default function PaymentConfirmationForm({
  orderId,
  orderMetode,
}: {
  orderId: number;
  orderMetode: string;
}) {
  const router = useRouter();
  const fileRef = useRef<HTMLInputElement>(null);
  const kanal = KANAL_PER_METODE[orderMetode.toLowerCase()] ?? KANAL_PER_METODE.transfer!;
  const [metode, setMetode] = useState<string>(kanal[0] ?? "BCA");
  const [namaFile, setNamaFile] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [sukses, setSukses] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError(null);

    const file = fileRef.current?.files?.[0];
    if (!file) {
      setError("Pilih file bukti transfer terlebih dahulu.");
      return;
    }

    setSubmitting(true);
    try {
      const fd = new FormData();
      fd.append("order_id", String(orderId));
      fd.append("metode", metode);
      fd.append("file", file);

      const res = await fetch("/api/payments", { method: "POST", body: fd });
      const data = (await res.json().catch(() => null)) as
        | { ok?: boolean; error?: string }
        | null;

      if (res.ok && data?.ok !== false) {
        setSukses(true);
        router.refresh();
        return;
      }
      setError(data?.error ?? "Gagal mengunggah bukti pembayaran. Coba lagi.");
    } catch {
      setError("Terjadi gangguan jaringan. Silakan coba lagi.");
    } finally {
      setSubmitting(false);
    }
  }

  if (sukses) {
    return (
      <div className="flex items-start gap-3 rounded-xl border border-leaf-500/40 bg-leaf-500/10 p-4">
        <CircleCheck className="mt-0.5 size-5 shrink-0 text-leaf-600" />
        <div className="text-sm">
          <p className="font-semibold text-leaf-600">Bukti pembayaran terkirim!</p>
          <p className="mt-1 text-muted-foreground">
            Tim kami akan memverifikasi pembayaranmu secepat mungkin. Pantau statusnya di halaman
            ini.
          </p>
        </div>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div className="space-y-2">
        <Label htmlFor="metode-bayar">Dibayar melalui</Label>
        <select
          id="metode-bayar"
          value={metode}
          onChange={(e) => setMetode(e.target.value)}
          className="flex h-9 w-full rounded-lg border border-input bg-background px-3 text-sm shadow-xs transition-colors focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 focus-visible:outline-none"
        >
          {kanal.map((k) => (
            <option key={k} value={k}>
              {k}
            </option>
          ))}
        </select>
      </div>

      <div className="space-y-2">
        <Label htmlFor="bukti">Bukti Transfer / Pembayaran</Label>
        <label
          htmlFor="bukti"
          className={cn(
            "flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed p-6 text-center transition-colors",
            namaFile ? "border-leaf-500/60 bg-leaf-500/5" : "border-border hover:border-chili-300"
          )}
        >
          <Upload className="size-6 text-chili-600" />
          {namaFile ? (
            <span className="text-sm font-medium text-leaf-600">{namaFile}</span>
          ) : (
            <span className="text-sm text-muted-foreground">
              Klik untuk pilih gambar/screenshot bukti bayar
            </span>
          )}
          <input
            ref={fileRef}
            id="bukti"
            name="file"
            type="file"
            accept="image/*,application/pdf"
            required
            className="sr-only"
            onChange={(e) => setNamaFile(e.target.files?.[0]?.name ?? null)}
          />
        </label>
        <p className="text-xs text-muted-foreground">Format: JPG, PNG, atau PDF. Maks. 2 MB.</p>
      </div>

      {error && (
        <p className="flex items-start gap-2 rounded-xl border border-destructive/30 bg-destructive/10 p-3 text-sm text-destructive">
          <CircleAlert className="mt-0.5 size-4 shrink-0" />
          {error}
        </p>
      )}

      <Button
        type="submit"
        disabled={submitting}
        className="w-full bg-chili-600 hover:bg-chili-700"
      >
        {submitting ? "Mengunggah…" : "Kirim Bukti Pembayaran"}
      </Button>
    </form>
  );
}
