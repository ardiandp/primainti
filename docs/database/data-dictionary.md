# Data Dictionary — Primainti (Phase 1)

Tanggal diperbarui: 13 Oktober 2026
Sumber: informasi langsung `information_schema` MySQL 8.0.30, database `primainti`.
Konvensi: PK `id bigint unsigned auto_increment` di semua tabel; `created_at`/`updated_at` timestamp nullable; soft delete = kolom `deleted_at` (belum ada di tabel ini bila kolom tidak dicantumkan).

## A. Tabel infrastruktur bawaan Laravel

### users
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| name | varchar(255) | no | — | — |
| email | varchar(255) | no | UNI | — |
| email_verified_at | timestamp | yes | — | — |
| password | varchar(255) | no | — | hash bcrypt |
| is_active | boolean | no | true | false = tidak bisa login |
| remember_token | varchar(100) | yes | — | — |
| created_at / updated_at | timestamp | yes | — | — |

### password_reset_tokens
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| email | varchar(255) | no | PRI | — |
| token | varchar(255) | no | — | — |
| created_at | timestamp | yes | — | — |

### sessions
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | varchar(255) | no | PRI | — |
| user_id | bigint unsigned | yes | MUL, FK users (CASCADE) | — |
| ip_address | varchar(45) | yes | — | — |
| user_agent | text | yes | — | — |
| payload | longtext | no | — | — |
| last_activity | int | no | MUL | indeks kebersihan sesi |

### cache, cache_locks, jobs
Tabel bawaan `SESSION_DRIVER`/`CACHE_STORE`/`QUEUE_CONNECTION` = database. `cache(key PK, value, expiration)`, `cache_locks(key PK, owner, expiration)`, `jobs(id, queue, payload, attempts, reserved_at, available_at, created_at)`.

### migrations
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| migration | varchar(255) | no | — | nama file migrasi |
| batch | int | no | — | nomor batch |

## B. RBAC

### roles
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| name | varchar(255) | no | UNI | mis. administrator, editor |
| description | varchar(255) | yes | — | — |
| created_at / updated_at | timestamp | yes | — | — |

### permissions
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| name | varchar(255) | no | UNI | mis. product.create (diisi Phase 2) |
| description | varchar(255) | yes | — | — |
| created_at / updated_at | timestamp | yes | — | — |

### role_user (pivot)
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| role_id | bigint unsigned | no | PK komposit, FK roles CASCADE | — |
| user_id | bigint unsigned | no | PK komposit, FK users CASCADE | — |
| created_at / updated_at | timestamp | yes | — | — |

### permission_role (pivot)
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| permission_id | bigint unsigned | no | PK komposit, FK permissions CASCADE | — |
| role_id | bigint unsigned | no | PK komposit, FK roles CASCADE | — |
| created_at / updated_at | timestamp | yes | — | — |

## C. Media

### media_assets
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| disk | varchar(255) | no | 'public' | disk Laravel filesystem |
| path | varchar(255) | no | MUL | path relatif di disk |
| original_name | varchar(255) | no | — | nama file asli |
| mime_type | varchar(255) | no | — | — |
| size | bigint unsigned | no | — | byte |
| width / height | int unsigned | yes | — | piksel (bila terdeteksi) |
| alt | varchar(255) | yes | — | teks alternatif aksesibilitas |
| uploaded_by | bigint unsigned | yes | FK users SET NULL | — |
| created_at / updated_at / deleted_at | timestamp | yes | — | soft delete |

### media_variants
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| media_asset_id | bigint unsigned | no | FK CASCADE, UQ(media_asset_id, variant) | — |
| variant | varchar(255) | no | | mis. thumb, medium, large |
| path | varchar(255) | no | — | — |
| mime_type | varchar(255) | yes | — | — |
| size | bigint unsigned | no | 0 | byte |
| width / height | int unsigned | yes | — | — |
| created_at / updated_at | timestamp | yes | — | — |

## D. CMS Pages

### pages
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| template | varchar(255) | yes | — | nama template blade (opsional) |
| status | varchar(255) | no | MUL, 'draft' | draft / published |
| published_at | timestamp | yes | — | waktu publikasi |
| created_at / updated_at / deleted_at | timestamp | yes | — | soft delete |

### page_translations
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| page_id | bigint unsigned | no | FK pages CASCADE, UQ(page_id, locale) | — |
| locale | varchar(10) | no | UQ(locale, slug) | id / en (keputusan Q-01) |
| title | varchar(255) | no | — | — |
| slug | varchar(255) | no | | unik per locale → URL |
| summary | text | yes | — | — |
| content | longtext | yes | — | konten rich text (tersanitasi) |
| seo_title | varchar(255) | yes | — | SEO title per locale |
| seo_description | varchar(500) | yes | — | meta description per locale |
| og_image_id | bigint unsigned | yes | FK media_assets SET NULL | OG image dari Media Library |
| created_at / updated_at | timestamp | yes | — | — |

