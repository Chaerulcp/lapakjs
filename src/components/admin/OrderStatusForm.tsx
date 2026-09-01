"use client";

import { useEffect, useState, useTransition } from "react";
import { toast } from "sonner";
import { updateOrderStatusAction, type ActionResult } from "@/app/admin/actions";
import { ORDER_STATUS_LABEL } from "@/lib/format";

const ORDER_STATUS_OPTIONS = ["menunggu", "diproses", "dikirim", "selesai", "dibatalkan"];

/** Dropdown pengubah status pesanan — langsung tersimpan saat pilihan berubah. */
export function OrderStatusForm({ orderId, status }: { orderId: number; status: string }) {
  const [value, setValue] = useState(status);
  const [result, setResult] = useState<ActionResult | null>(null);
  const [pending, startTransition] = useTransition();

  // Sinkronkan bila data server berubah (mis. setelah aksi lain).
  // Pola resmi React: sesuaikan state saat props berubah ketika render.
  const [prevStatus, setPrevStatus] = useState(status);
  if (prevStatus !== status) {
    setPrevStatus(status);
    setValue(status);
  }

  useEffect(() => {
    if (result?.message) {
      if (result.ok) toast.success(result.message);
      else toast.error(result.message);
    }
  }, [result]);

  function handleChange(next: string) {
    setValue(next);
    const fd = new FormData();
    fd.set("id", String(orderId));
    fd.set("status", next);
    startTransition(async () => {
      setResult(await updateOrderStatusAction(fd));
    });
  }

  return (
    <select
      aria-label={`Ubah status pesanan #${orderId}`}
      value={value}
      disabled={pending}
      onChange={(e) => handleChange(e.target.value)}
      className="flex h-8 w-36 rounded-lg border border-input bg-background px-2 text-xs font-medium shadow-xs transition-colors focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 focus-visible:outline-none disabled:opacity-60"
    >
      {ORDER_STATUS_OPTIONS.map((opt) => (
        <option key={opt} value={opt}>
          {ORDER_STATUS_LABEL[opt] ?? opt}
        </option>
      ))}
    </select>
  );
}