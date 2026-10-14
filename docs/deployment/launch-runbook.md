# Launch Runbook — Primainti (PRD Phase 11)

Status: Draf. Peluncuran DILARANG sampai seluruh keputusan terbuka di bawah
terkunci dan UAT ditandatangani.

## 0. Penghambat peluncuran (wajib terkunci dulu)

| ID | Keputusan | Status |
|---|---|---|
| Q-02 | Domain produksi | Terbuka |
| Q-03 | Hosting & metode deployment | Terbuka |
| Q-04 | SMTP + alamat penerima formulir | Terbuka (interim: penerima = `contact_email` pengaturan) |
| Q-05 | Materi resmi konten/media | Terbuka (belum ada materi masuk) |
| Q-09 | Prosedur backup/restore | Terbuka (usulan prosedur di §4) |
| Q-10 | Daftar URL lama untuk redirect | Terbuka (tanpa daftar = tanpa redirect) |
| Q-01 | Bahasa default/fallback final | Terbuka (interim: default `en`, fallback `en`) |
| Q-12 | Hak publish Editor final | Terbuka (interim: tanpa publish) |

## 1. Urutan peluncuran

Staging DULU, verifikasi penuh, baru production. Jangan deploy langsung ke
production.

## 2. Deploy staging

```sh
git clone <repo> && cd primainti
cp .env.example .env
# Isi .env staging (lihat §3, dengan nilai staging)
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan storage:link
npm install && npm run build
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Verifikasi: smoke test §6 lulus di staging.

## 3. Checklist `.env` production

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<domain-produksi>
DB_CONNECTION=mysql
DB_HOST=...  DB_PORT=3306  DB_DATABASE=...  DB_USERNAME=...  DB_PASSWORD=<secret>
MAIL_MAILER=smtp
MAIL_HOST=...  MAIL_PORT=...  MAIL_USERNAME=...  MAIL_PASSWORD=<secret>
MAIL_FROM_ADDRESS=<alamat-pengirim>  MAIL_FROM_NAME="Primainti"
SESSION_SECURE_COOKIE=true
```

Secret HANYA di environment server, tidak di repo/chat/log.

## 4. Backup & restore (uji sebelum go-live)

Backup:

```sh
mysqldump -h $DB_HOST -u $DB_USER -p $DB_DATABASE | gzip > backup-$(date +%F).sql.gz
tar -czf files-$(date +%F).tar.gz storage/app/public
```

Uji restore di staging: buat database kosong, import dump, ekstrak arsip,
jalankan smoke test §6. Restore yang belum diuji = tidak ada backup.

## 5. HTTPS, storage, scheduler

- HTTPS aktif + redirect HTTP→HTTPS di hosting. HSTS hanya bila semua
  subdomain sudah HTTPS (jangan setengah-setengah).
- Permission tulis: `storage/`, `bootstrap/cache/`.
- Cron scheduler (rekomendasi standar, untuk kebutuhan mendatang):
  `* * * * * php /path/artisan schedule:run >> /dev/null 2>&1`
- Queue worker TIDAK dibutuhkan saat ini (email kontak dikirim sinkron;
  worker hanya bila antrean dipakai nanti).

## 6. Smoke test pasca-deploy (wajib lulus)

- `/` redirect ke home locale; `/id`, `/en` 200.
- `/id/produk`, `/id/galeri`, `/id/fasilitas`, `/id/kontak` 200.
- `/sitemap.xml`, `/robots.txt` 200.
- Kirim 1 pesan kontak uji → tersimpan + email diterima.
- Login admin → dashboard, buka Media Library, pratinjau 1 halaman.
- Header keamanan hadir (`X-Content-Type-Options: nosniff`).
- Otomatis: `php artisan test --filter=DeploySmokeTest`.

## 7. Akun admin produksi (manual, oleh pemilik)

```sh
php artisan tinker
```

```php
$admin = App\Models\User::factory()->create([
    'name' => 'Nama Resmi', 'email' => 'email@resmi',
    'password' => '<acak-kuat-min-16-karakter>', // ganti lagi setelah login pertama
]);
$admin->assignRole(App\Models\Role::where('name', 'super_admin')->first());
```

Seeder DITOLAK membuat akun default di production (proteksi di
`DatabaseSeeder`). Jangan pernah `db:seed` data dev di production.

## 8. Rollback plan

- Kode: deploy ulang tag/commit terakhir yang stabil
  (`git fetch && git checkout <tag-stabil>` + `npm run build` + clear cache).
- Database: restore dump §4. PERINGATAN: migration yang menambah kolom
  non-nullable tanpa default TIDAK bisa rollback mulus — tinjau migration
  sebelum deploy.
- File: restore arsip `files-*.tar.gz`.
- Kriteria rollback: smoke test §6 gagal, atau bug kritis UAT.

## 9. UAT (pemilik proyek)

| Area | Diperiksa | Status | Catatan |
|---|---|---|---|
| Beranda | hero, profil, unggulan, fasilitas, CTA, kontak | | |
| Tentang/konten | teks, foto, slug, bahasa ID/EN | | |
| Produk | kategori, filter, cari, detail, spek, dokumen | | |
| Galeri | filter, lightbox, caption | | |
| Fasilitas | urutan, foto | | |
| Formulir | valid, invalid, email diterima | | |
| Bahasa | switcher ID↔EN, fallback | | |
| Menu/header/footer | label, tautan, kontak konsisten | | |

Aturan bug: kritis (blokir peluncuran) vs mayor vs minor. Bug kritis
wajib selesai sebelum go-live. Persetujuan: nama + tanggal + tanda tangan.

## 10. Migrasi konten

Belum ada materi resmi yang diterima (Q-05) — migration konten = 0 item
sampai pemilik menyerahkan materi. Saat materi masuk: upload media SATU
kali via Media Library, hubungkan ke konten, verifikasi tiap produk/
datasheet/foto/sertifikat/alamat, petakan URL lama (Q-10), JANGAN pindah
klaim tak terverifikasi (`docs/content/unverified-content.md`).
