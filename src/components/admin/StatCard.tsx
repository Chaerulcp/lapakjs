import type { LucideIcon } from "lucide-react";
import { cn } from "@/lib/utils";

/** Kartu metrik untuk dashboard admin. */
export function StatCard({
  label,
  value,
  hint,
  icon: Icon,
  tone = "chili",
}: {
  label: string;
  value: string;
  hint?: string;
  icon: LucideIcon;
  tone?: "chili" | "ember" | "mango" | "leaf";
}) {
  const toneClass: Record<string, string> = {
    chili: "bg-chili-600/10 text-chili-600",
    ember: "bg-ember-600/10 text-ember-600",
    mango: "bg-mango-400/25 text-amber-700",
    leaf: "bg-leaf-500/15 text-leaf-600",
  };

  return (
    <div className="rounded-2xl border border-border bg-card p-5 shadow-sm">
      <div className="flex items-start justify-between gap-3">
        <div className="min-w-0">
          <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
            {label}
          </p>
          <p className="mt-2 truncate font-display text-2xl font-extrabold text-ink tabular-nums">
            {value}
          </p>
          {hint ? <p className="mt-1 text-xs text-muted-foreground">{hint}</p> : null}
        </div>
        <span
          className={cn(
            "flex size-10 shrink-0 items-center justify-center rounded-xl",
            toneClass[tone]
          )}
        >
          <Icon className="size-5" />
        </span>
      </div>
    </div>
  );
}