import type { Metadata } from "next";
import { RegisterForm } from "./register-form";

export const metadata: Metadata = {
  title: "Daftar",
  description: "Buat akun Sambal Mama Ana untuk mulai belanja sambal rumahan asli.",
};

export default function RegisterPage() {
  return <RegisterForm />;
}
