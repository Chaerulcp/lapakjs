"use client";

import { useState } from "react";
import Link from "next/link";
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

export function ForgotForm() {
  const [email, setEmail] = useState("");
  const [info, setInfo] = useState<{
    text: string;
    debugLink?: string;
  } | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError(null);
    setInfo(null);
    setLoading(true);
    try {
      const res = await fetch("/api/auth/forgot", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email }),
      });
      const data = (await res.json()) as {
        ok?: boolean;
        message?: string;
        error?: string;
        debug_link?: string;
      };
      if (res.ok && data.ok) {
        setInfo({ text: data.message ?? "Permintaan diproses.", debugLink: data.debug_link });
      } else {
        setError(data.error ?? "Permintaan gagal diproses. Coba lagi.");
      }
    } catch {
      setError("Terjadi gangguan. Coba lagi sebentar lagi.");
    } finally {
      setLoading(false);
    }
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="font-display text-2xl">Lupa password? 🤔</CardTitle>
        <CardDescription>
          Tenang, terjadi pada yang terbaik dari kita. Masukkan email akunmu dan kami
          kirimkan tautan pengaturan ulang.
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

          {info ? (
            <div className="rounded-lg border border-leaf-600/30 bg-leaf-600/10 px-4 py-3 text-sm text-leaf-600">
              {info.text}
              {info.debugLink ? (
                <a
                  href={info.debugLink}
                  className="mt-2 block font-semibold underline underline-offset-2"
                >
                  Mode pengembangan: buka tautan reset langsung →
                </a>
              ) : null}
            </div>
          ) : null}

          <div className="space-y-2">
            <Label htmlFor="email">Email terdaftar</Label>
            <Input
              id="email"
              type="email"
              autoComplete="email"
              placeholder="kamu@email.com"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />
          </div>

          <Button type="submit" className="w-full" disabled={loading}>
            {loading ? "Mengirim..." : "Kirim Tautan Reset"}
          </Button>
        </form>

        <p className="mt-6 text-center text-sm text-muted-foreground">
          Sudah ingat passwordnya?{" "}
          <Link href="/login" className="font-semibold text-chili-700 hover:underline">
            Masuk di sini
          </Link>
        </p>
      </CardContent>
    </Card>
  );
}
