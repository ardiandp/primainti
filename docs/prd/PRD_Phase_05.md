# PRD Phase 5 --- Katalog Produk dan Detail Produk

**Dependensi:** Phase 3 dan Phase 4 lulus.

## Scope admin

-   CRUD kategori produk dan terjemahan.
-   CRUD produk dan terjemahan.
-   Kode/model opsional, status, featured flag, urutan.
-   Deskripsi singkat dan deskripsi panjang via rich text editor.
-   Fungsi dan aplikasi.
-   Spesifikasi teknis dalam struktur label, nilai, unit.
-   Media utama, galeri, datasheet/manual/sertifikat dari Media Library.
-   SEO, preview, publish/unpublish.

## Scope frontend

-   Katalog grid responsif.
-   Filter kategori dan pencarian.
-   Pagination.
-   Kartu produk dengan foto, kategori, nama, ringkasan, CTA.
-   Detail produk dengan breadcrumb, galeri, deskripsi, fungsi,
    aplikasi, tabel spesifikasi, dokumen unduhan bila disetujui, produk
    terkait, CTA penawaran.
-   State kosong dan hasil tidak ditemukan.

## Aturan

-   Produk draft/archived tidak muncul ke publik.
-   Jangan mengarang spesifikasi atau sertifikasi. Konten contoh harus
    jelas sebagai dummy dan tidak boleh dipublikasikan.
-   Media produk mereferensikan `media_assets`, tidak mengunggah salinan
    terpisah.
-   Filter numerik teknis lanjutan bukan bagian MVP kecuali disetujui.

## Acceptance criteria

-   Admin dapat mengelola kategori, produk, spesifikasi, dan media.
-   Katalog dapat dicari dan difilter.
-   Detail produk berfungsi pada mobile dan desktop.
-   Produk draft tidak dapat diakses publik.
-   Dokumen publik hanya dapat diunduh jika status/izin mengizinkan.
-   Test publikasi, pencarian, filter, dan spesifikasi lulus.
