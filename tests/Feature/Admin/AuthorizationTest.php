<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();
    }

    private function userWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('name', $roleName)->firstOrFail());

        return $user;
    }

    public function test_guest_cannot_access_admin_area(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/admin/users')->assertRedirect(route('login'));
        $this->get('/admin/roles')->assertRedirect(route('login'));
    }

    public function test_user_without_permission_is_denied_on_direct_url(): void
    {
        $editor = $this->userWithRole('editor');

        $this->actingAs($editor)->get('/admin/users')->assertForbidden();
        $this->actingAs($editor)->get('/admin/roles')->assertForbidden();
    }

    public function test_content_admin_without_user_permission_is_denied(): void
    {
        $contentAdmin = $this->userWithRole('content_admin');

        $this->actingAs($contentAdmin)->get('/admin/users')->assertForbidden();
    }

    public function test_editor_can_open_dashboard(): void
    {
        $editor = $this->userWithRole('editor');

        $this->actingAs($editor)->get('/admin')->assertOk();
    }

    public function test_super_admin_can_access_user_and_role_pages(): void
    {
        $superAdmin = $this->userWithRole('super_admin');

        $this->actingAs($superAdmin)->get('/admin/users')->assertOk();
        $this->actingAs($superAdmin)->get('/admin/roles')->assertOk();
    }

    public function test_super_admin_gate_bypass_works_without_any_permission(): void
    {
        // super_admin tanpa satu pun permission tetap lolos Gate.
        $role = Role::where('name', 'super_admin')->firstOrFail();
        $role->syncPermissions([]);

        $user = User::factory()->create();
        $user->assignRole($role);

        $this->assertTrue($user->fresh()->can('user.view'));
        $this->assertTrue($user->fresh()->can('permission.tidak.ada'));
    }

    public function test_permission_checks_are_enforced_for_non_super_admin(): void
    {
        $editor = $this->userWithRole('editor');

        $this->assertFalse($editor->can('user.view'));
        $this->assertTrue($editor->can('content.manage'));
        $this->assertFalse($editor->can('content.publish'));
    }
}
