"use client";

import { useEffect, useRef, useState, useTransition, type ReactNode } from "react";
import { toast } from "sonner";
import {
  createContentAction,
  updateContentAction,
  type ActionResult,
} from "@/app/admin/actions";
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
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { imageUrl } from "@/lib/format";

export type ContentFormValues = {
  id: number;
  judul: string;
  penulis: string;
  isi: string;
  gambar: string | null;
  video: string | null;
};

/** Dialog tambah/ubah konten (artikel/resep/promo). */
export function ContentFormDialog({
  trigger,
  initial,
}: {
  trigger: ReactNode;
  /** Bila diisi → mode ubah; bila kosong → mode tambah. */
  initial?: ContentFormValues;
}) {
  const isEdit = Boolean(initial);
  const [open, setOpen] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [pending, startTransition] = useTransition();
  const fileRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (!open) setError(null);
  }, [open]);

  function submit(formData: FormData) {
    setError(null);
    startTransition(async () => {
      const result: ActionResult = isEdit
        ? await updateContentAction(formData)
        : await createContentAction(formData);
      if (result.ok) {
        toast.success(result.message);
        setOpen(false);
        if (fileRef.current) fileRef.current.value = "";
      } else {
        setError(result.message);
        toast.error(result.message);
      }
    });
  }

  return (
    <Dialog open={open} onOpenChange={(next) => !pending && setOpen(next)}>
      <DialogTrigger asChild>{trigger}</DialogTrigger>
      <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>{isEdit ? `Ubah Konten #${initial!.id}` : "Tambah Konten"}</DialogTitle>
          <DialogDescription>
            Konten tampil di situs publik (artikel, resep, promo). Gambar dan video opsional.
          </DialogDescription>
        </DialogHeader>

        <form action={submit} className="space-y-4">
          {isEdit ? <input type="hidden" name="id" value={initial!.id} /> : null}

          <div className="space-y-2">
            <Label htmlFor="konten-judul">Judul</Label>
            <Input
              id="konten-judul"
              name="judul"
              required
              minLength={3}
              maxLength={200}
              defaultValue={initial?.judul}
              placeholder="mis. Resep Nasi Goreng Sambal Terasi"
            />
          </div>

          <div className="space-y-2">
            <Label htmlFor="konten-penulis">Penulis</Label>
            <Input
              id="konten-penulis"
              name="penulis"
              required
              minLength={2}
              maxLength={100}
              defaultValue={initial?.penulis ?? "Admin"}
              placeholder="mis. Admin Toko"
            />
          </div>

          <div className="space-y-2">
            <Label htmlFor="konten-isi">Isi Konten</Label>
            <Textarea
              id="konten-isi"
              name="isi"
              required
              minLength={10}
              rows={7}
              defaultValue={initial?.isi}
              placeholder="Tulis isi artikel/resep/promo di sini…"
              className="resize-y"
            />
          </div>

          <div className="space-y-2">
            <Label htmlFor="konten-video">Tautan Video (opsional)</Label>
            <Input
              id="konten-video"
              name="video"
              type="url"
              maxLength={500}
              defaultValue={initial?.video ?? ""}
              placeholder="https://youtube.com/watch?v=…"
            />
          </div>

          <div className="space-y-2">
            <Label htmlFor="konten-gambar">
              Gambar {isEdit ? "(opsional — kosongkan untuk mempertahankan)" : "(opsional)"}
            </Label>
            {isEdit && initial!.gambar ? (
              // eslint-disable-next-line @next/next/no-img-element
              <img
                src={imageUrl(initial!.gambar, "")}
                alt={`Gambar ${initial!.judul}`}
                className="mb-2 h-20 w-20 rounded-lg border border-border object-cover"
              />
            ) : null}
            <Input
              ref={fileRef}
              id="konten-gambar"
              name="gambar"
              type="file"
              accept="image/jpeg,image/png,image/webp"
            />
            <p className="text-xs text-muted-foreground">JPG, PNG, atau WEBP. Maksimal 5 MB.</p>
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
            <Button type="submit" disabled={pending}>
              {pending ? "Menyimpan…" : isEdit ? "Simpan Perubahan" : "Tambah Konten"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}