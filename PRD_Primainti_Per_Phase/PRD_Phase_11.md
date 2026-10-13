# PRD Phase 11 --- QA, UAT, Migrasi Konten, dan Peluncuran

**Dependensi:** Phase 0--10 diselesaikan atau pengecualian disetujui
tertulis.

## Scope QA

-   Unit/feature tests.
-   Browser smoke tests pada desktop dan mobile.
-   Pengujian role dan permission.
-   Pengujian status draft/published.
-   Pengujian media reuse dan perlindungan delete.
-   Pengujian form kontak/email.
-   Broken links, redirect, SEO metadata, bahasa, dan fallback.

## Migrasi konten

-   Impor konten yang disetujui.
-   Upload media resmi satu kali dan hubungkan lewat Media Library.
-   Verifikasi produk, kategori, datasheet, foto, sertifikasi, alamat,
    kontak.
-   Petakan URL lama ke URL baru.
-   Jangan memindahkan klaim yang tidak terverifikasi.

## Deployment

-   Staging lalu production.
-   Backup database dan file sebelum deploy.
-   Uji restore backup.
-   Setup HTTPS, env production, storage permission, scheduler, queue
    worker jika digunakan.
-   Smoke test setelah deployment.
-   Siapkan rollback plan.

## UAT

-   Pemilik proyek meninjau halaman, konten, produk, foto, formulir,
    bahasa, dan kontak.
-   Catat bug dan tingkat prioritas.
-   Bug kritis diselesaikan sebelum peluncuran.

## Acceptance criteria

-   UAT disetujui.
-   Backup/restore diuji.
-   HTTPS aktif dan debug nonaktif.
-   Login admin, upload media, halaman publik, dan form kontak bekerja
    di production.
-   Tidak ada akun/credential default.
-   Dokumentasi admin dan serah terima tersedia.
