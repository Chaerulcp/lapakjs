import { NextResponse } from "next/server";
import { auth } from "@/auth";
import { prisma } from "@/lib/db";
import { ACTIVITY_TYPES, logActivity } from "@/lib/activity";
import { saveUploadedFile, UploadError } from "@/lib/uploads";

/** GET /api/payments — daftar pembayaran milik pengguna yang sedang masuk. */
export async function GET() {
  const session = await auth();
  if (!session?.user?.id) {
    return NextResponse.json(
      { error: "Silakan masuk terlebih dahulu." },
      { status: 401 }
    );
  }

  const payments = await prisma.payment.findMany({
    where: { order: { user_id: Number(session.user.id) } },
    include: {
      order: { select: { id: true, total: true, status: true, tanggal: true } },
    },
    orderBy: { tanggal: "desc" },
  });

  return NextResponse.json({ ok: true, payments });
}

/**
 * POST /api/payments — unggah bukti transfer untuk sebuah pesanan.
 * FormData: order_id, metode?, file (gambar bukti transfer).
 */
export async function POST(req: Request) {
  const session = await auth();
  if (!session?.user?.id) {
    return NextResponse.json(
      { error: "Silakan masuk terlebih dahulu." },
      { status: 401 }
    );
  }

  let formData: FormData;
  try {
    formData = await req.formData();
  } catch {
    return NextResponse.json({ error: "Body request tidak valid." }, { status: 400 });
  }

  const orderId = Number(formData.get("order_id"));
  if (!Number.isInteger(orderId) || orderId <= 0) {
    return NextResponse.json({ error: "order_id tidak valid." }, { status: 400 });
  }

  const metodeRaw = formData.get("metode");
  const metode =
    typeof metodeRaw === "string" && metodeRaw.trim() !== ""
      ? metodeRaw.trim().slice(0, 50)
      : "transfer";

  const file = formData.get("file");
  if (!(file instanceof File)) {
    return NextResponse.json(
      { error: "Bukti transfer (file gambar) wajib diunggah." },
      { status: 400 }
    );
  }

  const userId = Number(session.user.id);
  const order = await prisma.order.findFirst({
    where: { id: orderId, user_id: userId },
  });
  if (!order) {
    return NextResponse.json(
      { error: "Pesanan tidak ditemukan." },
      { status: 404 }
    );
  }
  if (order.status === "dibatalkan") {
    return NextResponse.json(
      { error: "Pesanan ini sudah dibatalkan." },
      { status: 400 }
    );
  }

  let bukti: string;
  try {
    bukti = await saveUploadedFile(file, "payments", `payment_proof_${order.id}`);
  } catch (err) {
    if (err instanceof UploadError) {
      return NextResponse.json({ error: err.message }, { status: 400 });
    }
    console.error("[payments] gagal menyimpan file:", err);
    return NextResponse.json(
      { error: "Gagal menyimpan file. Coba lagi sebentar lagi." },
      { status: 500 }
    );
  }

  const payment = await prisma.payment.create({
    data: {
      order_id: order.id,
      metode,
      status: "menunggu",
      bukti_transfer: bukti,
    },
  });

  await logActivity(
    userId,
    ACTIVITY_TYPES.payment_uploaded,
    `Bukti transfer pesanan #${order.id} diunggah.`
  );

  return NextResponse.json(
    { ok: true, payment_id: payment.id, bukti_transfer: bukti },
    { status: 201 }
  );
}
