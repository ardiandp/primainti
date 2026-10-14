<?php

namespace Tests\Feature\Admin;

use App\Models\Facility;
use App\Models\MediaAsset;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityManagementTest extends TestCase
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

    /** @param array<string, mixed> $overrides */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'status' => 'draft',
            'sort_order' => 0,
            'published_at' => null,
            'translations' => [
                'id' => [
                    'name' => 'Pabrik A',
                    'slug' => 'pabrik-a',
                    'summary' => 'Ringkasan.',
                    'description' => '<p>Deskripsi <strong>fasilitas</strong></p>',
                ],
            ],
        ], $overrides);
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

    private function makeLiveFacility(string $slug = 'pabrik-a'): Facility
    {
        $facility = Facility::create([
            'status' => 'published',
            'published_at' => now()->subHour(),
            'sort_order' => 0,
        ]);
        $facility->translations()->create([
            'locale' => 'id', 'name' => 'Pabrik A', 'slug' => $slug,
            'summary' => 'Ringkasan.', 'description' => '<p>Deskripsi.</p>',
        ]);

        return $facility;
    }

    public function test_editor_can_create_draft_but_cannot_publish(): void
    {
        $this->actingAs($this->editor)
            ->post(route('admin.facilities.store'), $this->payload())
            ->assertRedirect();

        $this->assertEquals('draft', Facility::firstOrFail()->status);

        $this->actingAs($this->editor)
            ->post(route('admin.facilities.store'), $this->payload([
                'status' => 'published',
                'translations' => ['id' => ['name' => 'Lain', 'slug' => 'lain']],
            ]))
            ->assertForbidden();
    }

    public function test_duplicate_slug_is_rejected_and_description_is_sanitized(): void
    {
        $this->makeLiveFacility();

        $response = $this->actingAs($this->contentAdmin)
            ->post(route('admin.facilities.store'), $this->payload([
                'translations' => ['id' => [
                    'name' => 'Duplikat', 'slug' => 'pabrik-a',
                    'description' => '<p>OK</p><script>alert(1)</script>',
                ]],
            ]));

        $response->assertSessionHasErrors('translations.id.slug');
        $this->assertEquals(1, Facility::count());
    }

    public function test_photos_reuse_media_library_without_duplicate(): void
    {
        $asset = $this->makeAsset();

        $this->actingAs($this->contentAdmin)->post(route('admin.facilities.store'), $this->payload([
            'status' => 'published',
            'media' => [
                ['media_asset_id' => $asset->id, 'position' => 1],
            ],
        ]))->assertRedirect();

        $facility = Facility::firstOrFail();

        $this->assertEquals(1, MediaAsset::count());
        $this->assertEquals(1, $facility->media()->count());

        $this->get('/id/fasilitas')
            ->assertOk()
            ->assertSee('Pabrik A')
            ->assertSee('Alt aset');
    }

    public function test_draft_facility_is_hidden_but_previewable(): void
    {
        $facility = Facility::create(['status' => 'draft']);
        $facility->translations()->create(['locale' => 'id', 'name' => 'Draf', 'slug' => 'draf']);

        $this->get('/id/fasilitas')->assertOk()->assertDontSee('Draf');

        $this->get(route('admin.facilities.preview', $facility))->assertRedirect(route('login'));

        $this->actingAs($this->contentAdmin)
            ->get(route('admin.facilities.preview', $facility))
            ->assertOk()
            ->assertSee('Draf');
    }

    public function test_deleting_facility_keeps_media_files(): void
    {
        $asset = $this->makeAsset();

        $this->actingAs($this->contentAdmin)->post(route('admin.facilities.store'), $this->payload([
            'media' => [['media_asset_id' => $asset->id, 'position' => 0]],
        ]))->assertRedirect();

        Facility::firstOrFail()->forceDelete();

        $this->assertNotNull(MediaAsset::find($asset->id));
    }

    public function test_media_used_by_facility_cannot_be_deleted(): void
    {
        $asset = $this->makeAsset();

        $this->actingAs($this->contentAdmin)->post(route('admin.facilities.store'), $this->payload([
            'media' => [['media_asset_id' => $asset->id, 'position' => 0]],
        ]))->assertRedirect();

        $this->actingAs($this->contentAdmin)
            ->delete(route('admin.media.destroy', $asset))
            ->assertSessionHasErrors('media');
    }
}