### page_sections
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| page_translation_id | bigint unsigned | no | FK CASCADE, MUL(position) | section milik terjemahan |
| position | int unsigned | no | 0 | urutan tampil |
| heading | varchar(255) | yes | — | — |
| body | longtext | yes | — | rich text tersanitasi |
| is_visible | boolean | no | true | visibility section |
| created_at / updated_at | timestamp | yes | — | — |

## E. Produk

### product_categories
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| status | varchar(255) | no | MUL, 'draft' | draft / published |
| created_at / updated_at / deleted_at | timestamp | yes | — | soft delete |

### product_category_translations
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| product_category_id | bigint unsigned | no | FK CASCADE, UQ(product_category_id, locale) | — |
| locale | varchar(10) | no | UQ(locale, slug) | — |
| name | varchar(255) | no | — | — |
| slug | varchar(255) | no | — | — |
| created_at / updated_at | timestamp | yes | — | — |

### products
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| product_category_id | bigint unsigned | no | FK RESTRICT | kategori wajib |
| sku | varchar(255) | yes | UNI | kode produk |
| status | varchar(255) | no | MUL, 'draft' | draft / published / archived |
| is_featured | boolean | no | false | produk unggulan |
| sort_order | int unsigned | no | 0 | urutan katalog |
| published_at | timestamp | yes | — | — |
| created_at / updated_at / deleted_at | timestamp | yes | — | soft delete |

### product_translations
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| product_id | bigint unsigned | no | FK CASCADE, UQ(product_id, locale) | — |
| locale | varchar(10) | no | UQ(locale, slug) | — |
| name | varchar(255) | no | — | — |
| slug | varchar(255) | no | — | — |
| short_description | text | yes | — | — |
| description | longtext | yes | — | deskripsi lengkap (tersanitasi) |
| functions | longtext | yes | — | fungsi produk (tersanitasi) |
| applications | longtext | yes | — | aplikasi produk (tersanitasi) |
| seo_title | varchar(255) | yes | — | SEO title per locale |
| seo_description | varchar(500) | yes | — | meta description per locale |
| og_image_id | bigint unsigned | yes | FK media_assets SET NULL | OG image dari Media Library |
| created_at / updated_at | timestamp | yes | — | — |

### product_documents
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| product_id | bigint unsigned | no | FK CASCADE, MUL(position) | — |
| media_asset_id | bigint unsigned | yes | FK media_assets SET NULL | file dari Media Library |
| type | varchar(50) | no | MUL, 'datasheet' | datasheet / manual / sertifikat |
| label | varchar(255) | yes | — | label tautan unduhan |
| position | int unsigned | no | 0 | urutan tampil |
| created_at / updated_at | timestamp | yes | — | — |

### product_media
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| product_id | bigint unsigned | no | FK CASCADE, UQ(product_id, media_asset_id) | — |
| media_asset_id | bigint unsigned | no | FK CASCADE | — |
| position | int unsigned | no | 0 | urutan tampil (MUL komposit) |
| is_primary | boolean | no | false | gambar utama |
| created_at / updated_at | timestamp | yes | — | — |

### product_specifications
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| product_id | bigint unsigned | no | FK CASCADE, MUL(product_id, sort_order) | — |
| sort_order | int | no | 0 | urutan spesifikasi |
| unit | varchar(50) | yes | — | satuan nilai (mis. mm, kg) |
| created_at / updated_at | timestamp | yes | — | — |

### product_specification_translations
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| product_specification_id | bigint unsigned | no | FK `pst_spec_fk` CASCADE, UQ `pst_locale_unique` | — |
| locale | varchar(10) | no | bagian dari UQ | — |
| label | varchar(255) | no | — | mis. "Dimensi" |
| value | text | yes | — | nilai spesifikasi |
| created_at / updated_at | timestamp | yes | — | — |

## F. Fasilitas

### facilities
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| status | varchar(255) | no | MUL, 'draft' | draft / published / archived |
| sort_order | int unsigned | no | 0 | urutan tampil |
| published_at | timestamp | yes | — | — |
| created_at / updated_at / deleted_at | timestamp | yes | — | soft delete |

### facility_translations
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| facility_id | bigint unsigned | no | FK CASCADE, UQ(facility_id, locale) | — |
| locale | varchar(10) | no | UQ(locale, slug) | — |
| name | varchar(255) | no | — | — |
| slug | varchar(255) | no | — | — |
| summary | text | yes | — | ringkasan |
| description | text | yes | — | body rich text (tersanitasi) |
| created_at / updated_at | timestamp | yes | — | — |

