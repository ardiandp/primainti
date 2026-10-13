# PRD Phase 3 --- Media Library Reusable

**Dependensi:** Phase 2 lulus.\
**Tujuan:** satu sumber media yang dapat dipakai lintas modul seperti
WordPress.

## Scope

-   Upload satu file atau multi-file.
-   Grid/list, pencarian, filter jenis media, pagination.
-   Metadata: title, alt text, caption, description.
-   Detail teknis: MIME, ukuran, dimensi, tanggal, uploader.
-   Modal Media Picker reusable untuk semua modul.
-   Referensi penggunaan media dan peringatan sebelum penghapusan.
-   Soft delete/trash dan restore jika diimplementasikan.
-   Thumbnail dan optimasi gambar dasar.
-   Storage memakai Laravel Filesystem agar dapat berpindah disk
    kemudian.

## Prinsip data

`media_assets` menyimpan metadata dan path; file berada di storage,
bukan binary database. Konten menyimpan FK/referensi ke media. Variasi
thumbnail adalah turunan dari media sumber.

## Keamanan

-   Validasi MIME dan ekstensi, ukuran file, serta nama file yang aman.
-   Tolak file executable dan format yang tidak diperlukan.
-   Storage tidak boleh mengeksekusi upload sebagai kode.
-   Media yang masih direferensikan tidak boleh dihapus permanen.
-   Jangan melakukan deduplikasi berdasarkan checksum tanpa strategi
    collision dan keputusan produk yang jelas.

## Deliverables

-   Media Library admin.
-   Media Picker component.
-   Service/helper untuk URL media dan thumbnail.
-   Pemeriksaan referensi pemakaian.
-   Test upload, invalid file, reuse, dan delete protection.

## Acceptance criteria

-   Satu media dapat dipilih oleh dua atau lebih modul tanpa upload
    ulang.
-   Menghapus konten tidak menghapus file media yang dipakai modul lain.
-   File tidak valid ditolak dengan pesan jelas.
-   Metadata dan alt text dapat diedit.
-   Media picker reusable dan tidak terikat ke satu modul.
