# PRD Phase 6 --- Fasilitas dan Galeri

**Dependensi:** Phase 3 dan Phase 4 lulus.

## Fasilitas admin

-   CRUD fasilitas dan terjemahan ID/EN.
-   Judul, slug, ringkasan, body rich text, urutan, status.
-   Foto dari Media Library.
-   Field lokasi/kapasitas hanya bila data resmi tersedia.
-   URL peta/alamat berdasarkan konfirmasi pemilik.

## Galeri admin

-   CRUD kategori galeri.
-   CRUD item galeri, pilih media existing.
-   Caption, alt text override, tanggal opsional, urutan, status.
-   Satu media boleh dipakai pada fasilitas dan galeri tanpa file
    duplikat.

## Frontend

-   Halaman fasilitas dengan section produksi/perakitan,
    pergudangan/logistik, quality control, dan dukungan operasional jika
    terverifikasi.
-   Halaman galeri dengan filter kategori dan grid.
-   Lightbox accessible dengan keyboard/fokus yang memadai.
-   Lazy loading gambar dan alt text.

## Acceptance criteria

-   Fasilitas dan galeri dikelola dari admin.
-   Filter galeri berfungsi.
-   Draft tidak tampil publik.
-   Media dapat dipakai lintas modul.
-   Foto, lokasi, kapasitas, dan klaim operasional belum terverifikasi
    tidak dipublikasikan.
