# PRD Phase 9 --- Formulir Kontak dan Email

**Dependensi:** Phase 1, 2, 7, dan 8 tersedia.

## Scope frontend

-   Field nama, email, subjek, pesan wajib.
-   Perusahaan dan telepon opsional.
-   Validasi yang jelas dan pesan sukses/gagal.
-   Link peta atau embed peta berdasarkan lokasi resmi yang
    dikonfirmasi.

## Scope backend

-   Simpan pesan ke `contact_messages`.
-   Status: new, in_progress, resolved, spam.
-   Email notifikasi melalui SMTP.
-   Admin dapat melihat detail dan memperbarui status.
-   Catatan internal opsional.

## Keamanan dan privasi

-   CSRF, server-side validation, rate limiting.
-   Honeypot; CAPTCHA hanya jika disetujui/diperlukan.
-   Batas panjang field dan normalisasi input.
-   Pesan tidak boleh tampil di frontend publik.
-   Retensi pesan dan akses admin perlu disepakati.
-   Bila email gagal, pesan tetap tersimpan dan error tercatat tanpa
    data sensitif.

## Acceptance criteria

-   Pesan valid tersimpan satu kali.
-   Email notifikasi berjalan pada SMTP staging.
-   Pesan invalid/spam ditangani dengan aman.
-   Hanya role berizin dapat melihat pesan.
-   Kegagalan SMTP tidak menghilangkan pesan tersimpan.
