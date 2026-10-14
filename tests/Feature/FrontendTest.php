<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(): ProductCategory
    {
        $category = ProductCategory::create(['status' => 'published']);
        $category->translations()->create(['locale' => 'id', 'name' => 'Pompa', 'slug' => 'pompa']);

        return $category;
    }

    private function makeProduct(ProductCategory $category, string $name, string $slug, array $overrides = []): Product
    {
        $product = Product::create(array_merge([
            'product_category_id' => $category->id,
            'status' => 'published',
            'published_at' => now()->subHour(),
        ], $overrides));

        $product->translations()->create([
            'locale' => 'id', 'name' => $name, 'slug' => $slug, 'short_description' => 'Ringkasan.',
        ]);

        return $product;
    }

    public function test_empty_home_renders_brand_without_sections(): void
    {
        $response = $this->get('/id')->assertOk();

        $response->assertSee('Primainti');
        $response->assertDontSee('Produk Unggulan');
        $response->assertDontSee('Fasilitas Kami');
        $response->assertDontSee('Hubungi Kami');
    }

    public function test_home_shows_only_featured_live_products(): void
    {
        $category = $this->makeCategory();
        $this->makeProduct($category, 'Unggulan A', 'unggulan-a', ['is_featured' => true]);
        $this->makeProduct($category, 'Biasa B', 'biasa-b');
        $this->makeProduct($category, 'Draf Unggulan', 'draf-unggulan', ['is_featured' => true, 'status' => 'draft']);

        $response = $this->get('/id')->assertOk();

        $response->assertSee('Produk Unggulan');
        $response->assertSee('Unggulan A');
        $response->assertDontSee('Biasa B');
        $response->assertDontSee('Draf Unggulan');
    }

    public function test_home_shows_profile_facilities_and_contact_from_cms(): void
    {
        $page = Page::create(['status' => 'published', 'published_at' => now()->subHour()]);
        $page->translations()->create([
            'locale' => 'id', 'title' => 'Profil Primainti', 'slug' => 'tentang-kami', 'summary' => 'Sejak lama.',
        ]);

        $facility = Facility::create(['status' => 'published', 'published_at' => now()->subHour()]);
        $facility->translations()->create(['locale' => 'id', 'name' => 'Pabrik', 'slug' => 'pabrik']);

        SiteSetting::updateOrCreate(['key' => 'tagline_id'], ['value' => 'Mitra industri Anda']);
        SiteSetting::updateOrCreate(['key' => 'contact_phone'], ['value' => '+62 21 555 0100']);

        $response = $this->get('/id')->assertOk();

        $response->assertSee('Mitra industri Anda');
        $response->assertSee('Profil Primainti');
        $response->assertSee('Fasilitas Kami');
        $response->assertSee('Pabrik');
        $response->assertSee('Hubungi Kami');
        $response->assertSee('+62 21 555 0100');
    }

    public function test_home_hides_cta_band_without_settings(): void
    {
        $this->get('/id')->assertOk()->assertDontSee('Minta Penawaran Yuk');

        SiteSetting::updateOrCreate(['key' => 'cta_label_id'], ['value' => 'Minta Penawaran Yuk']);
        SiteSetting::updateOrCreate(['key' => 'cta_url'], ['value' => '/id/kontak']);

        \Illuminate\Support\Facades\Cache::forget(\App\Services\SettingsService::CACHE_KEY);

        $this->get('/id')->assertOk()->assertSee('Minta Penawaran Yuk');
    }

    public function test_contact_page_shows_settings_or_empty_state(): void
    {
        $this->get('/id/kontak')->assertOk()->assertSee('Informasi kontak belum tersedia.');

        SiteSetting::updateOrCreate(['key' => 'address'], ['value' => 'Jl. Industri No. 1']);
        SiteSetting::updateOrCreate(['key' => 'contact_email'], ['value' => 'info@primainti.test']);

        // Penulisan langsung ke DB melewati flush cache service.
        \Illuminate\Support\Facades\Cache::forget(\App\Services\SettingsService::CACHE_KEY);

        $this->get('/id/kontak')
            ->assertOk()
            ->assertSee('Jl. Industri No. 1')
            ->assertSee('info@primainti.test')
            ->assertDontSee('Informasi kontak belum tersedia.');
    }

    public function test_custom_404_page(): void
    {
        $this->get('/id/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan')
            ->assertSee('Beranda');
    }

    public function test_header_has_mobile_toggle_and_switcher(): void
    {
        $response = $this->get('/id')->assertOk();

        $response->assertSee('data-mobile-toggle', false);
        $response->assertSee('/en', false);
    }

    public function test_public_pages_share_design_system_classes(): void
    {
        $category = $this->makeCategory();
        $this->makeProduct($category, 'Unggulan A', 'unggulan-a', ['is_featured' => true]);

        foreach (['/id', '/id/produk', '/id/produk/unggulan-a', '/id/galeri', '/id/fasilitas', '/id/kontak'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get('/id/produk')->assertSee('section-title', false);
    }
}
