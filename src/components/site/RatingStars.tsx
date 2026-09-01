import { Star } from "lucide-react";
import { cn } from "@/lib/utils";

/** Deretan bintang rating 1–5. */
export default function RatingStars({
  rating,
  className,
  size = "size-4",
}: {
  rating: number;
  className?: string;
  size?: string;
}) {
  const nilai = Math.max(0, Math.min(5, Math.round(rating)));
  return (
    <span className={cn("inline-flex items-center gap-0.5", className)} aria-label={`Rating ${nilai} dari 5`}>
      {[1, 2, 3, 4, 5].map((i) => (
        <Star
          key={i}
          className={cn(size, i <= nilai ? "fill-mango-500 text-mango-500" : "fill-border text-border")}
        />
      ))}
    </span>
  );
}
