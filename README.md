# Primainti — Website Company Profile

Aplikasi Laravel untuk website Primainti. Dikembangkan bertahap mengikuti PRD per fase di [`docs/prd/`](docs/prd/).

## Stack

| Komponen | Versi |
|---|---|
| PHP | ^8.3 (dev: 8.3.28) |
| Laravel | ^13.17 (terkunci: 13.36.0) |
| MySQL | 8.0 (dev: Laragon 8.0.30) |
| Livewire | ^4.4 |
| Tailwind CSS | ^4 |
| Vite | ^8 |
| Test | PHPUnit ^12 (`php artisan test`) |

## Prasyarat

- PHP 8.3+, Composer 2
- MySQL 8 berjalan (mis. Laragon/XAMPP/MAMP)
- Node.js 20+ dan npm (untuk build aset)

## Instalasi lokal (fresh install)

```powershell
# 1. Salin environment lalu isi kredensial DB (JANGAN commit .env)
copy .env.example .env

# 2. Buat database MySQL, misalnya bernama "primainti", lalu isi di .env:
#    DB_DATABASE=primainti
#    DB_USERNAME=...   DB_PASSWORD=...
#    ( .env.example sengaja TANPA kredensial )

# 3. Install dependensi + key + migrate + build aset dalam satu langkah
composer setup

# 4. (Opsional) isi data pengembangan: role + admin lokal
php artisan migrate:fresh --seed

# 5. (Opsional) isi data DUMMY agar website terlihat real (lokal saja,
#    DITOLAK di production, seluruh konten fiktif + gambar picsum.photos)
php artisan db:seed --class=DevDummySeeder
```

Jalankan server:

```powershell
php artisan serve        # http://localhost:8000
# atau mode dev (server + queue + vite sekaligus):
composer dev
```

Catatan instalasi:

- `composer setup` menjalankan: `composer install` → salin `.env` bila belum ada → `php artisan key:generate` → `php artisan migrate --force` → `npm install` → `npm run build`.
- Seeder mengisi role (`administrator`, `editor`) dan **satu akun admin HANYA untuk pengembangan lokal** (`admin@primainti.test`, password bawaan `UserFactory` — lihat `database/factories/UserFactory.php`). Bukan kredensial produksi.
- Seluruh kredensial produksi hanya hidup di `.env` yang masuk `.gitignore`.

## Menjalankan test

```powershell
composer test            # = php artisan test
```

Test memakai SQLite in-memory (`phpunit.xml`) sehingga tidak menyentuh database MySQL lokal. Cakupan Phase 1: smoke test homepage, koneksi database, dan schema test seluruh tabel inti.

## Perintah berguna

| Perintah | Fungsi |
|---|---|
| `php artisan migrate` | Jalankan migration yang belum jalan |
| `php artisan migrate:fresh --seed` | Bangun ulang database dari kosong + seeder |
| `php artisan migrate:status` | Status semua migration |
| `php artisan test` | Jalankan seluruh test |
| `composer setup` | Setup fresh install |
| `composer dev` | Server dev + queue + vite |

## Struktur dokumentasi

| Lokasi | Isi |
|---|---|
| [`docs/prd/`](docs/prd/) | PRD per fase (Phase 0–12) + Master Roadmap |
| [`docs/decisions/PROJECT_DECISIONS.md`](docs/decisions/PROJECT_DECISIONS.md) | Keputusan proyek & pertanyaan terbuka |
| [`docs/database/ERD.md`](docs/database/ERD.md) | Diagram relasi + aturan delete |
| [`docs/database/data-dictionary.md`](docs/database/data-dictionary.md) | Kamus data seluruh tabel |
| [`docs/content/`](docs/content/) | Inventaris konten & konten belum terverifikasi |
| [`docs/design/design-tokens.md`](docs/design/design-tokens.md) | Design tokens (preliminary) |
| [`docs/deployment/environment-checklist.md`](docs/deployment/environment-checklist.md) | Checklist environment |
| [`docs/deployment/email-setup.md`](docs/deployment/email-setup.md) | Setup email lokal (log mailer/Mailpit) & produksi |

## Aturan proyek (ringkas)

- Satu fase PRD dikerjakan per waktu; pindah fase butuh persetujuan.
- Satu task selesai = satu commit.
- Perubahan skema database hanya lewat migration.
- Query konten publik wajib status `published` (scope `published` tersedia di model).
- Jangan simpan secret di repository atau `site_settings`.
- Konten wajib dari materi resmi yang disetujui; yang belum diverifikasi ada di `docs/content/unverified-content.md`.
