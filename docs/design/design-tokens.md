# Design Tokens — Primainti

Fase: PRD Phase 0
Tanggal: 13 Oktober 2026
Status: **PRELIMINARY / SEMENTARA** — mockup belum tersedia di repository.

> Nilai warna-hex di bawah adalah **kandidat sementara** yang diturunkan dari deskripsi PRD
> (navy, putih, oranye). Wajib dikalibrasi ulang dari mockup resmi sebelum Phase 8
> (Frontend lengkap). Status setiap blok: `Preliminary` → `Confirmed`.

## 1. Warna (Colors)

### Brand

| Token | Nilai kandidat | Pemakaian | Status |
|---|---|---|---|
| `color-brand-navy` | `#0F2A54` | Header, footer, teks judul, tombol utama | Preliminary |
| `color-brand-navy-dark` | `#0A1D3A` | Hover state, overlay | Preliminary |
| `color-brand-navy-light` | `#1E4278` | Aksen sekunder, link aktif | Preliminary |
| `color-brand-orange` | `#F97316` | Aksen CTA, highlight, ikon | Preliminary |
| `color-brand-orange-dark` | `#EA580C` | Hover aksen | Preliminary |
| `color-white` | `#FFFFFF` | Latar utama, teks di atas navy | Preliminary |

### Netral

| Token | Nilai kandidat | Pemakaian |
|---|---|---|
| `color-gray-50` | `#F8FAFC` | Latar section alt |
| `color-gray-100` | `#F1F5F9` | Border ringan, chip |
| `color-gray-300` | `#CBD5E1` | Input border |
| `color-gray-500` | `#64748B` | Teks sekunder |
| `color-gray-700` | `#334155` | Teks body |
| `color-gray-900` | `#0F172A` | Teks utama |

### Semantik

| Token | Nilai kandidat | Pemakaian |
|---|---|---|
| `color-success` | `#16A34A` | Status berhasil, notifikasi OK |
| `color-warning` | `#EAB308` | Peringatan |
| `color-danger` | `#DC2626` | Error form, hapus |
| `color-info` | `#2563EB` | Informasi |

## 2. Tipografi (Typography)

| Token | Nilai kandidat | Catatan | Status |
|---|---|---|---|
| `font-sans` | `"Plus Jakarta Sans", "Inter", system-ui, sans-serif` | Kandidat; final menunggu mockup/mockup font resmi | Preliminary |
| `font-heading` | Sama dengan `font-sans`, bobot 700 | — | Preliminary |

Skala ukuran (rem/px):

| Token | Nilai | Untuk |
|---|---|---|
| `text-xs` | 12px / 0.75rem | Caption, label kecil |
| `text-sm` | 14px / 0.875rem | Meta, footer |
| `text-base` | 16px / 1rem | Body |
| `text-lg` | 18px / 1.125rem | Lead paragraph |
| `text-xl` | 20px / 1.25rem | Sub-heading |
| `text-2xl` | 24px / 1.5rem | Heading h4 |
| `text-3xl` | 30px / 1.875rem | Heading h3 |
| `text-4xl` | 36px / 2.25rem | Heading h2 |
| `text-5xl` | 48px / 3rem | Heading h1 / hero |

| Token | Nilai | Untuk |
|---|---|---|
| `leading-tight` | 1.2 | Judul |
| `leading-normal` | 1.5 | Body |
| `leading-relaxed` | 1.65 | Paragraf panjang |
| `weight-regular` | 400 | Body |
| `weight-medium` | 500 | Label, nav |
| `weight-bold` | 700 | Judul, tombol |

## 3. Spacing (Spacing)

Basis 4px, memakai skala Tailwind:

| Token | Nilai | Umum dipakai |
|---|---|---|
| `space-1` … `space-2` | 4px, 8px | Ikon, gap kecil |
| `space-3` / `space-4` | 12px, 16px | Padding tombol/field |
| `space-6` / `space-8` | 24px, 32px | Card internal, gap antar blok |
| `space-12` / `space-16` | 48px, 64px | Jarak antar section |
| `section-padding-y` | 64px–96px (mobile: 48px) | Vertikal tiap section |

## 4. Breakpoints

Memakai standar Tailwind (bukan penemuan baru):

| Token | Min-width | Target |
|---|---|---|
| `sm` | 640px | Large phone |
| `md` | 768px | Tablet portrait |
| `lg` | 1024px | Tablet landscape / small laptop |
| `xl` | 1280px | Desktop |
| `2xl` | 1536px | Large desktop |

## 5. Button

| Varian | Latar | Teks | Radius | Padding (x/y) | Status |
|---|---|---|---|---|---|
| Primary | `brand-orange` (kandidat) | putih | 8px | 24px / 12px | Preliminary |
| Primary publik | `#C2410C` (orange-700) | putih | 8px | 24px / 12px | Confirmed Phase 10 — oranye kandidat gagal kontras AA untuk teks, digelapkan di CSS publik |
| Secondary | `brand-navy` | putih | 8px | 24px / 12px | Preliminary |
| Outline | transparan, border navy | navy | 8px | 24px / 12px | Preliminary |
| Ghost | transparan | navy/oranye | 8px | 16px / 8px | Preliminary |

State: `default`, `hover` (gelap 1 langkah), `active` (skala 0.98), `focus-visible` (ring 2px oranye offset 2px), `disabled` (opacity 50%, nonaktif).
Ukuran: `sm` (pad 16/8, teks 14px), `md` (default), `lg` (pad 32/16, teks 18px).

## 6. Card

| Token | Nilai kandidat | Status |
|---|---|---|
| `card-bg` | `#FFFFFF` | Preliminary |
| `card-border` | 1px `color-gray-100` | Preliminary |
| `card-radius` | 12px | Preliminary |
| `card-shadow` | `0 1px 3px rgba(15, 23, 42, 0.08)` | Preliminary |
| `card-padding` | 24px | Preliminary |
| `card-shadow-hover` | `0 8px 24px rgba(15, 23, 42, 0.12)` + translate Y -2px | Preliminary |

## 7. Implementasi (untuk Phase 8)

- Semua token didefinisikan di `resources/css/app.css` memakai CSS custom properties (`--color-brand-navy`, dll.) atau theme Tailwind 4 — detail teknis disusun saat Phase 8.
- **Jangan** mengganti nilai `Preliminary` menjadi final tanpa persetujuan dari pemilik mockup.
- Ikon: pustaka ikon (mis. Heroicons/Lucide) dipilih saat Phase 8 — memakai bobot outline sebagai default.

## 8. Log perubahan

| Tanggal | Perubahan | Oleh |
|---|---|---|
| 2026-10-13 | Dokumen dibuat — token preliminary dari deskripsi PRD (mockup belum tersedia) | Audit agent |
