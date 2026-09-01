import type { Metadata } from "next";
import { ForgotForm } from "./forgot-form";

export const metadata: Metadata = {
  title: "Lupa Password",
  description: "Atur ulang password akun Sambal Mama Ana kamu.",
};

export default function ForgotPasswordPage() {
  return <ForgotForm />;
}
