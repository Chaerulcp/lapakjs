import type { Metadata } from "next";
import { RegisterForm } from "./register-form";
import { SITE } from "@/lib/site";

export const metadata: Metadata = {
  title: "Daftar",
  description: `Buat akun ${SITE.name} untuk mulai belanja produk pilihan berkualitas.`,
};

export default function RegisterPage() {
  return <RegisterForm />;
}
