<?php

namespace Tests\Feature\Admin;

use App\Models\MediaAsset;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\MenuService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $contentAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole(Role::where('name', 'super_admin')->first());

        $this->contentAdmin = User::factory()->create();
        $this->contentAdmin->assignRole(Role::where('name', 'content_admin')->first());
    }

    private function makeAsset(string $name = 'logo.png', string $mime = 'image/png'): MediaAsset
    {
        return MediaAsset::create([
            'disk' => 'public',
            'path' => 'media/2026/10/m_'.$name,
            'original_name' => $name,
            'mime_type' => $mime,
            'size' => 100,
        ]);
    }

    private function makeLivePage(string $slugId = 'tentang', string $slugEn = 'about'): Page
    {
        $page = Page::create(['status' => 'published', 'published_at' => now()->subHour()]);
        $page->translations()->create(['locale' => 'id', 'title' => 'Tentang', 'slug' => $slugId]);
        $page->translations()->create(['locale' => 'en', 'title' => 'About', 'slug' => $slugEn]);

        return $page;
    }

    // --- Pengaturan ---

    public function test_settings_requires_permission_and_saves_contact(): void
    {
        $this->get(route('admin.settings.index'))->assertRedirect(route('login'));

        // content_admin interim tidak memegang settings.update.
        $this->actingAs($this->contentAdmin)->get(route('admin.settings.index'))->assertForbidden();

        $this->actingAs($this->superAdmin)->get(route('admin.settings.index'))->assertOk();

        $this->actingAs($this->superAdmin)->put(route('admin.settings.update'), [
            'settings' => [
                'contact_phone' => '+62 21 555 0100',
                'contact_email' => 'info@primainti.test',
                'address' => 'Jl. Industri No. 1',
            ],
        ])->assertRedirect(route('admin.settings.index'));

        $this->assertEquals('+62 21 555 0100', SiteSetting::where('key', 'contact_phone')->first()->value);
    }

    public function test_unknown_keys_are_ignored_and_cannot_store_secrets(): void
    {
        $this->actingAs($this->superAdmin)->put(route('admin.settings.update'), [
            'settings' => [
                'contact_phone' => '123',
                'password' => 'rahasia',
                'api_key' => 'key-123',
                'smtp_password' => 'x',
            ],
        ])->assertRedirect();

        $this->assertNull(SiteSetting::where('key', 'password')->first());
        $this->assertNull(SiteSetting::where('key', 'api_key')->first());
        $this->assertNull(SiteSetting::where('key', 'smtp_password')->first());
        $this->assertNotNull(SiteSetting::where('key', 'contact_phone')->first());
    }

    public function test_logo_must_be_an_image_and_urls_are_validated(): void
    {
        $pdf = $this->makeAsset('doc.pdf', 'application/pdf');

        $this->actingAs($this->superAdmin)->put(route('admin.settings.update'), [
            'settings' => ['logo_id' => $pdf->id],
        ])->assertSessionHasErrors('settings.logo_id');

        $this->actingAs($this->superAdmin)->put(route('admin.settings.update'), [
            'settings' => ['social_facebook' => 'bukan-url'],
        ])->assertSessionHasErrors('settings.social_facebook');

        $this->actingAs($this->superAdmin)->put(route('admin.settings.update'), [
            'settings' => ['cta_url' => 'javascript:alert(1)'],
        ])->assertSessionHasErrors('settings.cta_url');
    }

    // --- Menu admin ---

    public function test_content_admin_can_manage_menu_items(): void
    {
        $menu = app(MenuService::class)->menu('header');

        $this->actingAs($this->contentAdmin)->post(route('admin.menu-items.store'), [
            'menu_id' => $menu->id,
            'parent_id' => null,
            'title' => 'Beranda',
            'translations' => ['en' => ['title' => 'Home']],
            'link_type' => 'url',
            'url' => '/id/produk',
            'position' => 0,
        ])->assertRedirect(route('admin.menus.edit', $menu->id));

        $this->assertEquals(1, $menu->items()->count());
        $this->assertEquals('Home', MenuItem::firstOrFail()->translations->firstWhere('locale', 'en')->title);
    }

    public function test_dangerous_and_empty_urls_are_rejected(): void
    {
        $menu = app(MenuService::class)->menu('header');

        $base = [
            'menu_id' => $menu->id,
            'title' => 'Jahat',
            'link_type' => 'url',
            'position' => 0,
        ];

        $this->actingAs($this->contentAdmin)
            ->post(route('admin.menu-items.store'), array_merge($base, ['url' => 'javascript:alert(1)']))
            ->assertSessionHasErrors('url');

        $this->actingAs($this->contentAdmin)
            ->post(route('admin.menu-items.store'), array_merge($base, ['url' => '']))
            ->assertSessionHasErrors('url');

        $this->assertEquals(0, $menu->items()->count());
    }

    public function test_menu_depth_is_limited_and_parent_must_match_menu(): void
    {
        $header = app(MenuService::class)->menu('header');
        $footer = app(MenuService::class)->menu('footer');

        $parent = $header->items()->create([
            'title' => 'Induk', 'link_type' => 'url', 'url' => '/id/produk', 'position' => 0,
        ]);
        $child = $header->items()->create([
            'title' => 'Anak', 'link_type' => 'url', 'url' => '/id/galeri', 'position' => 1, 'parent_id' => $parent->id,
        ]);

        // Cucu (level 3) ditolak.
        $this->actingAs($this->contentAdmin)->post(route('admin.menu-items.store'), [
            'menu_id' => $header->id,
            'parent_id' => $child->id,
            'title' => 'Cucu',
            'link_type' => 'url',
            'url' => '/id/fasilitas',
        ])->assertSessionHasErrors('parent_id');

        // Induk beda menu ditolak.
        $this->actingAs($this->contentAdmin)->post(route('admin.menu-items.store'), [
            'menu_id' => $footer->id,
            'parent_id' => $parent->id,
            'title' => 'Salah',
            'link_type' => 'url',
            'url' => '/id/produk',
        ])->assertSessionHasErrors('parent_id');

        // Diri sendiri sebagai induk ditolak.
        $this->actingAs($this->contentAdmin)->put(route('admin.menu-items.update', $parent), [
            'menu_id' => $header->id,
            'parent_id' => $parent->id,
            'title' => 'Induk',
            'link_type' => 'url',
            'url' => '/id/produk',
        ])->assertSessionHasErrors('parent_id');
    }

    public function test_route_target_is_restricted_to_allowlist(): void
    {
        $menu = app(MenuService::class)->menu('header');

        $this->actingAs($this->contentAdmin)->post(route('admin.menu-items.store'), [
            'menu_id' => $menu->id,
            'title' => 'Rute',
            'link_type' => 'route',
            'route_name' => 'public.products.index',
        ])->assertRedirect();

        $this->actingAs($this->contentAdmin)->post(route('admin.menu-items.store'), [
            'menu_id' => $menu->id,
            'title' => 'Nakal',
            'link_type' => 'route',
            'route_name' => 'admin.users.index',
        ])->assertSessionHasErrors('route_name');
    }

    public function test_deleting_parent_keeps_children_as_top_level(): void
    {
        $menu = app(MenuService::class)->menu('header');

        $parent = $menu->items()->create(['title' => 'Induk', 'link_type' => 'url', 'url' => '/id/produk']);
        $child = $menu->items()->create([
            'title' => 'Anak', 'link_type' => 'url', 'url' => '/id/galeri', 'parent_id' => $parent->id,
        ]);

        $this->actingAs($this->contentAdmin)
            ->delete(route('admin.menu-items.destroy', $parent))
            ->assertRedirect();

        $this->assertNull($child->fresh()->parent_id);
        $this->assertNotNull(MenuItem::find($child->id));
    }

    // --- Render publik ---

    public function test_header_and_footer_read_cms_and_hide_broken_links(): void
    {
        $service = app(MenuService::class);
        $header = $service->menu('header');
        $footer = $service->menu('footer');

        $page = $this->makeLivePage();

        $header->items()->create([
            'title' => 'Tentang', 'link_type' => 'page', 'page_id' => $page->id, 'position' => 0,
        ]);

        $draft = Page::create(['status' => 'draft']);
        $draft->translations()->create(['locale' => 'id', 'title' => 'Draf', 'slug' => 'draf']);
        $header->items()->create([
            'title' => 'Draf', 'link_type' => 'page', 'page_id' => $draft->id, 'position' => 1,
        ]);

        $footer->items()->create([
            'title' => 'Katalog', 'link_type' => 'route', 'route_name' => 'public.products.index', 'position' => 0,
        ]);

        MenuService::flushTrees();

        $response = $this->get('/id/tentang');
        $response->assertOk();
        $response->assertSee('Tentang', false);
        $response->assertDontSee('>Draf<', false);
        $response->assertSee('/id/produk', false);

        // Admin menandai target rusak.
        $this->actingAs($this->contentAdmin)
            ->get(route('admin.menus.edit', $header))
            ->assertOk()
            ->assertSee('rusak');
    }

    public function test_category_menu_links_to_filtered_catalog(): void
    {
        $service = app(MenuService::class);
        $header = $service->menu('header');

        $category = ProductCategory::create(['status' => 'published']);
        $category->translations()->create(['locale' => 'id', 'name' => 'Pompa', 'slug' => 'pompa']);

        $header->items()->create([
            'title' => 'Pompa', 'link_type' => 'product_category',
            'product_category_id' => $category->id, 'position' => 0,
        ]);

        MenuService::flushTrees();

        $this->makeLivePage();

        $this->get('/id/tentang')->assertOk()->assertSee('/id/produk?kategori=pompa', false);
    }

    public function test_contact_is_consistent_in_header_and_footer(): void
    {
        $this->makeLivePage();

        SiteSetting::updateOrCreate(['key' => 'contact_phone'], ['value' => '+62 21 555 0100']);
        SiteSetting::updateOrCreate(['key' => 'contact_email'], ['value' => 'info@primainti.test']);

        $response = $this->get('/id/tentang')->assertOk();

        // Header (tel:) + footer (teks + mailto:).
        $response->assertSee('tel:+62215550100', false);
        $response->assertSee('+62 21 555 0100');
        $response->assertSee('mailto:info@primainti.test', false);
    }

    public function test_language_switcher_points_to_matching_translation(): void
    {
        $this->makeLivePage('tentang', 'about');

        // ID → padanan EN.
        $this->get('/id/tentang')->assertOk()->assertSee('/en/about', false);

        // EN → padanan ID.
        $this->get('/en/about')->assertOk()->assertSee('/id/tentang', false);
    }

    public function test_switcher_falls_back_when_translation_missing(): void
    {
        $page = Page::create(['status' => 'published', 'published_at' => now()->subHour()]);
        $page->translations()->create(['locale' => 'en', 'title' => 'Only English', 'slug' => 'only-english']);

        // Konten yang tampil berbahasa EN → switcher menandai EN sebagai aktif.
        $this->get('/id/only-english')
            ->assertOk()
            ->assertSee('Only English')
            ->assertSee('aria-current="true"', false);
    }

    public function test_catalog_switcher_keeps_query(): void
    {
        $this->get('/id/produk?q=pompa')->assertOk()->assertSee('/en/produk?q=pompa', false);
    }
}
