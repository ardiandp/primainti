# PRD Phase 1 --- Infrastruktur Aplikasi dan Database

**Dependensi:** Phase 0 disetujui.\
**Tujuan:** fondasi Laravel/MySQL yang dapat diulang pemasangannya.

## Scope

-   Setup Laravel, Blade, Livewire, Tailwind, test runner.
-   Konfigurasi MySQL melalui `.env`.
-   Struktur konfigurasi locale, timezone, logging, mail, storage,
    queue.
-   Buat migration dasar untuk tabel Laravel yang dibutuhkan.
-   Buat migration entitas inti secara berurutan sesuai foreign key.
-   Siapkan factories/seeders hanya untuk data pengembangan.
-   Dokumentasikan instalasi lokal dan perintah test.

## Skema tahap fondasi

Urutan disarankan: 1. `users` dan tabel standar autentikasi yang
diperlukan. 2. `roles`, `permissions`, `role_user`, `permission_role`
(atau paket RBAC yang disetujui). 3. `media_assets`, `media_variants`.
4. `pages`, `page_translations`. 5. `product_categories`,
`product_category_translations`. 6. `products`, `product_translations`,
`product_media`, `product_specifications`,
`product_specification_translations`. 7. `facilities`,
`facility_translations`, `facility_media`. 8. `gallery_categories`,
`gallery_category_translations`, `gallery_items`,
`gallery_item_translations`. 9. `menus`, `menu_items`, `site_settings`.
10. `contact_messages`, `redirects`, `audit_logs`. Boleh menunda
migration modul yang belum dibutuhkan, tetapi jangan membuat tabel tanpa
relasi/constraint yang jelas.

## Aturan database

-   PK `id` bigint unsigned secara konsisten.
-   FK dengan index dan aturan delete yang disengaja.
-   Unique locale + slug untuk tabel terjemahan.
-   `created_at`/`updated_at`; `deleted_at` hanya pada entitas yang
    membutuhkan soft delete.
-   Jangan cascade-delete media dari konten.
-   Konten publik hanya boleh mengambil status `published`.
-   Semua perubahan skema harus lewat migration.
-   Nilai secret tidak disimpan dalam `site_settings`.

## Deliverables

-   Aplikasi bisa dijalankan lokal.
-   `.env.example` tanpa credential.
-   Migration, factory, seeder dasar.
-   ERD diperbarui di `docs/database/ERD.md`.
-   Data dictionary di `docs/database/data-dictionary.md`.
-   README setup dan test.

## Acceptance criteria

-   Fresh install mengikuti README berhasil.
-   Migration dan seeder berhasil dari database kosong.
-   Foreign key, unique constraint, dan index penting terpasang.
-   Test koneksi dan smoke test dasar berhasil.
-   Tidak ada credential di repository.
