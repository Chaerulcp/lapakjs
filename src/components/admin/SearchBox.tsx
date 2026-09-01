"use client";

import { Suspense, useEffect, useState } from "react";
import { usePathname, useRouter, useSearchParams } from "next/navigation";
import { Search } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";

function SearchBoxInner({ placeholder }: { placeholder?: string }) {
  const router = useRouter();
  const pathname = usePathname();
  const params = useSearchParams();
  const [value, setValue] = useState(params.get("q") ?? "");

  // Sinkronkan kembali saat navigasi (mis. pindah halaman/filter lain).
  useEffect(() => {
    setValue(params.get("q") ?? "");
  }, [params]);

  function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const next = new URLSearchParams(params.toString());
    if (value.trim()) next.set("q", value.trim());
    else next.delete("q");
    next.delete("page");
    router.push(next.toString() ? `${pathname}?${next.toString()}` : pathname);
  }

  return (
    <form onSubmit={handleSubmit} className="flex w-full max-w-xs items-center gap-2">
      <div className="relative flex-1">
        <Search className="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
        <Input
          name="q"
          value={value}
          onChange={(e) => setValue(e.target.value)}
          placeholder={placeholder ?? "Cari…"}
          className="pl-8"
        />
      </div>
      <Button type="submit" variant="outline" size="sm" className="h-9">
        Cari
      </Button>
    </form>
  );
}

/** Kotak pencarian yang menyimpan kata kunci di query param `q`. */
export function SearchBox(props: { placeholder?: string }) {
  return (
    <Suspense>
      <SearchBoxInner {...props} />
    </Suspense>
  );
}