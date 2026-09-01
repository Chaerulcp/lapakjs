"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { CircleAlert, Landmark, QrCode, Wallet } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import { clearCartAction } from "@/app/(site)/actions";
import { cn } from "@/lib/utils";

export type CheckoutLine = {
  product_id: number;
  nama: string;
  harga: number;
  qty: number;
};

const METODE_OPTIONS = [
  {
    value: "transfer",
    label: "Transfer Bank",
    deskripsi: "BCA, BNI, Mandiri — verifikasi manual",
    icon: Landmark,
  },
  { value: "qris", label: "QRIS", deskripsi: "Scan QR untuk pembayaran", icon: QrCode },
  {
    value: "ewallet",
    label: "E-Wallet",
    deskripsi: "Dana, OVO, GoPay, ShopeePay",
    icon: Wallet,
  },
] as const;

/** Form checkout: alamat + metode pembayaran, lalu POST /api/orders. */
export default function CheckoutForm({
  lines,
  defaultAlamat,
  namaPenerima,
  noHp,
}: {
  lines: CheckoutLine[];
  defaultAlamat: string;
  namaPenerima: string;
  noHp: string;
}) {
  const router = useRouter();
  const [alamat, setAlamat] = useState(defaultAlamat);
  const [metode, setMetode] = useState<string>("transfer");
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError(null);

    if (alamat.trim().length < 10) {
      setError("Mohon isi alamat pengiriman yang lengkap (min. 10 karakter).");
      return;
    }

    setSubmitting(true);
    try {
      const res = await fetch("/api/orders", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          items: lines.map((l) => ({ product_id: l.product_id, jumlah: l.qty })),
          alamat: alamat.trim(),
          metode_pembayaran: metode,
        }),
      });

      const data = (await res.json().catch(() => null)) as
        | { ok?: boolean; order_id?: number; error?: string }
        | null;

      if (res.ok && data?.ok && data.order_id) {
        await clearCartAction();
        router.push(`/pesanan/${data.order_id}`);
        return;
      }

      setError(data?.error ?? "Gagal membuat pesanan. Silakan coba lagi.");
    } catch {
      setError("Terjadi gangguan jaringan. Silakan coba lagi.");
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {/* Penerima */}
      <div className="rounded-xl border border-border bg-muted/40 p-4 text-sm">
        <p className="font-semibold text-ink">{namaPenerima}</p>
        <p className="mt-0.5 font-mono text-muted-foreground tabular-nums">{noHp}</p>
        <p className="mt-1 text-xs text-muted-foreground">
          Ubah data penerima melalui halaman profil akunmu.
        </p>
      </div>

      {/* Alamat */}
      <div className="space-y-2">
        <Label htmlFor="alamat">Alamat Pengiriman</Label>
        <Textarea
          id="alamat"
          name="alamat"
          rows={4}
          required
          value={alamat}
          onChange={(e) => setAlamat(e.target.value)}
          placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota, provinsi, kode pos"
          className="resize-none"
        />
      </div>

      {/* Metode pembayaran */}
      <fieldset className="space-y-2">
        <legend className="text-sm font-medium leading-none">Metode Pembayaran</legend>
        <div className="grid gap-2">
          {METODE_OPTIONS.map((opt) => {
            const Icon = opt.icon;
            const aktif = metode === opt.value;
            return (
              <label
                key={opt.value}
                className={cn(
                  "flex cursor-pointer items-center gap-3 rounded-xl border p-3.5 transition-colors",
                  aktif
                    ? "border-chili-600 bg-chili-50 ring-1 ring-chili-600"
                    : "border-border bg-card hover:border-chili-200"
                )}
              >
                <input
                  type="radio"
                  name="metode_pembayaran"
                  value={opt.value}
                  checked={aktif}
                  onChange={() => setMetode(opt.value)}
                  className="size-4 accent-chili-600"
                />
                <Icon className="size-5 shrink-0 text-chili-600" />
                <span>
                  <span className="block text-sm font-semibold text-ink">{opt.label}</span>
                  <span className="block text-xs text-muted-foreground">{opt.deskripsi}</span>
                </span>
              </label>
            );
          })}
        </div>
      </fieldset>

      {error && (
        <p className="flex items-start gap-2 rounded-xl border border-destructive/30 bg-destructive/10 p-3 text-sm text-destructive">
          <CircleAlert className="mt-0.5 size-4 shrink-0" />
          {error}
        </p>
      )}

      <Button
        type="submit"
        disabled={submitting}
        size="lg"
        className="h-11 w-full bg-chili-600 text-base hover:bg-chili-700"
      >
        {submitting ? "Memproses pesanan…" : "Buat Pesanan"}
      </Button>
      <p className="text-center text-xs text-muted-foreground">
        Setelah pesanan dibuat, unggah bukti pembayaran di halaman pesanan untuk verifikasi.
      </p>
    </form>
  );
}
