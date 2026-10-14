# Primainti — Website Company Profile

Aplikasi Laravel untuk website company profile Primainti (katalog produk,
fasilitas, galeri, halaman CMS bilingual ID/EN, formulir kontak).
Dikembangkan bertahap mengikuti PRD per fase di [`docs/prd/`](docs/prd/).

## 1. Software yang digunakan

| Software | Versi dipakai | Fungsi | Unduh |
|---|---|---|---|
| PHP | 8.3.28 (`^8.3`) | Runtime Laravel | [php.net](https://www.php.net/downloads) / Laragon |
| Composer | 2.10.2 | Dependensi PHP | [getcomposer.org](https://getcomposer.org/download/) |
| MySQL | 8.0.30 | Database | via Laragon / XAMPP |
| Node.js + npm | 24.17.0 + 11.13.0 | Build aset Vite | [nodejs.org](https://nodejs.org/) (butuh v20+) |
| Git | 2.37.3+ | Version control | [git-scm.com](https://git-scm.com/downloads) |
| Laragon (opsional) | — | Paket PHP+MySQL Windows | [laragon.org](https://laragon.org/download/) |

Paket aplikasi utama (`composer.json` / `package.json`):

| Paket | Versi | Fungsi |
|---|---|---|
| `laravel/framework` | 13.36.0 | Backend |
| `livewire/livewire` | ^4.4 | Komponen interaktif (Media Picker) |
| `mews/purifier` | ^3.4 | Sanitasi HTML konten editor |
| `tailwindcss` + `@tailwindcss/vite` | ^4 | CSS |
| `vite` + `laravel-vite-plugin` | ^8 / ^3 | Build aset |
| `@tiptap/*` (core, starter-kit, link, image, table, dsb.) | ^3 | Rich text editor |
| `phpunit/phpunit` | ^12 | Automated test (`php artisan test`) |

## 2. Instalasi lokal (fresh install)

```powershell
# 1. Clone lalu masuk direktori proyek
git clone <url-repo> primainti
cd primainti

# 2. Salin environment (JANGAN commit .env) lalu isi kredensial DB:
copy .env.example .env
#    DB_DATABASE=primainti
#    DB_USERNAME=...   DB_PASSWORD=...

# 3. Buat database MySQL kosong bernama "primainti"

# 4. Install + key + migrate + build aset dalam satu langkah
composer setup

# 5. (Opsional) isi role + akun admin lokal
php artisan migrate:fresh --seed

# 6. (Opsional) isi data DUMMY agar website terlihat real — LOKAL SAJA
#    (ditolak di production; konten fiktif + gambar picsum.photos)
php artisan db:seed --class=DevDummySeeder

# 7. Symlink storage agar gambar tampil (biasanya sudah ada)
php artisan storage:link
```

Jalankan server:

```powershell
php artisan serve        # http://localhost:8000
# atau sekaligus server + queue + vite:
composer dev
# lalu buka http://localhost:8000/id
```

Catatan:

- `composer setup` = `composer install` → salin `.env` bila belum ada →
  `key:generate` → `migrate --force` → `npm install` → `npm run build`.
- Akun admin lokal: `admin@primainti.test` (password bawaan `UserFactory`,
  lihat `database/factories/UserFactory.php`). **Bukan kredensial produksi**
  — seeder menolak membuatnya di production.
- Kredensial produksi hanya hidup di `.env` server (masuk `.gitignore`).
- Test memakai SQLite in-memory (`phpunit.xml`), tidak menyentuh MySQL.

## 3. Panduan pengunjung (website publik)

| Halaman | URL | Keterangan |
|---|---|---|
| Beranda | `/id`, `/en` (`/` redirect) | Hero, profil, produk unggulan, fasilitas, CTA, kontak |
| Produk | `/{locale}/produk` | Filter kategori, pencarian, pagination |
| Detail produk | `/{locale}/produk/{slug}` | Galeri, spek, dokumen, produk terkait, CTA penawaran |
| Fasilitas | `/{locale}/fasilitas` | Daftar fasilitas + foto |
| Galeri | `/{locale}/galeri` | Filter kategori + lightbox (keyboard: Esc/←/→) |
| Kontak | `/{locale}/kontak` | Info + formulir (nama, email, subjek, pesan wajib) |
| Halaman CMS | `/{locale}/{slug}` | Tentang Kami & halaman bebas dari admin |
| Peta situs / robot | `/sitemap.xml`, `/robots.txt` | Otomatis dari konten published |

Pindah bahasa lewat switcher ID/EN di header (mengikuti terjemahan yang ada,
fallback otomatis bila belum diterjemahkan).

## 4. Panduan admin

Masuk lewat `/login` → dashboard `/admin`. Peran dan haknya:

| Peran | Hak |
|---|---|
| Super Admin | Semua akses (termasuk user, role, pengaturan) |
| Content Admin | Konten, media, publish, pesan kontak |
| Editor | Buat/ubah **draft** saja (tanpa hak publish — interim) |

Alur kerja baku: **buat sebagai draft → Pratinjau → Publish**.
Draft/arsip tidak tampil publik. Hapus = masuk trash (bisa dipulihkan).

| Menu | URL | Kegunaan |
|---|---|---|
| Halaman | `/admin/pages` | Halaman CMS ID/EN (editor teks, sections, SEO, OG image) |
| Produk | `/admin/products` | Produk (kategori, SKU, unggulan, spek label/nilai/satuan, galeri + foto utama, dokumen PDF, SEO) |
| Kategori Produk | `/admin/product-categories` | Kategori + slug per bahasa (tak bisa dihapus bila berisi produk) |
| Fasilitas | `/admin/facilities` | Fasilitas + foto (foto pertama = sampul) |
| Kategori Galeri | `/admin/gallery-categories` | Kategori galeri |
| Galeri | `/admin/gallery-items` | Item galeri (media existing wajib, caption, alt override, tanggal, urutan) |
| Media Library | `/admin/media` | Upload JPG/PNG/WebP/GIF/PDF; satu file dipakai lintas modul; media yang dipakai tak bisa dihapus permanen |
| Menu | `/admin/menus` | Navigasi header/footer (sub-menu 1 level, label per bahasa; target rusak ditandai) |
| Pengaturan | `/admin/settings` | Tagline, logo, kontak, sosmed, CTA, SEO (super admin; bukan tempat secret) |
| Pesan Kontak | `/admin/contact-messages` | Workflow new → in_progress → resolved/spam + catatan internal |
| Users / Roles | `/admin/users`, `/admin/roles` | Kelola akun, peran, nonaktifkan, reset akses (super admin) |

Aturan konten (wajib):

- Tulis dari materi resmi yang disetujui; klaim/spesifikasi/angka tak
  terverifikasi **dilarang tayang** (daftar: `docs/content/unverified-content.md`).
- Gambar di dalam editor diambil dari Media Library, bukan upload ganda.
- Jangan simpan password/API key di pengaturan, catatan, atau log.

Detail tiap modul: [`docs/admin-manual.md`](docs/admin-manual.md).

## 5. Perintah berguna

| Perintah | Fungsi |
|---|---|
| `php artisan serve` | Server lokal |
| `composer dev` | Server + queue + vite |
| `npm run build` / `npm run dev` | Build / watch aset |
| `php artisan migrate` | Migration tertunda |
| `php artisan migrate:fresh --seed` | Ulang DB + role/admin lokal |
| `php artisan db:seed --class=DevDummySeeder` | Data dummy lokal |
| `php artisan storage:link` | Symlink file publik |
| `php artisan test` / `composer test` | Seluruh automated test (152 test) |
| `php artisan test --filter=DeploySmokeTest` | Gerbang smoke pra-deploy |

## 6. Deploy & dokumen lain

- Runbook peluncuran (staging→prod, backup, rollback, UAT):
  [`docs/deployment/launch-runbook.md`](docs/deployment/launch-runbook.md)
- Keputusan terbuka pemilik (domain, hosting, SMTP, materi, bahasa):
  [`docs/decisions/PROJECT_DECISIONS.md`](docs/decisions/PROJECT_DECISIONS.md)
- Struktur dokumentasi lengkap lihat tabel di bawah.

| Lokasi | Isi |
|---|---|
| [`docs/prd/`](docs/prd/) | PRD per fase (Phase 0–12) + Master Roadmap |
| [`docs/admin-manual.md`](docs/admin-manual.md) | Manual admin serah terima |
| [`docs/database/ERD.md`](docs/database/ERD.md) | Diagram relasi + aturan delete |
| [`docs/database/data-dictionary.md`](docs/database/data-dictionary.md) | Kamus data seluruh tabel |
| [`docs/content/`](docs/content/) | Inventaris konten & belum terverifikasi |
| [`docs/design/design-tokens.md`](docs/design/design-tokens.md) | Design tokens |
| [`docs/deployment/`](docs/deployment/) | Checklist env, email, runbook |

## 7. Aturan proyek (ringkas)

- Satu fase PRD per waktu; pindah fase butuh persetujuan.
- Satu task selesai = satu commit.
- Skema database hanya lewat migration.
- Konten publik wajib status `published` (+ jadwal tayang bila diisi).
- Jangan simpan secret di repo atau `site_settings`.
