"use client";

import { useEffect, useRef, useState } from "react";
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

type VerifyStatus = "checking" | "success" | "error" | "idle";

export function VerifyBox({
  token,
  email,
  justRegistered,
}: {
  token?: string;
  email?: string;
  justRegistered?: boolean;
}) {
  const [status, setStatus] = useState<VerifyStatus>(token ? "checking" : "idle");
  const [message, setMessage] = useState("");
  const [resendEmail, setResendEmail] = useState(email ?? "");
  const [resendInfo, setResendInfo] = useState<{
    text: string;
    debugLink?: string;
  } | null>(null);
  const [busy, setBusy] = useState(false);
  const ranOnce = useRef(false);

  useEffect(() => {
    if (!token || ranOnce.current) return;
    ranOnce.current = true;
    (async () => {
      try {
        const params = new URLSearchParams({ token });
        if (email) params.set("email", email);
        const res = await fetch(`/api/auth/verify?${params.toString()}`, {
          method: "POST",
        });
        const data = (await res.json()) as { ok?: boolean; message?: string; error?: string };
        setStatus(res.ok && data.ok ? "success" : "error");
        setMessage(data.message ?? data.error ?? "Verifikasi gagal.");
      } catch {
        setStatus("error");
        setMessage("Terjadi gangguan saat verifikasi. Coba lagi.");
      }
    })();
  }, [token, email]);

  async function handleResend(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setBusy(true);
    setResendInfo(null);
    try {
      const res = await fetch("/api/auth/resend", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email: resendEmail }),
      });
      const data = (await res.json()) as {
        ok?: boolean;
        message?: string;
        error?: string;
        debug_link?: string;
      };
      setResendInfo({
        text: data.message ?? data.error ?? "Permintaan diproses.",
        debugLink: data.debug_link,
      });
    } catch {
      setResendInfo({ text: "Terjadi gangguan. Coba lagi sebentar lagi." });
    } finally {
      setBusy(false);
    }
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="font-display text-2xl">Verifikasi email ✉️</CardTitle>
        <CardDescription>
          {justRegistered
            ? "Akun berhasil dibuat! Satu langkah lagi."
            : "Kami mengirim tautan verifikasi ke email kamu."}
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-6">
        {status === "checking" ? (
          <p className="text-sm text-muted-foreground">Memverifikasi tautan...</p>
        ) : null}

        {status === "success" ? (
          <div className="rounded-lg border border-leaf-600/30 bg-leaf-600/10 px-4 py-3 text-sm text-leaf-600">
            ✅ {message}
            <Link
              href="/login"
              className="mt-2 block font-semibold underline underline-offset-2"
            >
              Masuk ke akunmu →
            </Link>
          </div>
        ) : null}

        {status === "error" ? (
          <div className="rounded-lg border border-chili-200 bg-chili-50 px-4 py-3 text-sm text-chili-800">
            {message}
          </div>
        ) : null}

        {status === "idle" ? (
          <p className="text-sm text-muted-foreground">
            Buka kotak masuk email kamu dan klik tautan verifikasi dari Sambal Mama
            Ana. Tidak menerima email? Kirim ulang di bawah.
          </p>
        ) : null}

        <form onSubmit={handleResend} className="space-y-3 border-t pt-5">
          <p className="text-sm font-semibold">Kirim ulang email verifikasi</p>
          <div className="space-y-2">
            <Label htmlFor="resend-email">Email terdaftar</Label>
            <Input
              id="resend-email"
              type="email"
              value={resendEmail}
              onChange={(e) => setResendEmail(e.target.value)}
              placeholder="kamu@email.com"
              required
            />
          </div>
          <Button type="submit" variant="outline" className="w-full" disabled={busy}>
            {busy ? "Mengirim..." : "Kirim Ulang Tautan"}
          </Button>
          {resendInfo ? (
            <div className="rounded-lg bg-muted px-4 py-3 text-sm">
              {resendInfo.text}
              {resendInfo.debugLink ? (
                <a
                  href={resendInfo.debugLink}
                  className="mt-2 block font-semibold text-chili-700 underline underline-offset-2"
                >
                  Mode pengembangan: buka tautan verifikasi langsung →
                </a>
              ) : null}
            </div>
          ) : null}
        </form>

        <p className="text-center text-sm text-muted-foreground">
          <Link href="/login" className="font-semibold text-chili-700 hover:underline">
            ← Kembali ke halaman masuk
          </Link>
        </p>
      </CardContent>
    </Card>
  );
}
