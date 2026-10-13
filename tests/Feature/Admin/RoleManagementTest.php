<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole(Role::where('name', 'super_admin')->first());
    }

    public function test_super_admin_can_create_role_with_permissions(): void
    {
        $permission = Permission::where('name', 'content.manage')->firstOrFail();

        $this->actingAs($this->superAdmin)->post(route('admin.roles.store'), [
            'name' => 'reviewer',
            'description' => 'Reviewer konten',
            'permissions' => [$permission->id],
        ])->assertRedirect(route('admin.roles.index'));

        $role = Role::where('name', 'reviewer')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->permissions->pluck('name')->contains('content.manage'));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'role.create',
            'auditable_id' => $role->id,
        ]);
    }

    public function test_super_admin_role_cannot_be_deleted(): void
    {
        $superRole = Role::where('name', 'super_admin')->firstOrFail();

        $this->actingAs($this->superAdmin)
            ->from(route('admin.roles.index'))
            ->delete(route('admin.roles.destroy', $superRole))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('roles', ['id' => $superRole->id, 'name' => 'super_admin']);
    }

    public function test_role_in_use_cannot_be_deleted(): void
    {
        $editorRole = Role::where('name', 'editor')->firstOrFail();
        $user = User::factory()->create();
        $user->assignRole($editorRole);

        $this->actingAs($this->superAdmin)
            ->from(route('admin.roles.index'))
            ->delete(route('admin.roles.destroy', $editorRole))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('roles', ['id' => $editorRole->id]);
    }

    public function test_unused_role_can_be_deleted_with_audit(): void
    {
        $role = Role::create(['name' => 'sekali_pakai']);

        $this->actingAs($this->superAdmin)
            ->delete(route('admin.roles.destroy', $role))
            ->assertRedirect();

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'role.delete']);
    }

    public function test_editor_cannot_manage_roles(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole(Role::where('name', 'editor')->first());

        $this->actingAs($editor)->get(route('admin.roles.index'))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.roles.store'), ['name' => 'x'])->assertForbidden();
    }
}
