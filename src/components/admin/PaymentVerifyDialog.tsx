"use client";

import { useState, useTransition, type ReactNode } from "react";
import { toast } from "sonner";
import { verifyPaymentAction, type ActionResult } from "@/app/admin/actions";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { formatRupiah } from "@/lib/format";

/**
 * Dialog verifikasi pembayaran: konfirmasi atau tolak, dengan catatan opsional.
 * Keputusan dikirim ke verifyPaymentAction.
 */
export function PaymentVerifyDialog({
  trigger,
  paymentId,
  orderId,
  orderTotal,
  keputusan,
}: {
  trigger: ReactNode;
  paymentId: number;
  orderId: number;
  orderTotal: number;
  keputusan: "konfirmasi" | "gagal";
}) {
  const konfirmasi = keputusan === "konfirmasi";
  const [open, setOpen] = useState(false);
  const [catatan, setCatatan] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [pending, startTransition] = useTransition();

  function submit(formData: FormData) {
    setError(null);
    formData.set("id", String(paymentId));
    formData.set("keputusan", keputusan);
    startTransition(async () => {
      const result: ActionResult = await verifyPaymentAction(formData);
      if (result.ok) {
        toast.success(result.message);
        setOpen(false);
      } else {
        setError(result.message);
        toast.error(result.message);
      }
    });
  }

  return (
    <Dialog
      open={open}
      onOpenChange={(next) => {
        if (pending) return;
        setOpen(next);
        if (!next) {
          setError(null);
          setCatatan("");
        }
      }}
    >
      <DialogTrigger asChild>{trigger}</DialogTrigger>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>
            {konfirmasi ? "Konfirmasi Pembayaran" : "Tolak Pembayaran"} #{paymentId}
          </DialogTitle>
          <DialogDescription>
            Pesanan #{orderId} • total {formatRupiah(orderTotal)}.{" "}
            {konfirmasi
              ? "Pembayaran ditandai sah dan pesanan otomatis diproses."
              : "Pembayaran ditandai gagal; pelanggan dapat mengunggah bukti baru."}
          </DialogDescription>
        </DialogHeader>

        <form action={submit} className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor={`catatan-${paymentId}`}>Catatan (opsional)</Label>
            <Textarea
              id={`catatan-${paymentId}`}
              name="catatan"
              rows={3}
              maxLength={1000}
              value={catatan}
              onChange={(e) => setCatatan(e.target.value)}
              placeholder={
                konfirmasi
                  ? "mis. Nominal sesuai, BCA a.n. pelanggan."
                  : "mis. Bukti transfer tidak valid / nominal tidak sesuai."
              }
              className="resize-none"
            />
          </div>

          {error ? (
            <p
              role="alert"
              className="rounded-lg border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive"
            >
              {error}
            </p>
          ) : null}

          <DialogFooter>
            <Button
              type="button"
              variant="outline"
              onClick={() => setOpen(false)}
              disabled={pending}
            >
              Batal
            </Button>
            <Button
              type="submit"
              variant={konfirmasi ? "default" : "destructive"}
              disabled={pending}
            >
              {pending ? "Memproses…" : konfirmasi ? "Konfirmasi Pembayaran" : "Tolak Pembayaran"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}