"use client";

import { Area, AreaChart, CartesianGrid, XAxis, YAxis } from "recharts";
import {
  ChartContainer,
  ChartTooltip,
  ChartTooltipContent,
  type ChartConfig,
} from "@/components/ui/chart";

export type SalesPoint = {
  /** Label pendek sumbu X, mis. "12 Jun". */
  label: string;
  /** Total pendapatan hari itu (rupiah). */
  total: number;
  /** Jumlah pesanan hari itu. */
  pesanan: number;
};

const chartConfig = {
  total: { label: "Pendapatan", color: "var(--chart-1)" },
  pesanan: { label: "Pesanan", color: "var(--chart-3)" },
} satisfies ChartConfig;

/** Sumbu Y ringkas: 1500000 → "1,5 jt", 250000 → "250 rb". */
function compactRupiah(value: number): string {
  if (value >= 1_000_000) {
    const jt = value / 1_000_000;
    return `${jt.toLocaleString("id-ID", { maximumFractionDigits: 1 })} jt`;
  }
  if (value >= 1_000) return `${Math.round(value / 1_000)} rb`;
  return String(value);
}

/** Grafik pendapatan harian (14 hari terakhir, pesanan non-dibatalkan). */
export function SalesChart({ data }: { data: SalesPoint[] }) {
  return (
    <ChartContainer config={chartConfig} className="aspect-auto h-[280px] w-full">
      <AreaChart data={data} margin={{ left: 4, right: 12, top: 8 }}>
        <defs>
          <linearGradient id="fillTotal" x1="0" y1="0" x2="0" y2="1">
            <stop offset="5%" stopColor="var(--color-total)" stopOpacity={0.35} />
            <stop offset="95%" stopColor="var(--color-total)" stopOpacity={0.02} />
          </linearGradient>
        </defs>
        <CartesianGrid vertical={false} strokeDasharray="3 3" />
        <XAxis
          dataKey="label"
          tickLine={false}
          axisLine={false}
          tickMargin={8}
          minTickGap={24}
        />
        <YAxis
          tickLine={false}
          axisLine={false}
          width={44}
          tickFormatter={(v: number) => compactRupiah(v)}
        />
        <ChartTooltip
          cursor={false}
          content={<ChartTooltipContent indicator="dot" />}
        />
        <Area
          type="monotone"
          dataKey="total"
          stroke="var(--color-total)"
          strokeWidth={2.5}
          fill="url(#fillTotal)"
        />
      </AreaChart>
    </ChartContainer>
  );
}