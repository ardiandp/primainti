# PRD Phase 7 --- Navigasi, Pengaturan Global, dan Bilingual

**Dependensi:** Phase 2 dan struktur konten Phase 4 tersedia.

## Scope

-   CMS menu header/footer dengan urutan, parent-child, label bahasa,
    link route/URL/page/kategori.
-   Pengaturan logo, favicon, tagline, kontak, alamat, jam operasional,
    sosial media, CTA global.
-   Bahasa Indonesia dan Inggris.
-   Default locale dan fallback sesuai keputusan Phase 0.
-   Pengaturan SEO default.
-   Nilai sensitif seperti SMTP/API key tetap berada di
    environment/secret manager.

## Validasi

-   Cegah link kosong atau URL berbahaya.
-   Validasi tipe target.
-   Tampilkan status bila item menu merujuk konten yang tidak tersedia.
-   Switch bahasa diarahkan ke terjemahan yang sesuai bila tersedia.

## Acceptance criteria

-   Admin dapat mengubah menu tanpa mengubah kode.
-   Header/footer membaca konfigurasi CMS.
-   Switch bahasa bekerja sesuai fallback.
-   Tidak ada link menu rusak pada data yang dipublikasikan.
-   Kontak yang tampil konsisten di header, footer, dan halaman kontak.
