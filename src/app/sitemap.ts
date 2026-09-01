import type { MetadataRoute } from "next";
import { prisma } from "@/lib/db";

const BASE_URL = (process.env.APP_URL || "http://localhost:3000").replace(/\/$/, "");

export const dynamic = "force-dynamic";

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const staticRoutes: MetadataRoute.Sitemap = [
    { url: `${BASE_URL}/`, changeFrequency: "weekly", priority: 1 },
    { url: `${BASE_URL}/produk`, changeFrequency: "weekly", priority: 0.9 },
    { url: `${BASE_URL}/konten`, changeFrequency: "weekly", priority: 0.6 },
  ];

  try {
    const [products, contents] = await Promise.all([
      prisma.product.findMany({
        select: { id: true, created_at: true, updated_at: true },
      }),
      prisma.content.findMany({
        select: { id: true, tanggal: true, updated_at: true },
      }),
    ]);

    return [
      ...staticRoutes,
      ...products.map((p) => ({
        url: `${BASE_URL}/produk/${p.id}`,
        lastModified: p.updated_at ?? p.created_at ?? new Date(),
        changeFrequency: "weekly" as const,
        priority: 0.8,
      })),
      ...contents.map((c) => ({
        url: `${BASE_URL}/konten/${c.id}`,
        lastModified: c.updated_at ?? c.tanggal,
        changeFrequency: "monthly" as const,
        priority: 0.5,
      })),
    ];
  } catch {
    // Database belum tersedia — kembalikan rute statis saja.
    return staticRoutes;
  }
}