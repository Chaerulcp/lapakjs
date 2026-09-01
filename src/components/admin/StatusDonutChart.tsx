"use client";

import { Cell, Pie, PieChart } from "recharts";
import {
  ChartContainer,
  ChartTooltip,
  ChartTooltipContent,
  type ChartConfig,
} from "@/components/ui/chart";

export type StatusSlice = {
  status: string;
  label: string;
  jumlah: number;
  color: string;
};

/** Donat distribusi pesanan per status, plus legenda ringkas di bawah. */
export function StatusDonutChart({ data }: { data: StatusSlice[] }) {
  const total = data.reduce((sum, d) => sum + d.jumlah, 0);

  const config = Object.fromEntries(
    data.map((d) => [d.status, { label: d.label, color: d.color }])
  ) as ChartConfig;

  return (
    <div className="flex flex-col gap-3">
      <div className="relative">
        <ChartContainer config={config} className="aspect-auto h-[210px] w-full">
          <PieChart>
            <ChartTooltip
              content={<ChartTooltipContent nameKey="label" hideIndicator />}
            />
            <Pie
              data={data}
              dataKey="jumlah"
              nameKey="label"
              innerRadius={58}
              outerRadius={82}
              strokeWidth={2}
            >
              {data.map((entry) => (
                <Cell key={entry.status} fill={entry.color} />
              ))}
            </Pie>
          </PieChart>
        </ChartContainer>
        <div className="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
          <span className="font-display text-2xl font-extrabold text-ink tabular-nums">
            {total}
          </span>
          <span className="text-[11px] font-medium text-muted-foreground">
            Total Pesanan
          </span>
        </div>
      </div>

      <ul className="grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs">
        {data.map((d) => (
          <li key={d.status} className="flex items-center justify-between gap-2">
            <span className="flex min-w-0 items-center gap-1.5 text-muted-foreground">
              <span
                className="size-2 shrink-0 rounded-[2px]"
                style={{ backgroundColor: d.color }}
              />
              <span className="truncate">{d.label}</span>
            </span>
            <span className="font-mono font-semibold text-foreground tabular-nums">
              {d.jumlah}
            </span>
          </li>
        ))}
      </ul>
    </div>
  );
}