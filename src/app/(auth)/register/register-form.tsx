"use client";

import { useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";

interface FieldErrors {
  [field: string]: string[] | undefined;
}

export function RegisterForm() {
  const router = useRouter();
  const [nama, setNama] = useState("");
  const [email, setEmail] = useState("");
  const [noHp, setNoHp] = useState("");
  const [alamat, setAlamat] = useState("");
  const [password, setPassword] = useState("");
  const [konfirmasi, setKonfirmasi] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<FieldErrors>({});
  const [loading, setLoading] = useState(false);

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError(null);
    setFieldErrors({});

    if (password !== konfirmasi) {
      setError("Konfirmasi password tidak sama dengan password.");
      return;
    }

    setLoading(true);
    try {
      const res = await fetch("/api/auth/register", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ nama, email, no_hp: noHp, alamat, password }),
      });
      const data = (await res.json()) as {
        ok?: boolean;
        error?: string;
        details?: FieldErrors;
      };
      if (res.ok && data.ok) {
        router.push(`/verify-email?email=${encodeURIComponent(email)}&registered=1`);
        return;
      }
      setError(data.error ?? "Pendaftaran gagal. Coba lagi.");
      if (data.details) setFieldErrors(data.details);
    } catch {
      setError("Terjadi gangguan saat mendaftar. Coba lagi sebentar lagi.");
    } finally {
      setLoading(false);
    }
  }

  function fieldHint(field: string): string | null {
    const list = fieldErrors[field];
    return list && list.length > 0 ? list[0] : null;
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="font-display text-2xl">Buat akun baru</CardTitle>
        <CardDescription>
          Gratis selamanya. Harga spesial menanti para reseller.
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-4">
          {error ? (
            <div
              role="alert"
              className="rounded-lg border border-chili-200 bg-chili-50 px-4 py-3 text-sm text-chili-800"
            >
              {error}
            </div>
          ) : null}
          <div className="space-y-2">
            <Label htmlFor="nama">Nama lengkap</Label>
            <Input
              id="nama"
              placeholder="Nama kamu"
              autoComplete="name"
              value={nama}
              onChange={(e) => setNama(e.target.value)}
              required
              minLength={2}
            />
            {fieldHint("nama") ? (
              <p className="text-xs text-destructive">{fieldHint("nama")}</p>
            ) : null}
          </div>

          <div className="space-y-2">
            <Label htmlFor="email">Email</Label>
            <Input
              id="email"
              type="email"
              placeholder="kamu@email.com"
              autoComplete="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />
            {fieldHint("email") ? (
              <p className="text-xs text-destructive">{fieldHint("email")}</p>
            ) : null}
          </div>

          <div className="space-y-2">
            <Label htmlFor="no_hp">Nomor HP / WhatsApp</Label>
            <Input
              id="no_hp"
              type="tel"
              placeholder="08xxxxxxxxxx"
              autoComplete="tel"
              value={noHp}
              onChange={(e) => setNoHp(e.target.value)}
              required
              minLength={8}
            />
            {fieldHint("no_hp") ? (
              <p className="text-xs text-destructive">{fieldHint("no_hp")}</p>
            ) : null}
          </div>
          <div className="space-y-2">
            <Label htmlFor="alamat">Alamat pengiriman</Label>
            <Textarea
              id="alamat"
              placeholder="Jalan, kelurahan, kecamatan, kota, kode pos"
              value={alamat}
              onChange={(e) => setAlamat(e.target.value)}
              required
              minLength={5}
              rows={3}
            />
            {fieldHint("alamat") ? (
              <p className="text-xs text-destructive">{fieldHint("alamat")}</p>
            ) : null}
          </div>

          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="password">Password</Label>
              <Input
                id="password"
                type="password"
                placeholder="Minimal 8 karakter"
                autoComplete="new-password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                minLength={8}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="konfirmasi">Ulangi password</Label>
              <Input
                id="konfirmasi"
                type="password"
                placeholder="Ketik ulang"
                autoComplete="new-password"
                value={konfirmasi}
                onChange={(e) => setKonfirmasi(e.target.value)}
                required
                minLength={8}
              />
            </div>
          </div>
          {fieldHint("password") ? (
            <p className="text-xs text-destructive">{fieldHint("password")}</p>
          ) : null}
          <Button type="submit" className="w-full" disabled={loading}>
            {loading ? "Mendaftarkan..." : "Daftar Sekarang"}
          </Button>
        </form>

        <p className="mt-6 text-center text-sm text-muted-foreground">
          Sudah punya akun?{" "}
          <Link href="/login" className="font-semibold text-chili-700 hover:underline">
            Masuk di sini
          </Link>
        </p>
      </CardContent>
    </Card>
  );
}
