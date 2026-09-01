"use client";

import { useState, useTransition, type ReactNode } from "react";
import { toast } from "sonner";
import type { ActionResult } from "@/app/admin/actions";
import {
  AlertDialog,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
import { Button } from "@/components/ui/button";

/**
 * Dialog konfirmasi untuk aksi berbahaya (hapus produk/konten, tolak bayar).
 * Membungkus server action + field tersembunyi; hasil aksi ditampilkan via toast.
 */
export function ConfirmDialog({
  trigger,
  title,
  description,
  confirmLabel = "Hapus",
  action,
  fields = {},
}: {
  trigger: ReactNode;
  title: string;
  description: string;
  confirmLabel?: string;
  action: (formData: FormData) => Promise<ActionResult>;
  fields?: Record<string, string | number>;
}) {
  const [open, setOpen] = useState(false);
  const [pending, startTransition] = useTransition();

  function submit(formData: FormData) {
    startTransition(async () => {
      const result = await action(formData);
      if (result.ok) {
        if (result.message) toast.success(result.message);
        setOpen(false);
      } else if (result.message) {
        toast.error(result.message);
      }
    });
  }

  return (
    <AlertDialog open={open} onOpenChange={(next) => !pending && setOpen(next)}>
      <AlertDialogTrigger asChild>{trigger}</AlertDialogTrigger>
      <AlertDialogContent>
        <form action={submit}>
          {Object.entries(fields).map(([key, value]) => (
            <input key={key} type="hidden" name={key} value={value} />
          ))}
          <AlertDialogHeader>
            <AlertDialogTitle>{title}</AlertDialogTitle>
            <AlertDialogDescription>{description}</AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter className="mt-4">
            <AlertDialogCancel type="button" disabled={pending}>
              Batal
            </AlertDialogCancel>
            <Button type="submit" variant="destructive" disabled={pending}>
              {pending ? "Memproses…" : confirmLabel}
            </Button>
          </AlertDialogFooter>
        </form>
      </AlertDialogContent>
    </AlertDialog>
  );
}