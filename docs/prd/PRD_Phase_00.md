# PRD Phase 0 --- Validasi Kebutuhan dan Setup Repository

**Tujuan:** mengunci keputusan awal sebelum implementasi.

## Scope

-   Audit repository dan environment yang sudah tersedia.
-   Catat stack dan versi PHP/Composer/Node/MySQL yang tersedia.
-   Konfirmasi hosting, domain, deployment, SMTP, bahasa, backup, dan
    akses.
-   Inventaris halaman: Beranda, Tentang Kami, Produk, Detail Produk,
    Galeri, Fasilitas, Kontak, halaman legal bila diperlukan.
-   Inventaris konten dan URL lama untuk migrasi/redirect.
-   Tetapkan design tokens dari mockup: navy, putih, oranye, typography,
    spacing, button, card, breakpoints.
-   Pisahkan data yang sudah diverifikasi dari data yang masih
    memerlukan persetujuan.

## Di luar scope

Tidak membuat modul aplikasi, migration database, atau frontend
produksi.

## Deliverables

-   `docs/decisions/PROJECT_DECISIONS.md`
-   `docs/content/content-inventory.md`
-   `docs/content/unverified-content.md`
-   `docs/design/design-tokens.md`
-   `docs/deployment/environment-checklist.md`
-   Daftar URL lama dan kandidat redirect jika dapat diakses.

## Keputusan yang harus dicatat

-   Laravel/PHP versi yang kompatibel dan masih didukung.
-   MySQL versi yang tersedia.
-   Bahasa ID/EN dan kebijakan fallback.
-   Sumber foto, logo, sertifikat, datasheet, dan data produk.
-   SMTP dan alamat penerima formulir.
-   Akun admin awal dan metode pengelolaannya.
-   Apakah dokumen produk diunduh publik.
-   Peta interaktif atau tautan peta.
-   Prosedur backup/restore.

## Acceptance criteria

-   Repository dan environment sudah diperiksa, bukan diasumsikan.
-   Semua keputusan yang diketahui terdokumentasi.
-   Pertanyaan terbuka ditandai dengan pemilik dan status.
-   Tidak ada secret yang dimasukkan ke file dokumentasi.
-   Belum ada implementasi fitur yang tidak disetujui.

## Instruksi OpenCode

Lakukan inspeksi dan dokumentasi saja. Jangan mulai membangun website.
Ajukan pertanyaan hanya untuk keputusan yang benar-benar menghambat fase
berikutnya. Laporkan apa yang ditemukan, file dokumentasi yang dibuat,
serta keputusan yang masih terbuka.
