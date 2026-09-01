import { NextResponse } from "next/server";
import crypto from "node:crypto";
import { z } from "zod";
import { prisma } from "@/lib/db";
import { sendMail, emailLayout } from "@/lib/mailer";
import { SITE } from "@/lib/site";
import { ACTIVITY_TYPES, logActivity } from "@/lib/activity";

function appUrl(): string {
  return (process.env.APP_URL || "http://localhost:3000").replace(/\/$/, "");
}

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

  const user = await prisma.user.findUnique({ where: { email } });

  // Jawaban generik agar keberadaan akun tidak bocor; proses hanya bila ada akun.
  const generic = {
    ok: true,
    message:
      "Jika email tersebut terdaftar, tautan verifikasi baru telah dikirim. Cek kotak masuk (atau folder spam) kamu.",
  } as const;

  if (!user) return NextResponse.json(generic);

  if (user.is_verified) {
    return NextResponse.json({
      ok: true,
      message: "Email ini sudah terverifikasi. Silakan masuk.",
    });
  }

  const token = crypto.randomBytes(24).toString("hex");
  await prisma.user.update({
    where: { id: user.id },
    data: { verification_token: token },
  });

  const link = `${appUrl()}/verify-email?token=${token}&email=${encodeURIComponent(email)}`;
  const sent = await sendMail(
    email,
    `Tautan verifikasi baru — ${SITE.name} ${SITE.emoji}`,
    emailLayout(
      "Tautan Verifikasi Baru",
      `<p>Halo <strong>${user.nama}</strong>,</p>
       <p>Kamu meminta tautan verifikasi baru. Klik tombol di bawah untuk mengaktifkan akunmu:</p>
       <p style="margin:24px 0"><a href="${link}" style="background:#a81c0d;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold">Verifikasi Email</a></p>
       <p>Atau salin tautan ini ke browser:<br><a href="${link}">${link}</a></p>`
    )
  );

  await logActivity(
    user.id,
    ACTIVITY_TYPES.resend_verification,
    `Tautan verifikasi baru dikirim ke ${email}.`
  );

  return NextResponse.json({
    ...generic,
    ...(sent ? {} : { debug_link: link }),
  });
}
