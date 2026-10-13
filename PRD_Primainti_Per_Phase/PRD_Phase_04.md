# PRD Phase 4 --- CMS Pages dan Rich Text Editor

**Dependensi:** Phase 3 lulus.

## Scope

-   CRUD halaman, terjemahan ID/EN, slug, excerpt, status
    draft/published/archived.
-   Preview halaman sebelum publish.
-   SEO title, meta description, Open Graph image dari Media Library.
-   Template key dan section/blocks yang sederhana, dengan urutan dan
    visibility.
-   Rich text editor terintegrasi dengan Blade/Livewire.
-   Sanitasi HTML saat penyimpanan/render.

## Rich text editor minimum

-   Paragraf dan heading.
-   Bold, italic, underline.
-   Bullet/numbered list.
-   Link/unlink.
-   Alignment dasar bila diperlukan.
-   Tabel sederhana bila didukung dengan aman.
-   Paste dari Word yang dibersihkan.
-   Pemilihan media dari Media Library.
-   Jangan menyediakan input HTML bebas tanpa sanitasi.

Pilih Tiptap atau CKEditor setelah mengecek dukungan integrasi, lisensi,
dan kebutuhan toolbar. Textarea biasa hanya untuk field pendek seperti
ringkasan, caption singkat, dan meta description.

## Aturan publikasi

-   Halaman draft/archived tidak tampil di publik.
-   `published_at` harus dipatuhi jika fitur jadwal diaktifkan.
-   Slug unik per locale.
-   Jika terjemahan belum ada, ikuti kebijakan fallback Phase 0 secara
    konsisten.

## Deliverables

-   CRUD CMS Pages.
-   Rich text editor.
-   Preview.
-   SEO fields.
-   Test slug, status, sanitasi, locale, dan permission.

## Acceptance criteria

-   Admin dapat membuat, mengedit, preview, dan publish halaman.
-   Format teks dasar tersimpan dan tampil benar.
-   HTML berbahaya dibersihkan.
-   Gambar di dalam konten memilih Media Library.
-   Slug duplikat ditolak.
