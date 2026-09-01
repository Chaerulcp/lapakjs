import type { Metadata } from "next";
import { LoginForm } from "./login-form";
import { SITE } from "@/lib/site";

export const metadata: Metadata = {
  title: "Masuk",
  description: `Masuk ke akun ${SITE.name} untuk belanja dan memantau pesanan.`,
};

export default async function LoginPage({
  searchParams,
}: {
  searchParams: Promise<{ callbackUrl?: string }>;
}) {
  const { callbackUrl } = await searchParams;
  return <LoginForm callbackUrl={callbackUrl} />;
}
