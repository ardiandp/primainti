# Setup Email — Primainti

Status Phase 2: **SMTP produksi belum dikonfigurasi** (terbuka: Q-04 di `docs/decisions/PROJECT_DECISIONS.md`).
Alur reset password sudah aktif memakai mailer bawaan Laravel, jadi tetap bisa diuji lokal.

## Mode lokal (default, tanpa SMTP)

`.env` bawaan memakai:

```
MAIL_MAILER=log
```

Semua email (mis. tautan reset password) **ditulis ke** `storage/logs/laravel.log`.
Cara menguji alur lupa password lokal:

1. Buka `/forgot-password`, masukkan email admin dev (`admin@primainti.test`).
2. Buka `storage/logs/laravel.log`, cari baris berisi URL `http://localhost:8000/reset-password/...`.
3. Buka URL tersebut, isi password baru, lalu login dengan password baru.

Email yang terkirim tidak pernah mengandung password — hanya tautan reset (token kedaluwarsa 60 menit).

## Mode lokal dengan server mail sungguhan (opsional)

Gunakan Mailpit (https://mailpit.axllent.org) atau MailHog:

```
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="no-reply@primainti.test"
MAIL_FROM_NAME="${APP_NAME}"
```

Email tampil di UI Mailpit (default http://localhost:8025) tanpa benar-benar terkirim.

## Produksi (menunggu keputusan Q-04)

Isi di `.env` **saja** (tidak pernah di-commit):

```
MAIL_MAILER=smtp
MAIL_HOST=<host SMTP>
MAIL_PORT=<port>
MAIL_USERNAME=<user>
MAIL_PASSWORD=<password>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=<alamat pengirim resmi>
MAIL_FROM_NAME="${APP_NAME}"
```

Checklist sebelum produksi:

- [ ] SMTP disetujui pemilik + kredensial tersedia (Q-04)
- [ ] Alamat penerima formulir kontak ditentukan (Q-04)
- [ ] Uji kirim email reset password dari domain produksi
- [ ] Cek folder spam/keluhan deliverability (SPF/DKIM)
