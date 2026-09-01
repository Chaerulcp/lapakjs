import nodemailer from "nodemailer";
import { SITE } from "@/lib/site";

const { SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_SECURE, MAIL_FROM } = process.env;

function getTransporter() {
  if (!SMTP_HOST || !SMTP_USER) return null;
  return nodemailer.createTransport({
    host: SMTP_HOST,
    port: Number(SMTP_PORT || 587),
    secure: SMTP_SECURE === "true",
    auth: { user: SMTP_USER, pass: SMTP_PASS || "" },
  });
}

export async function sendMail(to: string, subject: string, html: string): Promise<boolean> {
  const transporter = getTransporter();
  if (!transporter) {
    // Mode pengembangan tanpa SMTP: tulis ke console agar alur tetap bisa diuji.
    console.log(`[mailer] SMTP belum dikonfigurasi. Email ke ${to} — Subjek: ${subject}`);
    console.log(html.replace(/<[^>]+>/g, " ").replace(/\s+/g, " ").slice(0, 500));
    return false;
  }
  try {
    await transporter.sendMail({
      from: MAIL_FROM || `${SITE.name} <${SMTP_USER}>`,
      to,
      subject,
      html,
    });
    return true;
  } catch (err) {
    console.error("[mailer] gagal mengirim:", err);
    return false;
  }
}

export function emailLayout(title: string, bodyHtml: string): string {
  return `<!doctype html><html><body style="margin:0;background:#faf5ef;font-family:Arial,sans-serif">
  <div style="max-width:560px;margin:0 auto;padding:32px 16px">
    <div style="background:#a81c0d;border-radius:12px 12px 0 0;padding:20px 28px">
      <span style="color:#fff;font-size:20px;font-weight:bold">${SITE.emoji} ${SITE.name}</span>
    </div>
    <div style="background:#fff;border:1px solid #eadfd2;border-top:0;border-radius:0 0 12px 12px;padding:28px">
      <h2 style="margin:0 0 16px;color:#1c1310">${title}</h2>
      ${bodyHtml}
    </div>
    <p style="color:#8a7a6a;font-size:12px;text-align:center;margin-top:16px">© ${SITE.name} — ${SITE.tagline}</p>
  </div></body></html>`;
}
