import type { Metadata } from "next";
import { ForgotForm } from "./forgot-form";
import { SITE } from "@/lib/site";

export const metadata: Metadata = {
  title: "Lupa Password",
  description: `Atur ulang password akun ${SITE.name} kamu.`,
};

export default function ForgotPasswordPage() {
  return <ForgotForm />;
}
