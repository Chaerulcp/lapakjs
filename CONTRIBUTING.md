# Kontribusi ke LapakJS

Terima kasih sudah tertarik untuk berkontribusi. Panduan singkat ini menjelaskan cara ikut mengembangkan LapakJS.

## Persiapan lingkungan

1. Fork repository dan clone ke mesin lokal.
2. Ikuti langkah instalasi di [README](README.md#instalasi).
3. Pastikan `npm run lint` dan `npm run build` lolos tanpa error sebelum membuat PR.

## Alur kontribusi

1. Buat branch baru dari `main` — contoh: `feat/kupon-diskon` atau `fix/checkout-error`.
2. Commit dengan pesan yang jelas dalam bahasa Indonesia atau Inggris.
3. Buka Pull Request ke branch `main`.
4. Jelaskan perubahan, alasan, dan cara mengujinya di deskripsi PR.

## Standar kode

- **TypeScript** — hindari `any` kecuali benar-benar perlu.
- **ESLint** — jalankan `npm run lint` dan pastikan 0 error.
- **Formatting** — ikuti gaya yang sudah ada (2 spasi, kutip ganda untuk JSX).
- **Penamaan file** — `kebab-case` untuk route, `PascalCase` untuk komponen React.

## Melaporkan bug

Buka [Issue](../../issues) baru dengan informasi:
- Langkah reproduksi.
- Perilaku yang diharapkan vs yang terjadi.
- Versi Node.js, OS, dan browser (jika relevan).

## Mengusulkan fitur

Buka Issue atau [Discussion](../../discussions) baru. Jelaskan masalah yang ingin diselesaikan, bukan hanya solusinya. Diskusi terlebih dahulu membantu menghindari pekerjaan yang sia-sia.

## Lisensi

Dengan berkontribusi, kamu menyetujui bahwa kontribusimu dilisensikan di bawah [MIT License](LICENSE) yang sama dengan proyek ini.
