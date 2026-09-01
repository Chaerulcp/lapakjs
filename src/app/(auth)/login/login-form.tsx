"use client";

import { useState } from "react";
import Link from "next/link";
import { signIn } from "next-auth/react";
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

/** Cegah open-redirect: hanya terima path relatif internal. */
function safeRedirect(url?: string): string {
  if (url && url.startsWith("/") && !url.startsWith("//")) return url;
  return "/";
}

export function LoginForm({ callbackUrl }: { callbackUrl?: string }) {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError(null);
    setLoading(true);
    try {
      const res = await signIn("credentials", {
        email,
        password,
        redirect: false,
      });
      if (res && !res.error) {
        // Reload penuh agar sesi terbaru terbaca di server component.
        window.location.assign(safeRedirect(callbackUrl));
        return;
      }
      setError(
        "Email atau password salah, atau akunmu belum diverifikasi. Cek email untuk tautan verifikasi."
      );
    } catch {
      setError("Terjadi gangguan saat masuk. Coba lagi sebentar lagi.");
    } finally {
      setLoading(false);
    }
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="font-display text-2xl">Selamat datang kembali 👋</CardTitle>
        <CardDescription>
          Masuk untuk lanjut belanja sambal favoritmu.
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
              <Link
                href={`/verify-email${email ? `?email=${encodeURIComponent(email)}` : ""}`}
                className="mt-2 block font-semibold text-chili-700 underline underline-offset-2"
              >
                Belum menerima email verifikasi? Kirim ulang →
              </Link>
            </div>
          ) : null}

          <div className="space-y-2">
            <Label htmlFor="email">Email</Label>
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

          <div className="space-y-2">
            <div className="flex items-center justify-between">
              <Label htmlFor="password">Password</Label>
              <Link
                href="/forgot-password"
                className="text-xs font-medium text-chili-700 hover:underline"
              >
                Lupa password?
              </Link>
            </div>
            <Input
              id="password"
              type="password"
              autoComplete="current-password"
              placeholder="••••••••"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
          </div>

          <Button type="submit" className="w-full" disabled={loading}>
            {loading ? "Memproses..." : "Masuk"}
          </Button>
        </form>

        <p className="mt-6 text-center text-sm text-muted-foreground">
          Belum punya akun?{" "}
          <Link href="/register" className="font-semibold text-chili-700 hover:underline">
            Daftar gratis
          </Link>
        </p>
      </CardContent>
    </Card>
  );
}
