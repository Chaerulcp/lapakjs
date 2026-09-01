import { NextResponse } from "next/server";
import { cookies } from "next/headers";
import { z } from "zod";
import { auth } from "@/auth";
import { prisma } from "@/lib/db";
import { ACTIVITY_TYPES, logActivity } from "@/lib/activity";

const orderSchema = z.object({
  items: z
    .array(
      z.object({
        product_id: z.coerce.number().int().positive(),
        jumlah: z.coerce.number().int().positive(),
      })
    )
    .min(1, "Keranjang masih kosong."),
  alamat: z.string().trim().min(5, "Alamat pengiriman minimal 5 karakter.").max(500),
  metode_pembayaran: z.string().trim().min(1).max(50).default("transfer"),
});

/** GET /api/orders — daftar pesanan milik pengguna yang sedang masuk. */
export async function GET() {
  const session = await auth();
  if (!session?.user?.id) {
    return NextResponse.json(
      { error: "Silakan masuk terlebih dahulu." },
      { status: 401 }
    );
  }

  const orders = await prisma.order.findMany({
    where: { user_id: Number(session.user.id) },
    include: {
      items: {
        include: {
          product: { select: { id: true, nama: true, foto: true, kategori: true } },
        },
      },
      payments: { select: { id: true, status: true, metode: true, tanggal: true } },
    },
    orderBy: { tanggal: "desc" },
  });

  return NextResponse.json({ ok: true, orders });
}

/**
 * POST /api/orders — buat pesanan baru.
 * Body: { items: [{product_id, jumlah}], alamat, metode_pembayaran? }
 * Harga dihitung ulang di server (reseller otomatis dapat harga reseller),
 * stok dicek dan dikurangi secara atomik dalam satu transaksi.
 */
export async function POST(req: Request) {
  const session = await auth();
  if (!session?.user?.id) {
    return NextResponse.json(
      { error: "Silakan masuk terlebih dahulu." },
      { status: 401 }
    );
  }

  let body: unknown;
  try {
    body = await req.json();
  } catch {
    return NextResponse.json({ error: "Body request tidak valid." }, { status: 400 });
  }

  const parsed = orderSchema.safeParse(body);
  if (!parsed.success) {
    const flat = z.flattenError(parsed.error).fieldErrors;
    const first = Object.values(flat).find((v) => v && v.length > 0)?.[0];
    return NextResponse.json(
      { error: first ?? "Data pesanan tidak valid." },
      { status: 400 }
    );
  }

  const { alamat, metode_pembayaran } = parsed.data;

  // Gabungkan item duplikat per produk.
  const qtyByProduct = new Map<number, number>();
  for (const item of parsed.data.items) {
    qtyByProduct.set(
      item.product_id,
      (qtyByProduct.get(item.product_id) ?? 0) + item.jumlah
    );
  }

  const products = await prisma.product.findMany({
    where: { id: { in: [...qtyByProduct.keys()] } },
  });
  if (products.length !== qtyByProduct.size) {
    return NextResponse.json(
      { error: "Ada produk yang tidak ditemukan di katalog." },
      { status: 400 }
    );
  }

  const useResellerPrice = session.user.role === "reseller";
  const priceOf = (p: (typeof products)[number]) =>
    Number(useResellerPrice ? p.harga_reseller : p.harga);

  for (const product of products) {
    const qty = qtyByProduct.get(product.id) ?? 0;
    if (product.stok < qty) {
      return NextResponse.json(
        {
          error: `Stok ${product.nama} tidak mencukupi (tersisa ${product.stok}).`,
        },
        { status: 409 }
      );
    }
  }

  const total = products.reduce(
    (sum, p) => sum + priceOf(p) * (qtyByProduct.get(p.id) ?? 0),
    0
  );
  const userId = Number(session.user.id);

  try {
    const order = await prisma.$transaction(async (tx) => {
      // Kurangi stok secara atomik; gagal bila stok berubah di tengah jalan.
      for (const product of products) {
        const qty = qtyByProduct.get(product.id) ?? 0;
        const updated = await tx.product.updateMany({
          where: { id: product.id, stok: { gte: qty } },
          data: { stok: { decrement: qty } },
        });
        if (updated.count === 0) {
          throw new Error(`STOK_HABIS:${product.nama}`);
        }
      }

      return tx.order.create({
        data: {
          user_id: userId,
          total,
          status: "menunggu",
          alamat,
          metode_pembayaran,
          items: {
            create: products.map((p) => ({
              product_id: p.id,
              jumlah: qtyByProduct.get(p.id) ?? 0,
              harga: priceOf(p),
            })),
          },
        },
      });
    });

    // Keranjang cookie sudah terwakili pesanan — bersihkan.
    try {
      (await cookies()).delete("cart");
    } catch {
      // bukan kondisi fatal
    }

    await logActivity(
      userId,
      ACTIVITY_TYPES.order_created,
      `Pesanan #${order.id} dibuat dengan total Rp${total}.`
    );

    return NextResponse.json({ ok: true, order_id: order.id }, { status: 201 });
  } catch (err) {
    if (err instanceof Error && err.message.startsWith("STOK_HABIS:")) {
      return NextResponse.json(
        { error: `${err.message.slice("STOK_HABIS:".length)}: stok tidak mencukupi.` },
        { status: 409 }
      );
    }
    console.error("[orders] gagal membuat pesanan:", err);
    return NextResponse.json(
      { error: "Gagal membuat pesanan. Coba lagi sebentar lagi." },
      { status: 500 }
    );
  }
}
