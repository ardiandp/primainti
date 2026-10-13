# Environment Checklist — Primainti

Tanggal audit: 13 Oktober 2026
Fase: PRD Phase 0 (audit & dokumentasi, tanpa perubahan kode)
Metode: pemeriksaan langsung di mesin lokal, bukan asumsi.

## 1. Toolchain lokal (terverifikasi)

| Komponen | Versi terdeteksi | Catatan |
|---|---|---|
| OS | Windows (Laragon) | `C:\laragon\www\primainti` |
| PHP (CLI) | 8.3.28 (ZTS, Visual C++ 2019) | Memenuhi `composer.json` `php: ^8.3` |
| Composer | 2.10.2 | — |
| Node.js | 24.17.0 | Untuk Vite |
| npm | 11.13.0 | — |
| Git | 2.37.3.windows.1 | Repo baru diinisialisasi di branch `main` |

## 2. Stack aplikasi (dari `composer.lock` / `package.json`)

| Paket | Versi terkunci | Sumber |
|---|---|---|
| laravel/framework | v13.36.0 | `composer.lock` (77 paket produksi) |
| phpunit/phpunit | ^12.5.12 | `composer.json` require-dev |
| tailwindcss + @tailwindcss/vite | ^4.0.0 | `package.json` |
| vite | ^8.0.0 | `package.json` |
| laravel-vite-plugin | ^3.1 | `package.json` |

Belum terpasang: Livewire, Tiptap/CKEditor, Laravel Boost (Boost dijadwalkan dipasang saat Phase 1 dimulai).

## 3. Database MySQL

| Item | Status | Keterangan |
|---|---|---|
| MySQL server | ✅ Tersedia | Laragon `mysql-8.0.30-winx64` |
| Port 3306 | ✅ Terbuka | Tes TCP connect sukses |
| `DB_CONNECTION` di `.env` | ✅ `mysql` | Bukan sqlite |
| Koneksi aktual | ✅ Berhasil | `php artisan migrate:status` terhubung ke database default `laravel` |
| Migrasi | ⏳ Pending | 3 migration bawaan (`users`, `cache`, `jobs`) belum dijalankan |

Catatan: `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`, dan `CACHE_STORE=database`, tetapi tabel terkait belum ada karena migrasi belum dijalankan. Keputusan kapan migrasi dijalankan ada di Phase 1.

## 4. Konfigurasi `.env` saat ini

| Variabel | Nilai | Status |
|---|---|---|
| `APP_URL` | `http://localhost:8000` | Placeholder — perlu domain produksi |
| `APP_LOCALE` / `APP_FALLBACK_LOCALE` | `en` / `en` | Menunggu keputusan bahasa (lihat PROJECT_DECISIONS) |
| `DB_HOST` / `DB_PORT` / `DB_DATABASE` | tidak diset (default 127.0.0.1:3306 / `laravel`) | Cukup untuk lokal, perlu nilai eksplisit untuk produksi |
| `MAIL_MAILER` | `log` | SMTP produksi belum dikonfigurasi |

Keamanan: `.env` masuk `.gitignore` dan **tidak** ter-commit (diverifikasi via `git status`).

## 5. Checklist sebelum Phase 1

- [x] PHP, Composer, Node, npm, Git terpasang dan versinya dicatat
- [x] Versi Laravel/MySQL terverifikasi dari lock file dan pemeriksaan langsung
- [x] Koneksi MySQL teruji, bukan diasumsikan
- [x] `.env` tidak masuk repository
- [ ] Deployment target & hosting dikonfirmasi (pertanyaan terbuka — PROJECT_DECISIONS)
- [ ] Domain produksi dikonfirmasi
- [ ] SMTP produksi dipilih dan diuji
- [ ] Prosedur backup/restore disetujui
- [ ] Database produksi dibuat dan kredensial diisi di `.env` (Phase 1)

## 6. Hal yang sengaja TIDAK dilakukan di fase ini

- Menjalankan `migrate` (perubahan database hanya lewat Phase 1+ dan harus disetujui)
- Memasang paket baru (Livewire, editor, Boost)
- Membuat modul aplikasi atau frontend produksi
