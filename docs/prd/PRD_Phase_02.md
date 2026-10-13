# PRD Phase 2 --- Autentikasi Admin, RBAC, dan Audit Log

**Dependensi:** Phase 1 lulus.

## Scope

-   Login/logout admin.
-   Reset password melalui alur aman jika SMTP tersedia; jika belum,
    dokumentasikan setup lokal.
-   Middleware untuk area admin.
-   Role dan permission.
-   User management: tambah, edit, nonaktifkan, reset akses.
-   Policies/Gates untuk semua aksi sensitif.
-   Audit log untuk login penting, perubahan role/user, publish,
    hapus/arsip konten, dan pengaturan.

## Role awal

-   Super Admin: seluruh akses.
-   Administrator Konten: kelola konten dan media sesuai izin.
-   Editor/Reviewer: membuat dan mengubah draft; hak publish ditentukan
    pada Phase 0.

## Keamanan

-   Validasi backend untuk semua input.
-   Rate limiting login.
-   CSRF dan session security sesuai praktik Laravel.
-   Jangan mencatat password, token, atau secret dalam log.
-   Jangan mengandalkan penyembunyian tombol sebagai otorisasi.

## Deliverables

-   Halaman login/logout.
-   Admin layout dasar.
-   CRUD user dan role/permission.
-   Policies/Gates.
-   Audit log dasar.
-   Test akses per role.

## Acceptance criteria

-   Pengunjung tanpa login tidak dapat mengakses admin.
-   User tanpa permission ditolak meskipun mengakses URL langsung.
-   User nonaktif tidak dapat login.
-   Aksi sensitif tercatat tanpa menyimpan data rahasia.
-   Test otorisasi utama lulus.
