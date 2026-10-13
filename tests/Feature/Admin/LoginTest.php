<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();
    }

    public function test_guest_can_view_login_page(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_successful_login_is_audited(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login.success',
            'user_id' => $user->id,
        ]);
    }

    public function test_wrong_password_is_rejected_and_audited_without_secret(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'salah-banget',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        $log = \App\Models\AuditLog::where('action', 'login.failed')->first();
        $this->assertNotNull($log);
        $this->assertStringNotContainsString('salah-banget', json_encode($log->new_values));
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->inactive()->create();

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login.blocked',
            'auditable_id' => $user->id,
        ]);
    }

    public function test_inactive_user_session_is_terminated_by_middleware(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $user->update(['is_active' => false]);

        $response = $this->get('/admin');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_attempts(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $i) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => "salah-{$i}",
            ]);
        }

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'salah-6',
        ])->assertStatus(429);
    }

    public function test_logout_invalidates_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_user_with_role_can_login(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('name', 'editor')->first());

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }
}
