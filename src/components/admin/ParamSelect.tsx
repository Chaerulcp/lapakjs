"use client";

import { Suspense } from "react";
import { usePathname, useRouter, useSearchParams } from "next/navigation";

export type ParamOption = { value: string; label: string };

function ParamSelectInner({
  param,
  value,
  options,
  placeholder,
  ariaLabel,
}: {
  param: string;
  value: string;
  options: ParamOption[];
  placeholder: string;
  ariaLabel: string;
}) {
  const router = useRouter();
  const pathname = usePathname();
  const params = useSearchParams();

  function handleChange(nextValue: string) {
    const next = new URLSearchParams(params.toString());
    if (nextValue) next.set(param, nextValue);
    else next.delete(param);
    next.delete("page");
    router.push(next.toString() ? `${pathname}?${next.toString()}` : pathname);
  }

  return (
    <select
      aria-label={ariaLabel}
      value={value}
      onChange={(e) => handleChange(e.target.value)}
      className="flex h-9 w-full min-w-36 rounded-lg border border-input bg-background px-3 text-sm shadow-xs transition-colors focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 focus-visible:outline-none"
    >
      <option value="">{placeholder}</option>
      {options.map((opt) => (
        <option key={opt.value} value={opt.value}>
          {opt.label}
        </option>
      ))}
    </select>
  );
}

/** Dropdown filter yang menyimpan pilihan di query param (mis. `status`, `role`). */
export function ParamSelect(props: {
  param: string;
  value: string;
  options: ParamOption[];
  placeholder: string;
  ariaLabel: string;
}) {
  return (
    <Suspense>
      <ParamSelectInner {...props} />
    </Suspense>
  );
}