# Content Inventory — Primainti

Fase: PRD Phase 0
Tanggal: 13 Oktober 2026
Status: Inventaris **halaman yang akan dibangun** (repo masih skeleton — belum ada halaman produksi).

> Sumber setiap konten wajib dari materi resmi yang disetujui Primainti.
> Item yang belum diverifikasi dicatat di `docs/content/unverified-content.md`.

## 1. Inventaris halaman

| # | Halaman | Route kandidat | Konten inti yang dibutuhkan | Sumber konten | Status |
|---|---|---|---|---|---|
| 1 | Beranda | `/` | Hero, ringkasan produk, keunggulan, CTA, footer | Materi resmi Primainti | Menunggu materi (Q-05) |
| 2 | Tentang Kami | `/tentang-kami` | Profil perusahaan, visi/misi, sejarah, sertifikat | Materi resmi Primainti | Menunggu materi (Q-05) |
| 3 | Produk (katalog) | `/produk` | Daftar produk: nama, gambar, kategori, ringkasan | Katalog resmi produk | Menunggu materi (Q-05) |
| 4 | Detail Produk | `/produk/{slug}` | Deskripsi, spesifikasi, galeri gambar, datasheet (opsional), produk terkait | Datasheet resmi | Menunggu materi (Q-05, Q-07) |
| 5 | Galeri | `/galeri` | Foto kegiatan/produk dengan filter kategori | Foto resmi | Menunggu materi (Q-05) |
| 6 | Fasilitas | `/fasilitas` | Daftar fasilitas: nama, foto, deskripsi | Materi resmi Primainti | Menunggu materi (Q-05) |
| 7 | Kontak | `/kontak` | Formulir kontak, alamat, telepon, email, peta | Data kontak resmi | Menunggu materi (Q-04, Q-08) |
| 8 | Legal (opsional) | `/kebijakan-privasi`, `/syarat-ketentuan` | Kebijakan privasi & syarat ketentuan | Disetujui owner | Keputusan: perlu/tidak (Q-11) |

Halaman admin (CMS Pages, manajemen produk, media library) bukan bagian inventaris publik ini — dirinci pada PRD Phase 2–7.

## 2. Inventaris konten per jenis

| Jenis konten | Dipakai di | Format | Status ketersediaan |
|---|---|---|---|
| Logo (primary, mono, favicon) | Seluruh halaman | SVG/PNG | Belum diterima |
| Nama & profil perusahaan | Tentang Kami, footer | Teks | Belum diterima |
| Visi/misi | Tentang Kami | Teks | Belum diterima |
| Daftar produk + spesifikasi | Katalog, Detail Produk | Teks + tabel | Belum diterima |
| Foto produk | Katalog, Detail, Galeri | JPG/WebP | Belum diterima |
| Foto fasilitas | Fasilitas | JPG/WebP | Belum diterima |
| Sertifikat/sertifikasi | Tentang Kami | Gambar/PDF | Belum diterima |
| Datasheet produk | Detail Produk | PDF | Belum diterima (Q-07: publik atau tidak) |
| Alamat, telepon, email, jam operasional | Kontak, footer | Teks | Belum diterima |
| Statistik perusahaan (tahun berdiri, kapasitas, dst.) | Beranda/Tentang | Teks/angka | Belum diterima — semua masuk unverified-content |
| Klaim bisnis (tagline, "terbaik", "terbesar", dst.) | Beranda | Teks | Belum diterima — semua masuk unverified-content |
| Testimoni/klien (bila ada) | Beranda/Tentang | Teks+logo | Belum diterima |

## 3. URL lama & redirect

| Item | Status |
|---|---|
| Daftar URL lama | **Belum tersedia** — tidak ada akses ke situs lama saat audit |
| Kandidat redirect 301 | Belum bisa disusun |
| Tindak lanjut | Owner mengisi daftar URL lama (Q-10 di `PROJECT_DECISIONS.md`); bila tidak ada situs lama, deklarasikan "tanpa redirect" |

## 4. Catatan audit

- Repo berisi skeleton Laravel segar: hanya `welcome.blade.php`, tidak ada halaman di atas yang sudah dibangun. Inventaris ini adalah **rencana**, bukan kondisi eksisting.
- Belum ada aset gambar/font/konten di `resources/` selain berkas bawaan.
- Struktur folder konten yang diusulkan untuk Phase 3+: `storage/app/public/` (media library) — detail di PRD Phase 3.
