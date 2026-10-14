<?php

namespace Tests\Feature\Admin;

use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $contentAdmin;

    private User $editor;

    private ProductCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();

        $this->contentAdmin = User::factory()->create();
        $this->contentAdmin->assignRole(Role::where('name', 'content_admin')->first());

        $this->editor = User::factory()->create();
        $this->editor->assignRole(Role::where('name', 'editor')->first());

        $this->category = ProductCategory::create(['status' => 'published']);
        $this->category->translations()->create(['locale' => 'id', 'name' => 'Pompa', 'slug' => 'pompa']);
        $this->category->translations()->create(['locale' => 'en', 'name' => 'Pumps', 'slug' => 'pumps']);
    }

    /** @param array<string, mixed> $overrides */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'product_category_id' => $this->category->id,
            'sku' => 'PMP-001',
            'status' => 'draft',
            'is_featured' => 0,
            'sort_order' => 0,
            'published_at' => null,
            'translations' => [
                'id' => [
                    'name' => 'Pompa Sentrifugal',
                    'slug' => 'pompa-sentrifugal',
                    'short_description' => 'Pompa andal.',
                    'description' => '<p>Deskripsi <strong>lengkap</strong></p>',
                    'functions' => '<p>Fungsi utama</p>',
                    'applications' => '<p>Irigasi</p>',
                    'seo_title' => null,
                    'seo_description' => null,
                    'og_image_id' => null,
                ],
            ],
        ], $overrides);
    }

    private function makeAsset(string $name = 'foto.jpg', string $mime = 'image/jpeg'): MediaAsset
    {
        return MediaAsset::create([
            'disk' => 'public',
            'path' => 'media/2026/10/m_'.$name,
            'original_name' => $name,
            'mime_type' => $mime,
            'size' => 100,
        ]);
    }

    private function makeLiveProduct(string $slug = 'pompa-sentrifugal', array $overrides = []): Product
    {
        $product = Product::create(array_merge([
            'product_category_id' => $this->category->id,
            'sku' => 'PMP-'.strtoupper(substr(md5($slug), 0, 6)),
            'status' => 'published',
            'published_at' => now()->subHour(),
        ], $overrides));

        $product->translations()->create([
            'locale' => 'id',
            'name' => 'Pompa Sentrifugal',
            'slug' => $slug,
            'short_description' => 'Pompa andal.',
            'description' => '<p>Deskripsi lengkap.</p>',
        ]);

        return $product;
    }

    // --- Publikasi & otorisasi ---

    public function test_editor_can_create_draft_but_cannot_publish_product(): void
    {
        $this->actingAs($this->editor)
            ->post(route('admin.products.store'), $this->payload())
            ->assertRedirect();

        $this->assertEquals('draft', Product::firstOrFail()->status);

        $this->actingAs($this->editor)
            ->post(route('admin.products.store'), $this->payload([
                'sku' => 'PMP-002',
                'status' => 'published',
                'translations' => ['id' => ['name' => 'Lain', 'slug' => 'lain']],
            ]))
            ->assertForbidden();
    }

    public function test_duplicate_product_slug_is_rejected(): void
    {
        $this->makeLiveProduct();

        $this->actingAs($this->contentAdmin)
            ->post(route('admin.products.store'), $this->payload(['sku' => 'PMP-999']))
            ->assertSessionHasErrors('translations.id.slug');
    }

    public function test_description_and_functions_are_sanitized(): void
    {
        $this->actingAs($this->contentAdmin)->post(route('admin.products.store'), $this->payload([
            'translations' => ['id' => [
                'name' => 'Uji',
                'slug' => 'uji',
                'description' => '<p>OK</p><script>alert(1)</script>',
                'functions' => '<p>F</p><a href="javascript:alert(2)">x</a>',
            ]],
        ]))->assertRedirect();

        $translation = Product::firstOrFail()->translations->first();

        $this->assertStringNotContainsString('<script>', $translation->description);
        $this->assertStringNotContainsString('javascript:', $translation->functions);
    }

    // --- Spesifikasi ---

    public function test_specifications_with_label_value_unit_are_saved_and_updated(): void
    {
        $this->actingAs($this->contentAdmin)->post(route('admin.products.store'), $this->payload([
            'specs' => [
                ['sort_order' => 1, 'unit' => 'mm', 'translations' => [
                    'id' => ['label' => 'Diameter', 'value' => '100'],
                    'en' => ['label' => 'Diameter', 'value' => '100'],
                ]],
                ['sort_order' => 0, 'unit' => null, 'translations' => [
                    'id' => ['label' => 'Daya', 'value' => '5,5 kW'],
                ]],
                // Baris kosong diabaikan.
                ['sort_order' => 2, 'translations' => ['id' => ['label' => '', 'value' => '']]],
            ],
        ]))->assertRedirect();

        $product = Product::firstOrFail();
        $this->assertEquals(2, $product->specifications()->count());

        $spec = $product->specifications()->where('unit', 'mm')->firstOrFail();
        $this->assertEquals('Diameter', $spec->translations->firstWhere('locale', 'id')->label);

        // Update: hapus satu, ubah satunya.
        $specs = $product->specifications()->ordered()->get();

        $this->actingAs($this->contentAdmin)->put(route('admin.products.update', $product), $this->payload([
            'specs' => [
                ['id' => $specs[0]->id, 'sort_order' => 0, 'unit' => 'kW', 'translations' => [
                    'id' => ['label' => 'Daya', 'value' => '7,5 kW'],
                ]],
                ['id' => $specs[1]->id, '_remove' => 1],
            ],
        ]))->assertRedirect();

        $remaining = $product->fresh()->specifications;
        $this->assertCount(1, $remaining);
        $this->assertEquals('kW', $remaining->first()->unit);
        $this->assertEquals('7,5 kW', $remaining->first()->translations->firstWhere('locale', 'id')->value);
    }

    // --- Media & dokumen ---

    public function test_gallery_references_media_without_reupload_and_sets_primary(): void
    {
        $a = $this->makeAsset('a.jpg');
        $b = $this->makeAsset('b.jpg');

        $this->actingAs($this->contentAdmin)->post(route('admin.products.store'), $this->payload([
            'media' => [
                ['media_asset_id' => $a->id, 'position' => 1],
                ['media_asset_id' => $b->id, 'position' => 0],
            ],
            'primary_media_asset_id' => $b->id,
        ]))->assertRedirect();

        $product = Product::firstOrFail();

        // Satu file dipakai tanpa duplikat baris media_assets.
        $this->assertEquals(2, MediaAsset::count());
        $this->assertEquals(2, $product->media()->count());
        $this->assertEquals($b->id, $product->primaryMedia()->media_asset_id);
    }

    public function test_deleting_product_keeps_media_files(): void
    {
        $a = $this->makeAsset('a.jpg');

        $this->actingAs($this->contentAdmin)->post(route('admin.products.store'), $this->payload([
            'media' => [['media_asset_id' => $a->id, 'position' => 0]],
            'primary_media_asset_id' => $a->id,
        ]))->assertRedirect();

        $product = Product::firstOrFail();
        $product->forceDelete();

        $this->assertNotNull(MediaAsset::find($a->id));
    }

    public function test_media_referenced_by_product_cannot_be_deleted(): void
    {
        $a = $this->makeAsset('a.jpg');

        $this->actingAs($this->contentAdmin)->post(route('admin.products.store'), $this->payload([
            'media' => [['media_asset_id' => $a->id, 'position' => 0]],
        ]))->assertRedirect();

        $this->actingAs($this->contentAdmin)
            ->delete(route('admin.media.destroy', $a))
            ->assertSessionHasErrors('media');
    }

    public function test_documents_are_saved_and_shown_only_on_live_product(): void
    {
        $pdf = $this->makeAsset('datasheet.pdf', 'application/pdf');

        $this->actingAs($this->contentAdmin)->post(route('admin.products.store'), $this->payload([
            'status' => 'published',
            'documents' => [
                ['media_asset_id' => $pdf->id, 'type' => 'datasheet', 'label' => 'Datasheet Pompa', 'position' => 0],
            ],
        ]))->assertRedirect();

        $product = Product::firstOrFail();
        $this->assertEquals(1, $product->documents()->count());

        $this->get('/id/produk/pompa-sentrifugal')
            ->assertOk()
            ->assertSee('Datasheet Pompa')
            ->assertSee('datasheet.pdf');

        // Produk draft: dokumen tidak tampil publik.
        $product->update(['status' => 'draft']);
        $this->get('/id/produk/pompa-sentrifugal')->assertNotFound();
    }

    // --- Katalog & detail publik ---

    public function test_catalog_lists_search_filters_and_paginates(): void
    {
        $this->makeLiveProduct('pompa-sentrifugal');
        $this->makeLiveProduct('pompa-celup');

        $other = ProductCategory::create(['status' => 'published']);
        $other->translations()->create(['locale' => 'id', 'name' => 'Katup', 'slug' => 'katup']);
        $valve = Product::create([
            'product_category_id' => $other->id, 'status' => 'published', 'published_at' => now()->subHour(),
        ]);
        $valve->translations()->create(['locale' => 'id', 'name' => 'Katup Gerbang', 'slug' => 'katup-gerbang']);

        // Semua tampil.
        $this->get('/id/produk')->assertOk()->assertSee('Pompa Sentrifugal')->assertSee('Katup Gerbang');

        // Pencarian.
        $this->get('/id/produk?q=katup')->assertOk()->assertSee('Katup Gerbang')->assertDontSee('Pompa Sentrifugal');

        // Filter kategori.
        $this->get('/id/produk?kategori=pompa')->assertOk()->assertSee('Pompa Sentrifugal')->assertDontSee('Katup Gerbang');

        // Tidak ada hasil.
        $this->get('/id/produk?q=tidakada')->assertOk()->assertSee('Tidak ada produk yang cocok');
    }

    public function test_draft_product_is_hidden_from_catalog_and_detail(): void
    {
        $this->makeLiveProduct('tampil');
        $hidden = Product::create(['product_category_id' => $this->category->id, 'status' => 'draft']);
        $hidden->translations()->create(['locale' => 'id', 'name' => 'Sembunyi', 'slug' => 'sembunyi']);

        $this->get('/id/produk')->assertOk()->assertSee('Pompa Sentrifugal')->assertDontSee('Sembunyi');
        $this->get('/id/produk/sembunyi')->assertNotFound();
    }

    public function test_draft_category_hides_its_products(): void
    {
        $draftCategory = ProductCategory::create(['status' => 'draft']);
        $draftCategory->translations()->create(['locale' => 'id', 'name' => 'Rahasia', 'slug' => 'rahasia']);

        $product = Product::create([
            'product_category_id' => $draftCategory->id, 'status' => 'published', 'published_at' => now()->subHour(),
        ]);
        $product->translations()->create(['locale' => 'id', 'name' => 'Produk Rahasia', 'slug' => 'produk-rahasia']);

        $this->get('/id/produk')->assertOk()->assertDontSee('Produk Rahasia');
        $this->get('/id/produk/produk-rahasia')->assertNotFound();
    }

    public function test_detail_shows_specs_functions_and_related(): void
    {
        $product = $this->makeLiveProduct();
        $product->translations()->update([
            'functions' => '<p>Fungsi utama</p>',
            'applications' => '<p>Irigasi</p>',
        ]);

        $spec = $product->specifications()->create(['sort_order' => 0, 'unit' => 'mm']);
        $spec->translations()->create(['locale' => 'id', 'label' => 'Diameter', 'value' => '100']);

        $this->makeLiveProduct('pompa-celup');

        $this->get('/id/produk/pompa-sentrifugal')
            ->assertOk()
            ->assertSee('Fungsi utama')
            ->assertSee('Irigasi')
            ->assertSee('Diameter')
            ->assertSee('100 mm')
            ->assertSee('Produk Terkait')
            ->assertSee('Minta Penawaran');
    }

    public function test_preview_shows_draft_to_admin(): void
    {
        $draft = Product::create(['product_category_id' => $this->category->id, 'status' => 'draft']);
        $draft->translations()->create(['locale' => 'id', 'name' => 'Draf Produk', 'slug' => 'draf-produk']);

        $this->get(route('admin.products.preview', $draft))->assertRedirect(route('login'));

        $this->actingAs($this->contentAdmin)
            ->get(route('admin.products.preview', $draft))
            ->assertOk()
            ->assertSee('Draf Produk')
            ->assertSee('Pratinjau');
    }

    public function test_product_trash_restore_and_force_delete(): void
    {
        $product = $this->makeLiveProduct();

        $this->actingAs($this->contentAdmin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->get('/id/produk/pompa-sentrifugal')->assertNotFound();

        $this->actingAs($this->contentAdmin)
            ->post(route('admin.products.restore', $product->id))
            ->assertRedirect();

        $this->get('/id/produk/pompa-sentrifugal')->assertOk();

        $this->actingAs($this->contentAdmin)
            ->delete(route('admin.products.force-destroy', $product->id))
            ->assertRedirect();

        $this->assertNull(Product::withTrashed()->find($product->id));
    }
}
