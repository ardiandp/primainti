# MASTER ROADMAP --- PRD Website Primainti

Versi 1.1 \| 13 Oktober 2026 \| Penyusun: Irnis Dev

## Tujuan

Dokumen ini adalah indeks eksekusi. **Jangan meminta OpenCode
mengerjakan seluruh fase sekaligus.** Berikan hanya satu file PRD fase
aktif pada satu waktu, lalu tinjau hasilnya sebelum melanjutkan.

## Stack acuan

-   Laravel versi stabil yang masih mendapat dukungan keamanan saat
    implementasi
-   MySQL
-   Blade, Livewire, Tailwind CSS
-   Rich text editor yang kompatibel (Tiptap atau CKEditor setelah
    pemeriksaan lisensi/kompatibilitas)
-   Laravel Filesystem untuk Media Library
-   Pest atau PHPUnit

## Urutan fase

1.  Phase 0 --- Validasi kebutuhan dan setup repository
2.  Phase 1 --- Infrastruktur aplikasi dan database
3.  Phase 2 --- Autentikasi, RBAC, dan audit log
4.  Phase 3 --- Media Library reusable
5.  Phase 4 --- CMS Pages dan rich text editor
6.  Phase 5 --- Katalog produk dan detail produk
7.  Phase 6 --- Fasilitas dan galeri
8.  Phase 7 --- Navigasi, pengaturan global, dan bilingual
9.  Phase 8 --- Frontend lengkap sesuai mockup
10. Phase 9 --- Formulir kontak dan email
11. Phase 10 --- SEO, performa, aksesibilitas, dan keamanan
12. Phase 11 --- QA, UAT, migrasi konten, dan peluncuran
13. Phase 12 --- Pemeliharaan dan pengembangan lanjutan (opsional)

## Cara menggunakan

-   Simpan seluruh file ini dalam folder dokumentasi proyek, misalnya
    `docs/prd/`.
-   Mulai dengan `PRD_Phase_00.md` saja.
-   Setelah acceptance criteria fase terpenuhi dan hasil ditinjau,
    lanjutkan ke file fase berikutnya.
-   Jangan mengerjakan fase berikutnya ketika ada migration/test kritis
    yang gagal.
-   Konten, foto, sertifikasi, spesifikasi produk, statistik, alamat,
    dan klaim bisnis harus bersumber dari materi resmi yang disetujui
    Primainti.

## Prompt dasar untuk setiap fase

> Baca hanya PRD fase aktif dan dokumen arsitektur/keputusan yang
> relevan. Periksa repository sebelum mengubah kode. Kerjakan fase ini
> saja, jangan mengerjakan fase lain. Jika ada dependensi yang belum
> ada, laporkan dan berhenti pada titik tersebut; jangan membuat solusi
> asumtif yang mengubah scope. Gunakan migration, validasi,
> authorization, dan automated test yang sesuai. Jalankan test relevan,
> lalu laporkan file yang berubah, perintah yang dijalankan, hasil test,
> acceptance criteria yang terpenuhi/belum terpenuhi, asumsi, dan
> risiko. Jangan mengarang konten atau data perusahaan. Jangan simpan
> secret di repository. Tunggu persetujuan sebelum pindah fase.

## Definisi selesai umum

-   Acceptance criteria fase lulus.
-   Test relevan lulus atau kegagalan dijelaskan dengan bukti.
-   Dokumentasi diperbarui.
-   Tidak ada perubahan tak terkait.
-   Tidak ada secret/data sensitif di kode atau log.
-   Perubahan database hanya melalui migration.
