<?php

namespace Tests\Feature\Admin;

use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $contentAdmin;

    private User $editor;

    private User $noAccess;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole(Role::where('name', 'super_admin')->first());

        $this->contentAdmin = User::factory()->create();
        $this->contentAdmin->assignRole(Role::where('name', 'content_admin')->first());

        $this->editor = User::factory()->create();
        $this->editor->assignRole(Role::where('name', 'editor')->first());

        $this->noAccess = User::factory()->create();
    }

    /** @param array<string, mixed> $overrides */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'template' => 'default',
            'status' => 'draft',
            'published_at' => null,
            'translations' => [
                'id' => [
                    'title' => 'Tentang Kami',
                    'slug' => 'tentang-kami',
                    'summary' => 'Ringkasan perusahaan.',
                    'content' => '<p>Halo <strong>dunia</strong></p>',
                    'seo_title' => 'Tentang Kami | Primainti',
                    'seo_description' => 'Profil perusahaan Primainti.',
                    'og_image_id' => null,
                ],
            ],
        ], $overrides);
    }

    private function makeLivePage(string $locale = 'id', string $slug = 'tentang-kami', array $overrides = []): Page
    {
        $page = Page::create(array_merge([
            'status' => 'published',
            'published_at' => now()->subHour(),
        ], $overrides));

        $page->translations()->create([
            'locale' => $locale,
            'title' => 'Tentang Kami',
            'slug' => $slug,
            'summary' => 'Ringkasan.',
            'content' => '<p>Konten publik.</p>',
        ]);

        return $page;
    }

    // --- Akses & otorisasi ---

    public function test_guest_cannot_access_pages(): void
    {
        $this->get(route('admin.pages.index'))->assertRedirect(route('login'));
        $this->post(route('admin.pages.store'), [])->assertRedirect(route('login'));
    }

    public function test_user_without_content_permission_is_forbidden(): void
    {
        $this->actingAs($this->noAccess)->get(route('admin.pages.index'))->assertForbidden();
        $this->actingAs($this->noAccess)->post(route('admin.pages.store'), $this->payload())->assertForbidden();
    }

    public function test_editor_can_create_draft_but_cannot_publish(): void
    {
        // Editor boleh simpan draft.
        $this->actingAs($this->editor)
            ->post(route('admin.pages.store'), $this->payload())
            ->assertRedirect();

        $this->assertEquals('draft', Page::firstOrFail()->status);

        // Editor tidak boleh publish langsung.
        $this->actingAs($this->editor)
            ->post(route('admin.pages.store'), $this->payload([
                'translations' => ['id' => ['title' => 'Lain', 'slug' => 'lain']],
                'status' => 'published',
            ]))
            ->assertForbidden();
    }

    public function test_editor_cannot_transition_draft_to_published(): void
    {
        $page = Page::create(['status' => 'draft']);
        $page->translations()->create(['locale' => 'id', 'title' => 'Draf', 'slug' => 'draf']);

        $this->actingAs($this->editor)
            ->put(route('admin.pages.update', $page), $this->payload([
                'status' => 'published',
                'translations' => ['id' => ['title' => 'Draf', 'slug' => 'draf']],
            ]))
            ->assertForbidden();

        $this->assertEquals('draft', $page->fresh()->status);
    }

    public function test_editor_can_fix_typo_on_published_page(): void
    {
        $page = $this->makeLivePage();

        $this->actingAs($this->editor)
            ->put(route('admin.pages.update', $page), $this->payload([
                'status' => 'published',
                'translations' => ['id' => ['title' => 'Tentang Kami Revisi', 'slug' => 'tentang-kami']],
            ]))
            ->assertRedirect();

        $this->assertEquals('Tentang Kami Revisi', $page->fresh()->translations->first()->title);
    }

    public function test_content_admin_can_publish_and_sets_published_at(): void
    {
        $this->actingAs($this->contentAdmin)
            ->post(route('admin.pages.store'), $this->payload(['status' => 'published']))
            ->assertRedirect();

        $page = Page::firstOrFail();
        $this->assertEquals('published', $page->status);
        $this->assertNotNull($page->published_at);
    }

    // --- Slug ---

    public function test_slug_is_generated_from_title_when_empty(): void
    {
        $this->actingAs($this->contentAdmin)->post(route('admin.pages.store'), $this->payload([
            'translations' => ['id' => ['title' => 'Fasilitas Produksi', 'slug' => '']],
        ]))->assertRedirect();

        $this->assertEquals(
            'fasilitas-produksi',
            Page::firstOrFail()->translations->first()->slug
        );
    }

    public function test_duplicate_slug_in_same_locale_is_rejected(): void
    {
        $this->makeLivePage('id', 'tentang-kami');

        $response = $this->actingAs($this->contentAdmin)
            ->post(route('admin.pages.store'), $this->payload());

        $response->assertSessionHasErrors('translations.id.slug');
        $this->assertEquals(1, Page::count());
    }

    public function test_same_slug_in_different_locale_is_allowed(): void
    {
        $this->actingAs($this->contentAdmin)->post(route('admin.pages.store'), $this->payload([
            'translations' => [
                'id' => ['title' => 'Tentang Kami', 'slug' => 'tentang-kami'],
                'en' => ['title' => 'About Us', 'slug' => 'tentang-kami'],
            ],
        ]))->assertRedirect();

        $this->assertEquals(2, Page::firstOrFail()->translations()->count());
    }

    public function test_slug_uniqueness_is_checked_on_update(): void
    {
        $this->makeLivePage('id', 'tentang-kami');

        $other = Page::create(['status' => 'draft']);
        $other->translations()->create(['locale' => 'id', 'title' => 'Lain', 'slug' => 'lain']);

        $response = $this->actingAs($this->contentAdmin)
            ->put(route('admin.pages.update', $other), $this->payload([
                'translations' => ['id' => ['title' => 'Lain', 'slug' => 'tentang-kami']],
            ]));

        $response->assertSessionHasErrors('translations.id.slug');
    }

    public function test_unknown_locale_is_dropped_and_empty_translations_rejected(): void
    {
        $response = $this->actingAs($this->contentAdmin)
            ->post(route('admin.pages.store'), $this->payload([
                'translations' => ['fr' => ['title' => 'Bonjour', 'slug' => 'bonjour']],
            ]));

        $response->assertSessionHasErrors('translations');
        $this->assertEquals(0, Page::count());
    }

    // --- Sanitasi ---

    public function test_dangerous_html_is_cleaned_on_store(): void
    {
        $this->actingAs($this->contentAdmin)->post(route('admin.pages.store'), $this->payload([
            'translations' => ['id' => [
                'title' => 'Uji',
                'slug' => 'uji',
                'content' => '<p>Aman <strong>ya</strong></p><script>alert(1)</script>'
                    .'<a href="javascript:alert(2)">x</a>',
            ]],
        ]))->assertRedirect();

        $content = Page::firstOrFail()->translations->first()->content;

        $this->assertStringContainsString('<strong>ya</strong>', $content);
        $this->assertStringNotContainsString('<script>', $content);
        $this->assertStringNotContainsString('javascript:', $content);
    }

    public function test_section_body_is_cleaned_and_managed(): void
    {
        $pageId = $this->actingAs($this->contentAdmin)->post(route('admin.pages.store'), $this->payload([
            'translations' => ['id' => ['title' => 'Uji', 'slug' => 'uji']],
            'sections' => ['id' => [
                ['heading' => 'Bagian A', 'body' => '<p>Isi<script>alert(1)</script></p>', 'position' => 1, 'is_visible' => 1],
                ['heading' => 'Tersembunyi', 'body' => '<p>Rahasia</p>', 'position' => 0, 'is_visible' => 0],
            ]],
        ]))->assertRedirect();

        $page = Page::firstOrFail();

        $this->assertEquals(2, $page->translations->first()->sections()->count());

        // Update: ubah urutan, sembunyikan A, hapus section kedua.
        $sections = $page->translations->first()->sections()->ordered()->get();

        $this->actingAs($this->contentAdmin)->put(route('admin.pages.update', $page), $this->payload([
            'translations' => ['id' => ['title' => 'Uji', 'slug' => 'uji']],
            'sections' => ['id' => [
                ['id' => $sections[1]->id, 'heading' => 'Bagian A', 'body' => '<p>Isi</p>', 'position' => 5, 'is_visible' => 0],
                ['id' => $sections[0]->id, '_remove' => 1],
            ]],
        ]))->assertRedirect();

        $remaining = $page->fresh()->translations->first()->sections;
        $this->assertCount(1, $remaining);
        $this->assertFalse((bool) $remaining->first()->is_visible);
        $this->assertStringNotContainsString('<script>', $remaining->first()->body);
    }

    // --- Status & publik ---

    public function test_published_page_is_visible_to_public(): void
    {
        $this->makeLivePage();

        $this->get('/id/tentang-kami')
            ->assertOk()
            ->assertSee('Tentang Kami')
            ->assertSee('Konten publik.');
    }

    public function test_draft_archived_and_scheduled_pages_are_hidden(): void
    {
        $draft = Page::create(['status' => 'draft']);
        $draft->translations()->create(['locale' => 'id', 'title' => 'Draf', 'slug' => 'draf']);

        $archived = Page::create(['status' => 'archived']);
        $archived->translations()->create(['locale' => 'id', 'title' => 'Arsip', 'slug' => 'arsip']);

        $scheduled = Page::create(['status' => 'published', 'published_at' => now()->addDay()]);
        $scheduled->translations()->create(['locale' => 'id', 'title' => 'Jadwal', 'slug' => 'jadwal']);

        $this->get('/id/draf')->assertNotFound();
        $this->get('/id/arsip')->assertNotFound();
        $this->get('/id/jadwal')->assertNotFound();
    }

    public function test_only_visible_sections_render_in_order(): void
    {
        $page = $this->makeLivePage();
        $translation = $page->translations->first();
        $translation->sections()->create(['heading' => 'Kedua', 'body' => '<p>Isi kedua</p>', 'position' => 2, 'is_visible' => true]);
        $translation->sections()->create(['heading' => 'Pertama', 'body' => '<p>Isi pertama</p>', 'position' => 1, 'is_visible' => true]);
        $translation->sections()->create(['heading' => 'Sembunyi', 'body' => '<p>Jangan tampil</p>', 'position' => 0, 'is_visible' => false]);

        $response = $this->get('/id/tentang-kami')->assertOk();

        $response->assertSeeInOrder(['Pertama', 'Isi pertama', 'Kedua', 'Isi kedua']);
        $response->assertDontSee('Jangan tampil');
    }

    public function test_fallback_locale_is_used_when_translation_missing(): void
    {
        // fallback_locale = en (default config): slug en tampil via prefix /id/.
        $this->makeLivePage('en', 'about-us');

        $this->get('/id/about-us')
            ->assertOk()
            ->assertSee('Tentang Kami');
    }

    public function test_public_page_has_seo_and_og_tags(): void
    {
        $asset = MediaAsset::create([
            'disk' => 'public',
            'path' => 'media/2026/10/m_og.jpg',
            'original_name' => 'og.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 100,
        ]);

        $page = Page::create(['status' => 'published', 'published_at' => now()->subHour()]);
        $page->translations()->create([
            'locale' => 'id',
            'title' => 'Tentang Kami',
            'slug' => 'tentang-kami',
            'seo_title' => 'Tentang Kami | Primainti',
            'seo_description' => 'Profil resmi.',
            'og_image_id' => $asset->id,
        ]);

        $this->get('/id/tentang-kami')
            ->assertOk()
            ->assertSee('<title>Tentang Kami | Primainti</title>', false)
            ->assertSee('name="description" content="Profil resmi."', false)
            ->assertSee('property="og:image"', false);
    }

    public function test_media_used_as_og_image_cannot_be_deleted(): void
    {
        $asset = MediaAsset::create([
            'disk' => 'public',
            'path' => 'media/2026/10/m_og.jpg',
            'original_name' => 'og.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 100,
        ]);

        $page = Page::create(['status' => 'draft']);
        $page->translations()->create([
            'locale' => 'id', 'title' => 'Uji', 'slug' => 'uji', 'og_image_id' => $asset->id,
        ]);

        $this->actingAs($this->contentAdmin)
            ->delete(route('admin.media.destroy', $asset))
            ->assertSessionHasErrors('media');

        $this->assertFalse($asset->fresh()->trashed());
    }

    // --- Preview & lifecycle ---

    public function test_preview_shows_draft_to_authorized_admin(): void
    {
        $page = Page::create(['status' => 'draft']);
        $page->translations()->create([
            'locale' => 'id', 'title' => 'Draf Rahasia', 'slug' => 'draf-rahasia',
            'content' => '<p>Belum tayang</p>',
        ]);

        // actingAs persisten dalam satu test: cek guest lebih dulu.
        $this->get(route('admin.pages.preview', $page))->assertRedirect(route('login'));

        $this->actingAs($this->contentAdmin)
            ->get(route('admin.pages.preview', $page))
            ->assertOk()
            ->assertSee('Draf Rahasia')
            ->assertSee('Pratinjau');
    }

    public function test_page_trash_restore_and_force_delete(): void
    {
        $page = $this->makeLivePage();

        $this->actingAs($this->contentAdmin)
            ->delete(route('admin.pages.destroy', $page))
            ->assertRedirect(route('admin.pages.index'));

        $this->assertTrue($page->fresh()->trashed());
        $this->get('/id/tentang-kami')->assertNotFound();

        $this->actingAs($this->contentAdmin)
            ->post(route('admin.pages.restore', $page->id))
            ->assertRedirect();

        $this->assertFalse($page->fresh()->trashed());
        $this->get('/id/tentang-kami')->assertOk();

        $this->actingAs($this->contentAdmin)
            ->delete(route('admin.pages.force-destroy', $page->id))
            ->assertRedirect();

        $this->assertNull(Page::withTrashed()->find($page->id));
    }
}
