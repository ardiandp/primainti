# PROJECT_DECISIONS — Primainti

Fase: PRD Phase 0
Tanggal: 13 Oktober 2026
Status dokumen: **Draf menunggu tinjauan pemilik proyek**

Legenda status: `Terkunci` = disetujui/tidak berubah tanpa proses; `Usulan` = rekomendasi tim teknis, menunggu persetujuan; `Terbuka` = belum ada keputusan.

## A. Keputusan yang sudah terkunci (hasil audit, fakta terverifikasi)

| ID | Keputusan | Nilai | Status | Bukti |
|---|---|---|---|---|
| D-01 | Versi Laravel | 13 (terkunci `v13.36.0`) — masih dalam dukungan keamanan | Terkunci | `composer.lock` |
| D-02 | Versi PHP | 8.3.28, sesuai `php: ^8.3` | Terkunci | `php -v` |
| D-03 | Database | MySQL 8.0.30 (Laragon), port 3306, `DB_CONNECTION=mysql` | Terkunci | pemeriksaan langsung + `.env` |
| D-04 | Frontend build | Vite 8 + Tailwind CSS 4 | Terkunci | `package.json` |
| D-05 | Test framework | PHPUnit ^12.5.12 (terpasang); Pest menjadi opsional bila diinginkan | Terkunci | `composer.json` |
| D-06 | Version control | Git, branch `main`, 1 commit = 1 task selesai | Terkunci | riwayat git |
| D-07 | Kerahasiaan | `.env` tidak boleh masuk repository; secret tidak boleh ada di docs/log | Terkunci | `.gitignore` terverifikasi |

## B. Keputusan yang mengikuti Master Roadmap (dipakai sebagai acuan, pemasangan paket di Phase 1)

| ID | Keputusan | Status |
|---|---|---|
| D-10 | Stack: Laravel + MySQL + Blade + Livewire + Tailwind | Terkunci (acuan), implementasi Phase 1 |
| D-11 | Rich text editor: **Tiptap v3** (MIT) — CKEditor 5 ditolak karena lisensi GPL/komersial; Tiptap kompatibel Vite + Livewire/Blade, sanitasi server-side via HTMLPurifier | Terkunci 2026-10-14 |
| D-12 | Media Library memakai Laravel Filesystem | Terkunci (acuan) |
| D-13 | Role awal (Phase 2): `super_admin`, `content_admin`, `editor` — seeded via RoleSeeder | Terkunci interim |
| D-14 | Permission memakai notasi dot (`user.view`, `content.manage`, dll.) + `Gate::before` (super_admin bypass; ability dot dicek lewat RBAC) | Terkunci interim |
| D-15 | Editor saat ini TANPA hak publish (`content.publish` hanya super_admin & content_admin) — menunggu konfirmasi Q-12 | Usulan interim |

## C. Pertanyaan terbuka (wajib dijawab pemilik, sebagian menghambat fase berikut)

| ID | Pertanyaan | Usulan / rekomendasi | Pemilik | Status | Menghambat |
|---|---|---|---|---|---|
| Q-01 | Bahasa situs: ID, EN, atau bilingual? Kebijakan fallback? | Bahasa utama **ID**, EN sebagai alternatif dengan fallback ke ID | Owner Primainti | Terbuka | Phase 7 (bilingual) |
| Q-02 | Domain produksi? | — (belum ada info) | Owner Primainti | Terbuka | Phase 10/11 |
| Q-03 | Hosting & metode deployment (shared/VPS/dll)? | — | Owner Primainti | Terbuka | Phase 11 |
| Q-04 | SMTP untuk email, dan alamat penerima formulir kontak? | — | Owner Primainti | Terbuka | Phase 9 |
| Q-05 | Sumber foto, logo, sertifikat, datasheet, data produk? Materi resmi mana yang boleh dipakai? | Semua aset harus dari materi resmi yang disetujui; dilarang mengarang | Owner Primainti | Terbuka | Phase 3–8 |
| Q-06 | Akun admin awal: siapa, dan bagaimana pengelolaannya (buat manual saat Phase 2)? | Buat 1 akun admin melalui proses aman (bukan hardcoded password) | Owner Primainti + Tim Teknis | Terbuka | Phase 2 |
| Q-07 | Dokumen produk (datasheet) diunduh publik atau butuh login? | — | Owner Primainti | Terbuka | Phase 5 |
| Q-08 | Peta interaktif di halaman kontak, atau tautan peta biasa? | — | Owner Primainti | Terbuka | Phase 9 |
| Q-09 | Prosedur backup/restore database & file? | Backup terjadwal + dokumentasi restore | Owner Primainti | Terbuka | Phase 11 |
| Q-10 | URL situs lama untuk migrasi konten & redirect 301? | Perlu daftar URL; bila tidak ada situs lama, deklarasikan "tanpa redirect" | Owner Primainti | Terbuka | Phase 10/11 |
| Q-11 | Statistik, alamat, dan klaim bisnis mana yang sudah resmi disetujui? | Masuk daftar `docs/content/unverified-content.md` | Owner Primainti | Terbuka | Phase 8 |
| Q-12 | Hak publish konten: apakah Editor boleh publish, atau hanya Content Admin? (PRD menyebut ditentukan di Phase 0, belum tercatat) | Saat ini: Editor hanya draft (D-15) | Owner Primainti | Terbuka | Phase 4 |

## D. Aturan proses yang dipakai fase ini

- Satu fase PRD dikerjakan per waktu; pindah fase butuh persetujuan pemilik.
- Satu task selesai = satu commit.
- Perubahan database hanya melalui migration.
- Konten/klaim bisnis tidak boleh diarang-arang; yang belum diverifikasi ditandai eksplisit.
- Jawaban untuk Q-01…Q-11 dicatat di dokumen ini (ubah kolom **Status** menjadi `Terkunci` + tanggal dan nilai keputusan).

## E. Log perubahan keputusan

| Tanggal | Perubahan | Oleh |
|---|---|---|
| 2026-10-13 | Dokumen dibuat (Phase 0) | Audit agent |
| 2026-10-14 | Tambah D-13…D-15 (role awal & hak publish interim) + Q-12 | Phase 2 |
| 2026-10-14 | D-11 terkunci: Tiptap v3 (MIT); sanitasi HTMLPurifier | Phase 4 |
