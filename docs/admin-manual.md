# Manual Admin — Website Primainti (serah terima Phase 11)

## 1. Masuk & peran

- Buka `/login`. Akun nonaktif tidak bisa masuk; 5x salah = terkunci sesaat.
- Peran: **Super Admin** (semua akses), **Content Admin** (konten+media,
  publish, pesan kontak), **Editor** (buat/ubah draft, TANPA publish —
  interim menunggu Q-12).
- Ganti password secara berkala; jangan bagikan akun.

## 2. Alur kerja konten (bilingual ID/EN)

1. Buat sebagai **draft**, isi minimal 1 bahasa (ID diutamakan).
2. Slug otomatis dari judul; pastikan unik per bahasa.
3. **Pratinjau** sebelum publish (tombol Pratinjau).
4. Publish hanya oleh Content Admin/Super Admin. Jadwal tayang opsional.
5. Draft/arsip TIDAK tampil publik. Hapus = masuk trash dulu (bisa pulihkan).

## 3. Modul

- **Halaman**: konten RTE (format dasar, tabel, gambar dari Media Library),
  sections (urutan + tampil/sembunyi), SEO + OG image.
- **Produk**: kategori wajib; SKU opsional; unggulan + urutan untuk beranda/
  katalog; deskripsi, fungsi, aplikasi via RTE; spesifikasi label/nilai/
  satuan; galeri (tandai 1 utama); dokumen PDF (datasheet/manual/
  sertifikat).
- **Fasilitas**: ringkasan + deskripsi + foto (foto pertama = sampul).
- **Galeri**: kategori + item (media existing wajib, caption, alt override,
  tanggal, urutan). Satu file boleh dipakai banyak modul.
- **Media Library**: upload JPG/PNG/WebP/GIF/PDF; lengkapi alt text; media
  yang dipakai konten TIDAK bisa dihapus permanen; hapus konten tidak
  menghapus file.
- **Menu**: header/footer, 1 level sub-menu; label per bahasa; target
  rusak (konten draft/terhapus) ditandai dan disembunyikan di publik.
- **Pengaturan**: identitas, kontak, sosmed, CTA, SEO default. BUKAN
  tempat secret.
- **Pesan Kontak**: tandai new → in_progress → resolved/spam; catatan
  internal hanya untuk admin.

## 4. Larangan

- Dilarang mengarang spesifikasi, sertifikasi, statistik, atau klaim.
  Yang belum disetujui pemilik dilarang tayang.
- Dilarang menyimpan password/API key di pengaturan/catatan.
- Dilarang menghapus media yang masih dipakai modul lain (sistem menolak).

## 5. Bila ada masalah

Catat langkah reproduksi + tangkapan layar + waktu kejadian, lalu hubungi
tim teknis. Jangan utak-atik server/`.env` sendiri.
