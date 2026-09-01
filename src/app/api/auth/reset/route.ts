import { NextResponse } from "next/server";
import { z } from "zod";
import { prisma } from "@/lib/db";
import { hashPassword } from "@/lib/password";
import { ACTIVITY_TYPES, logActivity } from "@/lib/activity";

const bodySchema = z.object({
  token: z.string().trim().min(16, "Token tidak valid."),
  password: z.string().min(8, "Password minimal 8 karakter.").max(100),
});

export async function POST(req: Request) {
  let body: unknown;
  try {
    body = await req.json();
  } catch {
    return NextResponse.json({ error: "Body request tidak valid." }, { status: 400 });
  }

  const parsed = bodySchema.safeParse(body);
  if (!parsed.success) {
    return NextResponse.json(
      { error: z.flattenError(parsed.error).fieldErrors.password?.[0] ?? "Data tidak valid." },
      { status: 400 }
    );
  }
  const { token, password } = parsed.data;

  const reset = await prisma.passwordReset.findFirst({
    where: { token, expires_at: { gt: new Date() } },
    orderBy: { created_at: "desc" },
  });

  if (!reset) {
    return NextResponse.json(
      {
        error:
          "Tautan pengaturan ulang tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.",
      },
      { status: 400 }
    );
  }

  const user = await prisma.user.findUnique({ where: { id: reset.user_id } });
  if (!user) {
    return NextResponse.json({ error: "Akun tidak ditemukan." }, { status: 404 });
  }

  await prisma.$transaction([
    prisma.user.update({
      where: { id: user.id },
      data: { password: await hashPassword(password) },
    }),
    prisma.passwordReset.deleteMany({ where: { user_id: user.id } }),
  ]);

  await logActivity(
    user.id,
    ACTIVITY_TYPES.reset_password,
    `Password akun ${user.email} berhasil diatur ulang.`
  );

  return NextResponse.json({
    ok: true,
    message: "Password berhasil diatur ulang. Silakan masuk dengan password baru kamu.",
  });
}
