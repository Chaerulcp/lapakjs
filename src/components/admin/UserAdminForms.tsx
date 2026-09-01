"use client";

import { useEffect, useState, useTransition } from "react";
import { toast } from "sonner";
import {
  updateUserRoleAction,
  updateUserStatusAction,
  type ActionResult,
} from "@/app/admin/actions";

function useActionToast() {
  const [result, setResult] = useState<ActionResult | null>(null);
  const [pending, startTransition] = useTransition();

  useEffect(() => {
    if (result?.message) {
      if (result.ok) toast.success(result.message);
      else toast.error(result.message);
    }
  }, [result]);

  return { pending, startTransition, setResult };
}

const ROLE_OPTIONS = [
  { value: "admin", label: "Admin" },
  { value: "reseller", label: "Reseller" },
  { value: "pelanggan", label: "Pelanggan" },
];

const STATUS_OPTIONS = [
  { value: "active", label: "Aktif" },
  { value: "inactive", label: "Nonaktif" },
];

/** Dropdown pengubah role pengguna (admin/reseller/pelanggan). */
export function UserRoleForm({
  userId,
  role,
  disabled = false,
}: {
  userId: number;
  role: string;
  /** True untuk baris akun admin yang sedang login (tidak boleh diubah). */
  disabled?: boolean;
}) {
  const [value, setValue] = useState(role);
  const { pending, startTransition, setResult } = useActionToast();

  useEffect(() => {
    setValue(role);
  }, [role]);

  function handleChange(next: string) {
    setValue(next);
    const fd = new FormData();
    fd.set("id", String(userId));
    fd.set("role", next);
    startTransition(async () => {
      setResult(await updateUserRoleAction(fd));
    });
  }

  return (
    <select
      aria-label={`Ubah role pengguna #${userId}`}
      title={disabled ? "Tidak dapat mengubah akun sendiri" : undefined}
      value={value}
      disabled={pending || disabled}
      onChange={(e) => handleChange(e.target.value)}
      className="flex h-8 w-32 rounded-lg border border-input bg-background px-2 text-xs font-medium shadow-xs transition-colors focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-60"
    >
      {ROLE_OPTIONS.map((opt) => (
        <option key={opt.value} value={opt.value}>
          {opt.label}
        </option>
      ))}
    </select>
  );
}

/** Dropdown pengubah status akun (aktif/nonaktif). */
export function UserStatusForm({
  userId,
  status,
  disabled = false,
}: {
  userId: number;
  status: string;
  disabled?: boolean;
}) {
  const [value, setValue] = useState(status);
  const { pending, startTransition, setResult } = useActionToast();

  useEffect(() => {
    setValue(status);
  }, [status]);

  function handleChange(next: string) {
    setValue(next);
    const fd = new FormData();
    fd.set("id", String(userId));
    fd.set("status", next);
    startTransition(async () => {
      setResult(await updateUserStatusAction(fd));
    });
  }

  return (
    <select
      aria-label={`Ubah status pengguna #${userId}`}
      title={disabled ? "Tidak dapat mengubah akun sendiri" : undefined}
      value={value}
      disabled={pending || disabled}
      onChange={(e) => handleChange(e.target.value)}
      className="flex h-8 w-32 rounded-lg border border-input bg-background px-2 text-xs font-medium shadow-xs transition-colors focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-60"
    >
      {STATUS_OPTIONS.map((opt) => (
        <option key={opt.value} value={opt.value}>
          {opt.label}
        </option>
      ))}
    </select>
  );
}