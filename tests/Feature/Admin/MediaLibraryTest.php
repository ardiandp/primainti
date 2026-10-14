<?php

namespace Tests\Feature\Admin;

use App\Livewire\MediaPicker;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\Media\MediaUsageService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $contentAdmin;

    private User $editor;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        (new RoleSeeder)->run();

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole(Role::where('name', 'super_admin')->first());

        $this->contentAdmin = User::factory()->create();
        $this->contentAdmin->assignRole(Role::where('name', 'content_admin')->first());

        $this->editor = User::factory()->create();
        $this->editor->assignRole(Role::where('name', 'editor')->first());
    }

    private function makeAsset(array $overrides = []): MediaAsset
    {
        return MediaAsset::create(array_merge([
            'disk' => 'public',
            'path' => 'media/2026/10/m_test.jpg',
            'original_name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1234,
            'width' => 800,
            'height' => 600,
            'uploaded_by' => $this->superAdmin->id,
        ], $overrides));
    }

    // --- Akses & otorisasi ---

    public function test_guest_cannot_access_media_library(): void
    {
        $this->get(route('admin.media.index'))->assertRedirect(route('login'));
        $this->post(route('admin.media.store'), [])->assertRedirect(route('login'));
    }

    public function test_editor_without_media_permission_is_forbidden(): void
    {
        $this->actingAs($this->editor)
            ->get(route('admin.media.index'))
            ->assertForbidden();

        $this->actingAs($this->editor)
            ->post(route('admin.media.store'), [])
            ->assertForbidden();
    }

    public function test_content_admin_can_view_index_with_search_and_type_filter(): void
    {
        $this->makeAsset(['title' => 'Foto Pabrik', 'original_name' => 'pabrik.jpg']);
        $this->makeAsset([
            'title' => 'Datasheet',
            'original_name' => 'datasheet.pdf',
            'mime_type' => 'application/pdf',
            'path' => 'media/2026/10/m_doc.pdf',
        ]);

        $this->actingAs($this->contentAdmin)
            ->get(route('admin.media.index', ['q' => 'Pabrik', 'type' => 'image']))
            ->assertOk()
            ->assertSee('Foto Pabrik')
            ->assertDontSee('Datasheet');
    }

    // --- Upload ---

    public function test_upload_valid_image_creates_asset_file_and_thumbnail(): void
    {
        $response = $this->actingAs($this->contentAdmin)->post(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->image('produk.jpg', 800, 600)],
        ]);

        $response->assertRedirect();
        $this->assertEquals(1, MediaAsset::count());

        $asset = MediaAsset::firstOrFail();
        Storage::disk('public')->assertExists($asset->path);
        $this->assertNotNull($asset->width);
        $this->assertNotNull($asset->height);

        // Thumbnail turunan dari media sumber.
        $this->assertTrue($asset->variants()->where('variant', 'thumb')->exists());
        Storage::disk('public')->assertExists(
            $asset->variants()->where('variant', 'thumb')->firstOrFail()->path
        );
    }

    public function test_upload_multi_file(): void
    {
        $this->actingAs($this->contentAdmin)->post(route('admin.media.store'), [
            'files' => [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->create('b.pdf', 100, 'application/pdf'),
            ],
        ])->assertRedirect();

        $this->assertEquals(2, MediaAsset::count());
    }

    public function test_invalid_executable_file_is_rejected_with_clear_message(): void
    {
        $response = $this->actingAs($this->contentAdmin)->post(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->create('jahat.exe', 100, 'application/x-msdownload')],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('upload_errors');
        $this->assertEquals(0, MediaAsset::count());

        $errors = session('upload_errors');
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('tidak diizinkan', (string) $errors[0]);
    }

    public function test_extension_mismatch_is_rejected(): void
    {
        // Ekstensi .jpg tetapi MIME terdeteksi application/pdf → ditolak.
        // (File fake Laravel melaporkan MIME per ekstensi, jadi mismatch
        // disimulasikan lewat override mimeType seperti deteksi konten asli.)
        $file = UploadedFile::fake()->image('tipuan.jpg')->mimeType('application/pdf');

        $response = $this->actingAs($this->contentAdmin)->post(route('admin.media.store'), [
            'files' => [$file],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('upload_errors');
        $this->assertEquals(0, MediaAsset::count());
    }

    // --- Metadata ---

    public function test_metadata_and_alt_text_can_be_edited(): void
    {
        $asset = $this->makeAsset();

        $this->actingAs($this->contentAdmin)->put(route('admin.media.update', $asset), [
            'title' => 'Judul Baru',
            'alt' => 'Teks alternatif produk',
            'caption' => 'Keterangan singkat',
            'description' => 'Deskripsi panjang media.',
        ])->assertRedirect(route('admin.media.edit', $asset));

        $asset->refresh();
        $this->assertEquals('Judul Baru', $asset->title);
        $this->assertEquals('Teks alternatif produk', $asset->alt);
        $this->assertEquals('Keterangan singkat', $asset->caption);
        $this->assertEquals('Deskripsi panjang media.', $asset->description);
    }

    // --- Reuse lintas modul (tanpa upload ulang) ---

    public function test_one_media_can_be_referenced_by_multiple_modules(): void
    {
        $asset = $this->makeAsset();
        Storage::disk('public')->put($asset->path, 'isi-gambar');

        $product = Product::factory()->create();
        DB::table('product_media')->insert([
            'product_id' => $product->id,
            'media_asset_id' => $asset->id,
            'position' => 0,
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('gallery_items')->insert([
            'gallery_category_id' => null,
            'media_asset_id' => $asset->id,
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $usage = app(MediaUsageService::class);

        $this->assertTrue($usage->isReferenced($asset));
        $this->assertEquals(2, $usage->referenceCount($asset));

        // Satu file dipakai dua modul: tidak ada duplikat upload.
        $this->assertEquals(1, MediaAsset::count());
        Storage::disk('public')->assertExists($asset->path);
    }

    // --- Proteksi hapus ---

    public function test_soft_delete_is_blocked_while_referenced(): void
    {
        $asset = $this->makeAsset();
        $product = Product::factory()->create();
        DB::table('product_media')->insert([
            'product_id' => $product->id,
            'media_asset_id' => $asset->id,
            'position' => 0,
            'is_primary' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->contentAdmin)
            ->delete(route('admin.media.destroy', $asset));

        $response->assertRedirect();
        $response->assertSessionHasErrors('media');
        $this->assertFalse($asset->fresh()->trashed());
    }

    public function test_force_delete_is_blocked_while_referenced(): void
    {
        $asset = $this->makeAsset();
        DB::table('gallery_items')->insert([
            'gallery_category_id' => null,
            'media_asset_id' => $asset->id,
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->contentAdmin)
            ->delete(route('admin.media.force-destroy', $asset));

        $response->assertRedirect();
        $response->assertSessionHasErrors('media');
        $this->assertNotNull(MediaAsset::find($asset->id));
    }

    public function test_deleting_content_does_not_delete_media_file(): void
    {
        $asset = $this->makeAsset();
        Storage::disk('public')->put($asset->path, 'isi-gambar');

        $product = Product::factory()->create();
        DB::table('product_media')->insert([
            'product_id' => $product->id,
            'media_asset_id' => $asset->id,
            'position' => 0,
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Hapus permanen konten (produk) — pivot ikut ter-cascade,
        // media dan file-nya tetap ada.
        $product->forceDelete();

        $this->assertNotNull(MediaAsset::find($asset->id));
        $this->assertFalse(app(MediaUsageService::class)->isReferenced($asset->fresh()));
        Storage::disk('public')->assertExists($asset->path);
    }

    public function test_soft_delete_restore_and_force_delete_lifecycle(): void
    {
        $asset = $this->makeAsset();
        Storage::disk('public')->put($asset->path, 'isi-gambar');

        // Soft delete → trash.
        $this->actingAs($this->contentAdmin)
            ->delete(route('admin.media.destroy', $asset))
            ->assertRedirect(route('admin.media.index'));
        $this->assertTrue($asset->fresh()->trashed());
        Storage::disk('public')->assertExists($asset->path);

        // Restore.
        $this->actingAs($this->contentAdmin)
            ->post(route('admin.media.restore', $asset->id))
            ->assertRedirect();
        $this->assertFalse($asset->fresh()->trashed());

        // Force delete → baris + file fisik hilang.
        $this->actingAs($this->contentAdmin)
            ->delete(route('admin.media.force-destroy', $asset))
            ->assertRedirect();
        $this->assertNull(MediaAsset::withTrashed()->find($asset->id));
        Storage::disk('public')->assertMissing($asset->path);
    }

    // --- Media Picker reusable ---

    public function test_media_picker_lists_search_and_picks_asset(): void
    {
        $this->makeAsset(['title' => 'Foto Mesin A', 'original_name' => 'mesin-a.jpg']);
        $this->makeAsset(['title' => 'Foto Mesin B', 'original_name' => 'mesin-b.jpg']);

        $this->actingAs($this->contentAdmin);

        Livewire::test(MediaPicker::class)
            ->set('search', 'Mesin A')
            ->assertSee('Foto Mesin A')
            ->assertDontSee('Foto Mesin B');

        $asset = MediaAsset::where('title', 'Foto Mesin A')->firstOrFail();

        Livewire::test(MediaPicker::class)
            ->call('openPicker')
            ->assertSet('open', true)
            ->call('pick', $asset->id)
            ->assertDispatched('media-picked')
            ->assertSet('open', false);
    }

    public function test_media_picker_mode_filter_limits_to_images(): void
    {
        $this->makeAsset(['title' => 'Gambar Saja', 'original_name' => 'g.jpg']);
        $this->makeAsset([
            'title' => 'Dokumen Saja',
            'original_name' => 'd.pdf',
            'mime_type' => 'application/pdf',
            'path' => 'media/2026/10/m_doc.pdf',
        ]);

        $this->actingAs($this->contentAdmin);

        Livewire::test(MediaPicker::class, ['mode' => 'image'])
            ->assertSee('Gambar Saja')
            ->assertDontSee('Dokumen Saja');
    }

    public function test_media_picker_hides_assets_from_unauthorized_user(): void
    {
        $this->makeAsset(['title' => 'Rahasia Dapur', 'original_name' => 'rahasia.jpg']);

        $this->actingAs($this->editor);

        Livewire::test(MediaPicker::class)
            ->assertDontSee('Rahasia Dapur');
    }
}
