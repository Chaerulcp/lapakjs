import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationLink,
  PaginationNext,
  PaginationPrevious,
} from "@/components/ui/pagination";

/** Susun nomor halaman yang ditampilkan: 1 … 4 5 [6] 7 8 … 20. */
function pageWindow(current: number, total: number): (number | "…")[] {
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
  const pages = new Set<number>([1, total, current - 1, current, current + 1]);
  const sorted = [...pages].filter((p) => p >= 1 && p <= total).sort((a, b) => a - b);
  const out: (number | "…")[] = [];
  let prev = 0;
  for (const p of sorted) {
    if (p - prev > 1) out.push("…");
    out.push(p);
    prev = p;
  }
  return out;
}

/** Navigasi halaman tabel admin; mempertahankan seluruh query param lain. */
export function PaginationBar({
  page,
  totalPages,
  buildHref,
}: {
  page: number;
  totalPages: number;
  /** Kembalikan href untuk nomor halaman tertentu (query param sudah digabung). */
  buildHref: (page: number) => string;
}) {
  if (totalPages <= 1) return null;

  return (
    <Pagination className="mx-0 w-auto justify-start">
      <PaginationContent>
        <PaginationItem>
          {page > 1 ? (
            <PaginationPrevious href={buildHref(page - 1)} text="Sebelumnya" />
          ) : (
            <span aria-disabled className="pointer-events-none opacity-40">
              <PaginationPrevious text="Sebelumnya" />
            </span>
          )}
        </PaginationItem>

        {pageWindow(page, totalPages).map((p, i) =>
          p === "…" ? (
            <PaginationItem key={`e-${i}`}>
              <PaginationEllipsis />
            </PaginationItem>
          ) : (
            <PaginationItem key={p}>
              <PaginationLink href={buildHref(p)} isActive={p === page}>
                {p}
              </PaginationLink>
            </PaginationItem>
          )
        )}

        <PaginationItem>
          {page < totalPages ? (
            <PaginationNext href={buildHref(page + 1)} text="Berikutnya" />
          ) : (
            <span aria-disabled className="pointer-events-none opacity-40">
              <PaginationNext text="Berikutnya" />
            </span>
          )}
        </PaginationItem>
      </PaginationContent>
    </Pagination>
  );
}

/** Helper: gabungkan query param aktif dengan nomor halaman baru. */
export function makePageHref(
  pathname: string,
  params: URLSearchParams,
  page: number
): string {
  const next = new URLSearchParams(params.toString());
  if (page > 1) next.set("page", String(page));
  else next.delete("page");
  const qs = next.toString();
  return qs ? `${pathname}?${qs}` : pathname;
}