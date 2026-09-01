"use client";

import { useRef, useState, useTransition, type ReactNode } from "react";
import { toast } from "sonner";
import {
  createProductAction,
  updateProductAction,
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

export type ProductFormValues = {
  id: number;
  nama: string;
  kategori: string;
  deskripsi: string;
  harga: number;
  harga_reseller: number;
  stok: number;
  foto: string;
};

/** Dialog tambah/ubah produk (unggah foto via FormData → server action). */
export function ProductFormDialog({
  trigger,
  initial,
}: {
  trigger: ReactNode;
  /** Bila diisi → mode ubah; bila kosong → mode tambah. */
  initial?: ProductFormValues;
}) {
  const isEdit = Boolean(initial);
  const [open, setOpen] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [pending, startTransition] = useTransition();
  const fileRef = useRef<HTMLInputElement>(null);

  function submit(formData: FormData) {
    setError(null);
    startTransition(async () => {
      const result: ActionResult = isEdit
        ? await updateProductAction(formData)
        : await createProductAction(formData);
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
    <Dialog
      open={open}
      onOpenChange={(next) => {
        if (pending) return;
        setOpen(next);
        if (!next) setError(null);
      }}
    >
      <DialogTrigger asChild>{trigger}</DialogTrigger>
      <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>{isEdit ? `Ubah Produk #${initial!.id}` : "Tambah Produk"}</DialogTitle>
          <DialogDescription>
            {isEdit
              ? "Perbarui detail produk. Kosongkan foto bila tidak ingin mengganti gambar."
              : "Isi detail produk baru. Foto wajib diunggah."}
          </DialogDescription>
        </DialogHeader>

        <form action={submit} className="space-y-4">
          {isEdit ? <input type="hidden" name="id" value={initial!.id} /> : null}

          <div className="space-y-2">
            <Label htmlFor="produk-nama">Nama Produk</Label>
            <Input
              id="produk-nama"
              name="nama"
              required
              minLength={3}
              maxLength={150}
              defaultValue={initial?.nama}
              placeholder="mis. Sambal Bawang Level 5"
            />
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div className="space-y-2">
              <Label htmlFor="produk-kategori">Kategori</Label>
              <Input
                id="produk-kategori"
                name="kategori"
                required
                minLength={2}
                maxLength={80}
                defaultValue={initial?.kategori}
                placeholder="mis. Sambal Bawang"
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="produk-stok">Stok</Label>
              <Input
                id="produk-stok"
                name="stok"
                type="number"
                min={0}
                max={1000000}
                required
                defaultValue={initial?.stok ?? 0}
              />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div className="space-y-2">
              <Label htmlFor="produk-harga">Harga Jual (Rp)</Label>
              <Input
                id="produk-harga"
                name="harga"
                type="number"
                min={0}
                step={1}
                required
                defaultValue={initial?.harga ?? 0}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="produk-harga-reseller">Harga Reseller (Rp)</Label>
              <Input
                id="produk-harga-reseller"
                name="harga_reseller"
                type="number"
                min={0}
                step={1}
                required
                defaultValue={initial?.harga_reseller ?? 0}
              />
            </div>
          </div>

          <div className="space-y-2">
            <Label htmlFor="produk-deskripsi">Deskripsi</Label>
            <Textarea
              id="produk-deskripsi"
              name="deskripsi"
              required
              minLength={10}
              rows={4}
              defaultValue={initial?.deskripsi}
              placeholder="Ceritakan rasa, level pedas, komposisi, dan daya tahan produk."
              className="resize-none"
            />
          </div>

          <div className="space-y-2">
            <Label htmlFor="produk-foto">
              Foto Produk {isEdit ? "(opsional — kosongkan untuk mempertahankan)" : "(wajib)"}
            </Label>
            {isEdit ? (
              // eslint-disable-next-line @next/next/no-img-element
              <img
                src={imageUrl(initial!.foto)}
                alt={`Foto ${initial!.nama}`}
                className="mb-2 h-20 w-20 rounded-lg border border-border object-cover"
              />
            ) : null}
            <Input
              ref={fileRef}
              id="produk-foto"
              name="foto"
              type="file"
              accept="image/jpeg,image/png,image/webp"
              required={!isEdit}
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
              {pending ? "Menyimpan…" : isEdit ? "Simpan Perubahan" : "Tambah Produk"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}