### facility_media
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| facility_id | bigint unsigned | no | FK CASCADE, UQ(facility_id, media_asset_id) | — |
| media_asset_id | bigint unsigned | no | FK CASCADE | — |
| position | int unsigned | no | 0 | urutan (MUL komposit) |
| created_at / updated_at | timestamp | yes | — | — |

## G. Galeri

### gallery_categories
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| status | varchar(255) | no | MUL, 'draft' | draft / published |
| created_at / updated_at / deleted_at | timestamp | yes | — | soft delete |

### gallery_category_translations
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| gallery_category_id | bigint unsigned | no | FK CASCADE, UQ(gallery_category_id, locale) | — |
| locale | varchar(10) | no | UQ(locale, slug) | — |
| name | varchar(255) | no | — | — |
| slug | varchar(255) | no | — | — |
| created_at / updated_at | timestamp | yes | — | — |

### gallery_items
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| gallery_category_id | bigint unsigned | yes | FK SET NULL | boleh tanpa kategori |
| media_asset_id | bigint unsigned | yes | FK SET NULL | gambar utama; konten tetap bila media dihapus |
| alt_override | varchar(255) | yes | — | alt text override item |
| taken_at | date | yes | — | tanggal foto (opsional) |
| position | int unsigned | no | 0 | urutan tampil |
| status | varchar(255) | no | MUL, 'draft' | draft / published / archived |
| published_at | timestamp | yes | — | — |
| created_at / updated_at / deleted_at | timestamp | yes | — | soft delete |

### gallery_item_translations
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| gallery_item_id | bigint unsigned | no | FK CASCADE, UQ(gallery_item_id, locale) | — |
| locale | varchar(10) | no | UQ(locale, slug) | — |
| title | varchar(255) | no | — | — |
| slug | varchar(255) | no | — | — |
| caption | text | yes | — | — |
| created_at / updated_at | timestamp | yes | — | — |

## H. Navigasi & Pengaturan

### menus
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| location | varchar(255) | no | UNI | mis. primary, footer |
| created_at / updated_at | timestamp | yes | — | — |

### menu_items
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| menu_id | bigint unsigned | no | FK CASCADE, MUL(menu_id, position) | — |
| parent_id | bigint unsigned | yes | FK menu_items SET NULL | sub-menu (1 level+) |
| page_id | bigint unsigned | yes | FK pages SET NULL | tautan ke halaman CMS |
| title | varchar(255) | no | — | teks menu (non-lokal, sementara) |
| url | varchar(255) | yes | — | URL eksternal/kustom (salah satu dari page_id/url) |
| opens_in_new_tab | boolean | no | false | — |
| position | int unsigned | no | 0 | urutan dalam menu |
| created_at / updated_at | timestamp | yes | — | — |

### site_settings
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| key | varchar(255) | no | UNI | kunci pengaturan |
| value | text | yes | — | **dilarang menyimpan secret di sini** |
| created_at / updated_at | timestamp | yes | — | — |

## I. Pesan, Redirect, Audit

### contact_messages
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| name | varchar(255) | no | — | — |
| email | varchar(255) | no | — | — |
| phone | varchar(255) | yes | — | — |
| subject | varchar(255) | yes | — | — |
| message | text | no | — | — |
| ip_address | varchar(45) | yes | — | — |
| is_read | boolean | no | MUL, false | penanda dibaca admin |
| created_at / updated_at | timestamp | yes | — | — |

### redirects
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| from_path | varchar(255) | no | UNI | path lama (tanpa domain) |
| to_path | varchar(255) | no | — | path tujuan |
| status_code | smallint unsigned | no | 301 | kode HTTP redirect |
| created_at / updated_at | timestamp | yes | — | — |

### audit_logs
| Kolom | Tipe | Null | Key/Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | PRI, auto | — |
| user_id | bigint unsigned | yes | FK users SET NULL | pelaku (bila ada) |
| action | varchar(255) | no | MUL | mis. product.update |
| auditable_type | varchar(255) | **yes** | MUL(auditable_type, auditable_id) | class entitas (morph); NULL untuk aksi tanpa model (login dsb.) |
| auditable_id | bigint unsigned | **yes** | | id entitas (morph) |
| old_values | json | yes | — | snapshot sebelum perubahan |
| new_values | json | yes | — | snapshot sesudah perubahan |
| ip_address | varchar(45) | yes | — | — |
| created_at / updated_at | timestamp | yes | — | — |


