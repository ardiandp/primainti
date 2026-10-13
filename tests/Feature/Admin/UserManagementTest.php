<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
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

    public function test_super_admin_can_create_user_with_role(): void
    {
        $editorRole = Role::where('name', 'editor')->firstOrFail();

        $response = $this->actingAs($this->superAdmin)->post(route('admin.users.store'), [
            'name' => 'Staff Editor',
            'email' => 'editor@primainti.test',
            'password' => 'rahasialaravel',
            'password_confirmation' => 'rahasialaravel',
            'roles' => [$editorRole->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $created = User::where('email', 'editor@primainti.test')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->hasRole('editor'));
        $this->assertTrue($created->is_active);
    }

    public function test_user_creation_is_audited_without_password(): void
    {
        $this->actingAs($this->superAdmin)->post(route('admin.users.store'), [
            'name' => 'Tanpa Log',
            'email' => 'tanpalog@primainti.test',
            'password' => 'rahasialaravel',
            'password_confirmation' => 'rahasialaravel',
            'roles' => [],
        ]);

        $log = \App\Models\AuditLog::where('action', 'user.create')->first();
        $this->assertNotNull($log);
        $this->assertStringNotContainsString('rahasialaravel', json_encode($log->new_values));
    }

    public function test_editor_cannot_create_user(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole(Role::where('name', 'editor')->first());

        $this->actingAs($editor)->post(route('admin.users.store'), [
            'name' => 'X',
            'email' => 'x@primainti.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertForbidden();
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $victim = User::factory()->create(['email' => 'korban@primainti.test']);

        $this->actingAs($this->superAdmin)
            ->post(route('admin.users.deactivate', $victim))
            ->assertRedirect();

        $this->assertFalse($victim->fresh()->is_active);

        // Lepas sesi admin agar percobaan login berikutnya sebagai tamu.
        $this->app['auth']->forgetGuards();

        $this->post('/login', [
            'email' => 'korban@primainti.test',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_cannot_deactivate_self(): void
    {
        $this->actingAs($this->superAdmin)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.deactivate', $this->superAdmin))
            ->assertSessionHasErrors('status');

        $this->assertTrue($this->superAdmin->fresh()->is_active);
    }

    public function test_update_changes_roles_and_password_and_is_audited(): void
    {
        $target = User::factory()->create();
        $editorRole = Role::where('name', 'editor')->firstOrFail();
        $target->assignRole(Role::where('name', 'content_admin')->firstOrFail());

        $this->actingAs($this->superAdmin)->put(route('admin.users.update', $target), [
            'name' => 'Target Baru',
            'email' => $target->email,
            'password' => 'passwordbaruuu',
            'password_confirmation' => 'passwordbaruuu',
            'roles' => [$editorRole->id],
        ])->assertRedirect(route('admin.users.index'));

        $target->refresh();
        $this->assertSame('Target Baru', $target->name);
        $this->assertTrue($target->hasRole('editor'));
        $this->assertFalse($target->hasRole('content_admin'));
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('passwordbaruuu', $target->password));

        $log = \App\Models\AuditLog::where('action', 'user.update')->first();
        $this->assertNotNull($log);
        $this->assertStringNotContainsString(
            'passwordbaruuu',
            json_encode([$log->new_values, $log->old_values])
        );
    }

    public function test_reset_access_removes_all_roles_and_is_audited(): void
    {
        $target = User::factory()->create();
        $target->assignRole(Role::where('name', 'editor')->firstOrFail());

        $this->actingAs($this->superAdmin)
            ->post(route('admin.users.reset-access', $target))
            ->assertRedirect();

        $this->assertSame(0, $target->fresh()->roles()->count());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.reset_access',
            'user_id' => $this->superAdmin->id,
        ]);
    }
}
