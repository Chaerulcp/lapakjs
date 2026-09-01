import { NextResponse } from "next/server";
import crypto from "node:crypto";
import { z } from "zod";
import { prisma } from "@/lib/db";
import { hashPassword } from "@/lib/password";
import { sendMail, emailLayout } from "@/lib/mailer";
import { SITE } from "@/lib/site";
import { ACTIVITY_TYPES, logActivity } from "@/lib/activity";

const registerSchema = z.object({
  nama: z.string().trim().min(2, "Nama minimal 2 karakter.").max(100),
  email: z.email("Format email tidak valid.").toLowerCase(),
  no_hp: z.string().trim().min(8, "Nomor HP minimal 8 digit.").max(20),
  alamat: z.string().trim().min(5, "Alamat minimal 5 karakter.").max(500),
  password: z.string().min(8, "Password minimal 8 karakter.").max(100),
});

export function appUrl(): string {
  return (process.env.APP_URL || "http://localhost:3000").replace(/\/$/, "");
}

export async function POST(req: Request) {
  let body: unknown;
  try {
    body = await req.json();
  } catch {
    return NextResponse.json({ error: "Body request tidak valid." }, { status: 400 });
  }

  const parsed = registerSchema.safeParse(body);
  if (!parsed.success) {
    return NextResponse.json(
      {
        error: "Data pendaftaran tidak valid.",
        details: z.flattenError(parsed.error).fieldErrors,
      },
      { status: 400 }
    );
  }

  const { nama, email, no_hp, alamat, password } = parsed.data;

  const existing = await prisma.user.findUnique({ where: { email } });
  if (existing) {
    return NextResponse.json(
      { error: "Email sudah terdaftar. Silakan masuk dengan akun tersebut." },
      { status: 409 }
    );
  }

  const verificationToken = crypto.randomBytes(24).toString("hex");
  const user = await prisma.user.create({
    data: {
      nama,
      email,
      no_hp,
      alamat,
      password: await hashPassword(password),
      role: "pelanggan",
      status: "active",
      is_verified: false,
      verification_token: verificationToken,
    },
  });

  const link = `${appUrl()}/verify-email?token=${verificationToken}&email=${encodeURIComponent(email)}`;
  const sent = await sendMail(
    email,
    `Verifikasi email kamu — ${SITE.name} ${SITE.emoji}`,
    emailLayout(
      "Verifikasi Email Kamu",
      `<p>Halo <strong>${nama}</strong>,</p>
       <p>Terima kasih sudah mendaftar di ${SITE.name}! Klik tombol di bawah untuk mengaktifkan akunmu:</p>
       <p style="margin:24px 0"><a href="${link}" style="background:#a81c0d;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold">Verifikasi Email</a></p>
       <p>Atau salin tautan ini ke browser:<br><a href="${link}">${link}</a></p>
       <p>Tautan ini hanya bisa dipakai satu kali. Jika kamu tidak mendaftar, abaikan email ini.</p>`
    )
  );

  await logActivity(user.id, ACTIVITY_TYPES.register, `Pengguna baru terdaftar: ${email}`);

  return NextResponse.json(
    {
      ok: true,
      message: "Akun berhasil dibuat! Cek kotak masuk email kamu untuk verifikasi.",
      // Tanpa SMTP (mode pengembangan), tautan dikembalikan agar alur tetap bisa diuji.
      ...(sent ? {} : { debug_link: link }),
    },
    { status: 201 }
  );
}
