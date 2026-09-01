import type { DefaultSession } from "next-auth";

declare module "next-auth" {
  interface Session {
    user: {
      id: string;
      role: "admin" | "reseller" | "pelanggan";
    } & DefaultSession["user"];
  }

  interface User {
    role?: "admin" | "reseller" | "pelanggan";
  }
}

declare module "next-auth/jwt" {
  interface JWT {
    id?: string;
    role?: "admin" | "reseller" | "pelanggan";
  }
}
