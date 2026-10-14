<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryManagementTest extends TestCase
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

    public function test_editor_can_create_draft_category_but_cannot_publish(): void
    {
        $this->actingAs($this->editor)
            ->post(route('admin.product-categories.store'), [
                'status' => 'draft',
                'translations' => ['id' => ['name' => 'Alat Berat', 'slug' => 'alat-berat']],
            ])
            ->assertRedirect();

        $this->assertEquals('draft', ProductCategory::firstOrFail()->status);

        $this->actingAs($this->editor)
            ->post(route('admin.product-categories.store'), [
                'status' => 'published',
                'translations' => ['id' => ['name' => 'Lain', 'slug' => 'lain']],
            ])
            ->assertForbidden();
    }

    public function test_duplicate_category_slug_is_rejected(): void
    {
        ProductCategory::create(['status' => 'published'])->translations()->create([
            'locale' => 'id', 'name' => 'Alat Berat', 'slug' => 'alat-berat',
        ]);

        $this->actingAs($this->contentAdmin)
            ->post(route('admin.product-categories.store'), [
                'status' => 'draft',
                'translations' => ['id' => ['name' => 'Duplikat', 'slug' => 'alat-berat']],
            ])
            ->assertSessionHasErrors('translations.id.slug');
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $category = ProductCategory::create(['status' => 'published']);
        $category->translations()->create(['locale' => 'id', 'name' => 'Kat', 'slug' => 'kat']);

        Product::factory()->create(['product_category_id' => $category->id]);

        $this->actingAs($this->contentAdmin)
            ->delete(route('admin.product-categories.destroy', $category))
            ->assertSessionHasErrors('category');

        $this->assertFalse($category->fresh()->trashed());
    }
}
