<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_sends_reset_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);

        // Permintaan tercatat tanpa token di audit log.
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'password.reset_requested',
            'auditable_id' => $user->id,
        ]);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'reset@primainti.test']);

        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'reset@primainti.test',
            'password' => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(
            \Illuminate\Support\Facades\Auth::attempt(['email' => 'reset@primainti.test', 'password' => 'passwordbaru123'])
        );

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'password.reset',
            'auditable_id' => $user->id,
        ]);
    }

    public function test_invalid_token_is_rejected(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->from(route('password.request'))->post(route('password.update'), [
            'token' => 'token-salah',
            'email' => $user->email,
            'password' => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ])->assertSessionHasErrors('email');

        $this->assertFalse(
            \Illuminate\Support\Facades\Auth::attempt(['email' => $user->email, 'password' => 'passwordbaru123'])
        );
    }
}
