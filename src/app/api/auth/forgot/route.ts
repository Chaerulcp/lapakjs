import { NextResponse } from "next/server";
import crypto from "node:crypto";
import { z } from "zod";
import { prisma } from "@/lib/db";
import { sendMail, emailLayout } from "@/lib/mailer";
import { ACTIVITY_TYPES, logActivity } from "@/lib/activity";

function appUrl(): string {
  return (process.env.APP_URL || "http://localhost:3000").replace(/\/$/, "");
}

const RESET_TTL_MS = 60 * 60 * 1000; // 1 jam

const bodySchema = z.object({ email: z.email("Email tidak valid.").toLowerCase() });

export async function POST(req: Request) {
  let body: unknown;
  try {
    body = await req.json();
  } catch {
    return NextResponse.json({ error: "Body request tidak valid." }, { status: 400 });
  }

  const parsed = bodySchema.safeParse(body);
  if (!parsed.success) {
    return NextResponse.json({ error: "Email tidak valid." }, { status: 400 });
  }
  const email = parsed.data.email;

  const generic = {
    ok: true,
    message:
      "Jika email tersebut terdaftar, tautan pengaturan ulang password telah dikirim. Cek kotak masuk (atau folder spam) kamu.",
  } as const;

  const user = await prisma.user.findUnique({ where: { email } });
  if (!user) return NextResponse.json(generic);

  // Buang token lama, buat yang baru.
  await prisma.passwordReset.deleteMany({ where: { user_id: user.id } });
  const token = crypto.randomBytes(24).toString("hex");
  await prisma.passwordReset.create({
    data: {
      user_id: user.id,
      token,
      expires_at: new Date(Date.now() + RESET_TTL_MS),
    },
  });

  const link = `${appUrl()}/reset-password?token=${token}`;
  const sent = await sendMail(
    email,
    "Atur ulang password kamu — Sambal Mama Ana 🌶️",
    emailLayout(
      "Atur Ulang Password",
      `<p>Halo <strong>${user.nama}</strong>,</p>
       <p>Kamu meminta untuk mengatur ulang password. Klik tombol di bawah untuk membuat password baru (berlaku 1 jam):</p>
       <p style="margin:24px 0"><a href="${link}" style="background:#a81c0d;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold">Atur Ulang Password</a></p>
       <p>Atau salin tautan ini ke browser:<br><a href="${link}">${link}</a></p>
       <p>Jika kamu tidak meminta ini, abaikan email ini — password kamu tetap aman.</p>`
    )
  );

  await logActivity(
    user.id,
    ACTIVITY_TYPES.forgot_password,
    `Permintaan pengaturan ulang password untuk ${email}.`
  );

  return NextResponse.json({
    ...generic,
    ...(sent ? {} : { debug_link: link }),
  });
}
