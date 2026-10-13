<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seluruh tabel inti hasil migration tersedia setelah migrate dari database kosong.
     */
    public function test_all_core_tables_exist_after_migration(): void
    {
        $tables = [
            // Tabel standar Laravel
            'users',
            'password_reset_tokens',
            'sessions',
            'cache',
            'cache_locks',
            'jobs',
            'migrations',
            // RBAC
            'roles',
            'permissions',
            'role_user',
            'permission_role',
            // Media
            'media_assets',
            'media_variants',
            // CMS
            'pages',
            'page_translations',
            // Produk
            'product_categories',
            'product_category_translations',
            'products',
            'product_translations',
            'product_media',
            'product_specifications',
            'product_specification_translations',
            // Fasilitas
            'facilities',
            'facility_translations',
            'facility_media',
            // Galeri
            'gallery_categories',
            'gallery_category_translations',
            'gallery_items',
            'gallery_item_translations',
            // Navigasi & pengaturan
            'menus',
            'menu_items',
            'site_settings',
            // Lain-lain
            'contact_messages',
            'redirects',
            'audit_logs',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel hilang: {$table}");
        }
    }

    /**
     * Kolom wajib ada pada tabel terjemahan: locale, unique(locale, slug).
     */
    public function test_translation_tables_have_locale_and_slug(): void
    {
        $translationTables = [
            'page_translations',
            'product_category_translations',
            'product_translations',
            'facility_translations',
            'gallery_category_translations',
            'gallery_item_translations',
        ];

        foreach ($translationTables as $table) {
            $this->assertTrue(Schema::hasColumn($table, 'locale'), "{$table}.locale hilang");
            $this->assertTrue(Schema::hasColumn($table, 'slug'), "{$table}.slug hilang");
        }
    }

    /**
     * Kolom status tersedia pada konten publik (aturan: publik hanya published).
     */
    public function test_public_content_tables_have_status_column(): void
    {
        $contentTables = [
            'pages',
            'product_categories',
            'products',
            'facilities',
            'gallery_categories',
            'gallery_items',
        ];

        foreach ($contentTables as $table) {
            $this->assertTrue(Schema::hasColumn($table, 'status'), "{$table}.status hilang");
        }
    }
}
