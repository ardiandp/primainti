# PRD Phase 10 --- SEO, Performa, Aksesibilitas, dan Keamanan

**Dependensi:** frontend dan modul inti sudah terintegrasi.

## SEO

-   Title/meta description per halaman dan produk.
-   Canonical bila diperlukan.
-   Open Graph dari Media Library.
-   `sitemap.xml`, `robots.txt`.
-   Structured data yang valid dan sesuai data resmi.
-   Redirect 301/302 untuk URL lama yang sudah dipetakan.
-   Draft/archived tidak masuk sitemap.

## Performa

-   Optimasi ukuran gambar dan thumbnail.
-   Lazy loading media non-hero.
-   Pagination dan eager loading.
-   Cache hanya bila sesuai dengan kebutuhan invalidasi konten.
-   Periksa query N+1.
-   Jangan menjanjikan skor performa sebelum diuji pada hosting target.

## Keamanan

-   Review authorization semua modul.
-   Review upload, sanitasi rich text, CSRF, rate limiting, validasi,
    error handling.
-   Pastikan debug mode nonaktif di production.
-   HTTPS dan headers keamanan yang sesuai.
-   Secrets tidak masuk repo/log.
-   Dependency audit bila tool tersedia.

## Aksesibilitas

-   Label form, alt text, struktur heading, focus state, keyboard
    navigation, kontras.
-   Lightbox dan menu mobile dapat dioperasikan dengan keyboard.

## Acceptance criteria

-   Sitemap hanya berisi URL publik yang layak diindeks.
-   Redirect penting teruji.
-   Tidak ada kerentanan kritis yang diketahui dari pemeriksaan yang
    dilakukan.
-   Tidak ada N+1 yang jelas pada katalog/detail produk.
-   Laporan hasil audit dan pekerjaan yang tersisa tersedia.
