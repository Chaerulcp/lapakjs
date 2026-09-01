import type { Metadata } from "next";
import { VerifyBox } from "./verify-box";

export const metadata: Metadata = {
  title: "Verifikasi Email",
  description: "Verifikasi email akun Sambal Mama Ana kamu.",
};

export default async function VerifyEmailPage({
  searchParams,
}: {
  searchParams: Promise<{ token?: string; email?: string; registered?: string }>;
}) {
  const { token, email, registered } = await searchParams;
  return <VerifyBox token={token} email={email} justRegistered={registered === "1"} />;
}
