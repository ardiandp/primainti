<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Redirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeoSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(): ProductCategory
    {
        $category = ProductCategory::create(['status' => 'published']);
        $category->translations()->create(['locale' => 'id', 'name' => 'Pompa', 'slug' => 'pompa']);

        return $category;
    }

    private function makeProduct(ProductCategory $category, string $slug): Product
    {
        $product = Product::create([
            'product_category_id' => $category->id,
            'status' => 'published',
            'published_at' => now()->subHour(),
        ]);
        $product->translations()->create(['locale' => 'id', 'name' => 'P '.$slug, 'slug' => $slug]);

        $spec = $product->specifications()->create(['sort_order' => 0, 'unit' => 'mm']);
        $spec->translations()->create(['locale' => 'id', 'label' => 'Diameter', 'value' => '100']);

        $asset = MediaAsset::create([
            'disk' => 'public', 'path' => "media/2026/10/m_{$slug}.jpg",
            'original_name' => $slug.'.jpg', 'mime_type' => 'image/jpeg', 'size' => 100,
        ]);
        $product->media()->create(['media_asset_id' => $asset->id, 'position' => 0, 'is_primary' => true]);
        $product->documents()->create(['media_asset_id' => $asset->id, 'type' => 'datasheet', 'position' => 0]);

        return $product;
    }

    private function countQueries(callable $run): int
    {
        $count = 0;
        DB::listen(function () use (&$count) {
            $count++;
        });

        $run();

        return $count;
    }

    // --- Sitemap & robots ---

    public function test_sitemap_contains_only_live_urls(): void
    {
        $page = Page::create(['status' => 'published', 'published_at' => now()->subHour()]);
        $page->translations()->create(['locale' => 'id', 'title' => 'Tentang', 'slug' => 'tentang']);

        $draft = Page::create(['status' => 'draft']);
        $draft->translations()->create(['locale' => 'id', 'title' => 'Draf', 'slug' => 'draf']);

        $this->makeProduct($this->makeCategory(), 'pompa-a');

        $response = $this->get('/sitemap.xml')->assertOk();
        $this->assertStringContainsString('text/xml', $response->headers->get('Content-Type'));

        $response->assertSee('/id/tentang', false);
        $response->assertSee('/id/produk/pompa-a', false);
        $response->assertSee('/id/produk', false);
        $response->assertDontSee('/id/draf');
    }

    public function test_robots_disallows_admin_and_points_to_sitemap(): void
    {
        $response = $this->get('/robots.txt')->assertOk();
        $this->assertStringContainsString('text/plain', $response->headers->get('Content-Type'));

        $response->assertSee('Disallow: /admin', false);
        $response->assertSee('/sitemap.xml', false);
    }

    // --- Meta & JSON-LD ---

    public function test_canonical_og_and_jsonld_are_present(): void
    {
        $this->makeProduct($this->makeCategory(), 'pompa-a');

        $this->get('/id/produk')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="http://localhost:8000/id/produk">', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('"@type":"Organization"', false);

        $this->get('/id/produk/pompa-a')
            ->assertOk()
            ->assertSee('"@type":"Product"', false)
            ->assertSee('Lewati ke konten');
    }

    // --- Redirect ---

    public function test_mapped_urls_redirect_and_live_routes_untouched(): void
    {
        $this->makeProduct($this->makeCategory(), 'pompa-a');

        Redirect::create(['from_path' => '/produk-lama', 'to_path' => '/id/produk', 'status_code' => 301]);
        Redirect::create(['from_path' => '/promo', 'to_path' => '/id/galeri', 'status_code' => 302]);

        $this->get('/produk-lama')->assertRedirect('/id/produk')->assertStatus(301);
        $this->get('/promo')->assertRedirect('/id/galeri')->assertStatus(302);

        // Rute hidup tidak dibajak walau ada pemetaan yang sama.
        Redirect::create(['from_path' => '/id/produk', 'to_path' => '/id/galeri', 'status_code' => 301]);
        $this->get('/id/produk')->assertOk();

        // Tanpa pemetaan tetap 404.
        $this->get('/tidak-ada-di-mana pun')->assertNotFound();
    }

    // --- Header keamanan ---

    public function test_security_headers_are_present(): void
    {
        foreach (['/id', '/login'] as $url) {
            $response = $this->get($url)->assertOk();
            $this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'));
            $this->assertEquals('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
            $this->assertEquals('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
        }
    }

    // --- N+1 ---

    public function test_catalog_has_no_n_plus_one_queries(): void
    {
        $category = $this->makeCategory();
        $this->makeProduct($category, 'pompa-a');

        $withOne = $this->countQueries(fn () => $this->get('/id/produk')->assertOk());

        foreach (['b', 'c', 'd'] as $suffix) {
            $this->makeProduct($category, "pompa-{$suffix}");
        }

        $withFour = $this->countQueries(fn () => $this->get('/id/produk')->assertOk());

        // Tiga produk tambahan tidak boleh menambah query (eager loading).
        $this->assertEquals($withOne, $withFour);
    }

    public function test_detail_has_no_n_plus_one_queries(): void
    {
        $product = $this->makeProduct($this->makeCategory(), 'pompa-a');

        foreach ([1, 2] as $i) {
            $spec = $product->specifications()->create(['sort_order' => $i, 'unit' => 'kg']);
            $spec->translations()->create(['locale' => 'id', 'label' => "Bobot {$i}", 'value' => (string) $i]);
        }

        $extra = MediaAsset::create([
            'disk' => 'public', 'path' => 'media/2026/10/m_extra.jpg',
            'original_name' => 'extra.jpg', 'mime_type' => 'image/jpeg', 'size' => 100,
        ]);
        $product->media()->create(['media_asset_id' => $extra->id, 'position' => 1]);

        $count = $this->countQueries(fn () => $this->get('/id/produk/pompa-a')->assertOk());

        $this->assertLessThan(26, $count);
    }
}
