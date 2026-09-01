import type { Metadata } from "next";
import Link from "next/link";
import { ResetForm } from "./reset-form";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { SITE } from "@/lib/site";

export const metadata: Metadata = {
  title: "Atur Ulang Password",
  description: `Buat password baru untuk akun ${SITE.name} kamu.`,
};

export default async function ResetPasswordPage({
  searchParams,
}: {
  searchParams: Promise<{ token?: string }>;
}) {
  const { token } = await searchParams;

  if (!token) {
    return (
      <Card>
        <CardHeader>
          <CardTitle className="font-display text-2xl">Tautan tidak valid</CardTitle>
          <CardDescription>
            Tautan pengaturan ulang password ini tidak lengkap atau sudah kedaluwarsa.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-3">
          <Link
            href="/forgot-password"
            className="font-semibold text-chili-700 underline underline-offset-2"
          >
            Minta tautan baru →
          </Link>
        </CardContent>
      </Card>
    );
  }

  return <ResetForm token={token} />;
}
