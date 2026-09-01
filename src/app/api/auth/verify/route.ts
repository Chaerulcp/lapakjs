import { NextRequest, NextResponse } from "next/server";
import { prisma } from "@/lib/db";
import { ACTIVITY_TYPES, logActivity } from "@/lib/activity";

async function verify(req: NextRequest) {
  const token = req.nextUrl.searchParams.get("token");
  const email = req.nextUrl.searchParams.get("email");

  if (!token) {
    return NextResponse.json(
      { error: "Tautan verifikasi tidak valid." },
      { status: 400 }
    );
  }

  const user = await prisma.user.findFirst({ where: { verification_token: token } });
  if (!user || (email && user.email !== email.toLowerCase().trim())) {
    return NextResponse.json(
      { error: "Tautan verifikasi tidak valid atau sudah digunakan." },
      { status: 400 }
    );
  }

  if (user.is_verified) {
    // Idempoten: bersihkan sisa token lama bila ada.
    if (user.verification_token) {
      await prisma.user.update({
        where: { id: user.id },
        data: { verification_token: null },
      });
    }
    return NextResponse.json({
      ok: true,
      message: "Email sudah terverifikasi. Silakan masuk.",
    });
  }

  await prisma.user.update({
    where: { id: user.id },
    data: { is_verified: true, verification_token: null },
  });

  await logActivity(
    user.id,
    ACTIVITY_TYPES.verify_email,
    `Email ${user.email} berhasil diverifikasi.`
  );

  return NextResponse.json({
    ok: true,
    message: "Email berhasil diverifikasi! Silakan masuk untuk mulai belanja.",
  });
}

export async function GET(req: NextRequest) {
  return verify(req);
}

export async function POST(req: NextRequest) {
  return verify(req);
}
