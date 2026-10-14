<?php

namespace Tests\Feature\Admin;

use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\MediaAsset;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GalleryManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $contentAdmin;

    private User $editor;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();

        $this->contentAdmin = User::factory()->create();
        $this->contentAdmin->assignRole(Role::where('name', 'content_admin')->first());

        $this->editor = User::factory()->create();
        $this->editor->assignRole(Role::where('name', 'editor')->first());
    }

    private function makeAsset(string $name = 'foto.jpg'): MediaAsset
    {
        return MediaAsset::create([
            'disk' => 'public',
            'path' => 'media/2026/10/m_'.$name,
            'original_name' => $name,
            'mime_type' => 'image/jpeg',
            'size' => 100,
            'alt' => 'Alt aset',
        ]);
    }

    private function makeCategory(string $slug = 'kegiatan', string $status = 'published'): GalleryCategory
    {
        $category = GalleryCategory::create(['status' => $status]);
        $category->translations()->create(['locale' => 'id', 'name' => 'Kegiatan', 'slug' => $slug]);

        return $category;
    }

    /** @param array<string, mixed> $overrides */
    private function itemPayload(MediaAsset $asset, array $overrides = []): array
    {
        return array_merge([
            'gallery_category_id' => null,
            'media_asset_id' => $asset->id,
            'alt_override' => null,
            'taken_at' => null,
            'position' => 0,
            'status' => 'draft',
            'published_at' => null,
            'translations' => [
                'id' => ['title' => 'Foto Kegiatan', 'slug' => 'foto-kegiatan', 'caption' => 'Caption foto.'],
            ],
        ], $overrides);
    }

    public function test_category_publish_gating_and_delete_protection(): void
    {
        $this->actingAs($this->editor)
            ->post(route('admin.gallery-categories.store'), [
                'status' => 'published',
                'translations' => ['id' => ['name' => 'X', 'slug' => 'x']],
            ])
            ->assertForbidden();

        $category = $this->makeCategory();

        $asset = $this->makeAsset();
        $item = GalleryItem::create([
            'gallery_category_id' => $category->id,
            'media_asset_id' => $asset->id,
            'status' => 'published',
            'published_at' => now()->subHour(),
        ]);
        $item->translations()->create(['locale' => 'id', 'title' => 'T', 'slug' => 't']);

        $this->actingAs($this->contentAdmin)
            ->delete(route('admin.gallery-categories.destroy', $category))
            ->assertSessionHasErrors('category');
    }

    public function test_item_requires_existing_media_and_reuses_it(): void
    {
        // Tanpa media → ditolak.
        $this->actingAs($this->contentAdmin)
            ->post(route('admin.gallery-items.store'), $this->itemPayload($this->makeAsset(), [
                'media_asset_id' => null,
            ]))
            ->assertSessionHasErrors('media_asset_id');

        // Satu media dipakai fasilitas + galeri tanpa duplikat file.
        $asset = MediaAsset::firstOrFail();

        \App\Models\Facility::create(['status' => 'published', 'published_at' => now()->subHour()])
            ->media()->create(['media_asset_id' => $asset->id, 'position' => 0]);

        $this->actingAs($this->contentAdmin)
            ->post(route('admin.gallery-items.store'), $this->itemPayload($asset, ['status' => 'published']))
            ->assertRedirect();

        $this->assertEquals(1, MediaAsset::count());
        $this->assertEquals(1, GalleryItem::count());
    }

    public function test_gallery_filter_works_and_draft_is_hidden(): void
    {
        $catA = $this->makeCategory('kegiatan');
        $catB = $this->makeCategory('produksi');

        $makeItem = function (GalleryCategory $category, string $title, string $slug) {
            $item = GalleryItem::create([
                'gallery_category_id' => $category->id,
                'media_asset_id' => $this->makeAsset($slug.'.jpg')->id,
                'status' => 'published',
                'published_at' => now()->subHour(),
            ]);
            $item->translations()->create(['locale' => 'id', 'title' => $title, 'slug' => $slug, 'caption' => $title]);

            return $item;
        };

        $makeItem($catA, 'Foto A', 'foto-a');
        $makeItem($catB, 'Foto B', 'foto-b');

        $draft = GalleryItem::create([
            'gallery_category_id' => $catA->id,
            'media_asset_id' => $this->makeAsset('draf.jpg')->id,
            'status' => 'draft',
        ]);
        $draft->translations()->create(['locale' => 'id', 'title' => 'Draf', 'slug' => 'draf']);

        $this->get('/id/galeri')->assertOk()->assertSee('Foto A')->assertSee('Foto B')->assertDontSee('Draf');

        $this->get('/id/galeri?kategori=kegiatan')
            ->assertOk()->assertSee('Foto A')->assertDontSee('Foto B');
    }

    public function test_draft_category_hides_its_items(): void
    {
        $category = $this->makeCategory('rahasia', 'draft');

        $item = GalleryItem::create([
            'gallery_category_id' => $category->id,
            'media_asset_id' => $this->makeAsset()->id,
            'status' => 'published',
            'published_at' => now()->subHour(),
        ]);
        $item->translations()->create(['locale' => 'id', 'title' => 'Sembunyi', 'slug' => 'sembunyi']);

        $this->get('/id/galeri')->assertOk()->assertDontSee('Sembunyi');
    }

    public function test_alt_override_lazy_loading_and_lightbox_markup(): void
    {
        $item = GalleryItem::create([
            'media_asset_id' => $this->makeAsset()->id,
            'alt_override' => 'Alt khusus item',
            'status' => 'published',
            'published_at' => now()->subHour(),
        ]);
        $item->translations()->create(['locale' => 'id', 'title' => 'T', 'slug' => 't-alt', 'caption' => 'Cap']);

        $response = $this->get('/id/galeri')->assertOk();

        $response->assertSee('alt="Alt khusus item"', false);
        $response->assertSee('loading="lazy"', false);
        $response->assertSee('role="dialog"', false);
        $response->assertSee('data-lightbox', false);
    }

    public function test_item_trash_restore_and_keeps_media(): void
    {
        $asset = $this->makeAsset();

        $this->actingAs($this->contentAdmin)
            ->post(route('admin.gallery-items.store'), $this->itemPayload($asset, ['status' => 'published']))
            ->assertRedirect();

        $item = GalleryItem::firstOrFail();

        $this->actingAs($this->contentAdmin)
            ->delete(route('admin.gallery-items.destroy', $item))
            ->assertRedirect(route('admin.gallery-items.index'));

        // Grid menampilkan caption (fallback ke judul bila caption kosong).
        $this->get('/id/galeri')->assertOk()->assertDontSee('Caption foto.');

        $this->actingAs($this->contentAdmin)
            ->post(route('admin.gallery-items.restore', $item->id))
            ->assertRedirect();

        $this->get('/id/galeri')->assertOk()->assertSee('Caption foto.');

        $item->forceDelete();
        $this->assertNotNull(MediaAsset::find($asset->id));
    }
}
