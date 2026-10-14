<?php

namespace Tests\Feature;

use App\Mail\ContactNotification;
use App\Models\ContactMessage;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactTest extends TestCase
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
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'company' => 'PT Maju',
            'phone' => '08123456789',
            'subject' => 'Permintaan katalog',
            'message' => 'Mohon kirim katalog produk terbaru.',
            'website' => '',
        ], $overrides);
    }

    // --- Publik ---

    public function test_valid_message_is_stored_once_and_notified_in_indonesian(): void
    {
        Mail::fake();
        SiteSetting::updateOrCreate(['key' => 'contact_email'], ['value' => 'sales@primainti.test']);

        $response = $this->post('/id/kontak', $this->payload());

        $response->assertRedirect(route('public.contact.show', 'id'));
        $response->assertSessionHas('status', 'Terima kasih! Pesan Anda sudah kami terima dan akan segera ditindaklanjuti.');

        $this->assertEquals(1, ContactMessage::count());

        $message = ContactMessage::firstOrFail();
        $this->assertEquals('new', $message->status);
        $this->assertEquals('PT Maju', $message->company);
        $this->assertNotNull($message->ip_address);

        Mail::assertSent(ContactNotification::class, function ($mail) {
            return $mail->hasTo('sales@primainti.test');
        });
    }

    public function test_english_flash_message_and_fallback_recipient(): void
    {
        Mail::fake();
        config(['mail.from.address' => 'noreply@example.com']);

        $this->post('/en/kontak', $this->payload())
            ->assertSessionHas('status', 'Thank you! Your message has been received and will be followed up soon.');

        Mail::assertSent(ContactNotification::class, function ($mail) {
            return $mail->hasTo('noreply@example.com');
        });
    }

    public function test_invalid_message_is_rejected_and_nothing_stored(): void
    {
        Mail::fake();

        $response = $this->post('/id/kontak', $this->payload([
            'email' => 'bukan-email',
            'subject' => '',
            'message' => '',
        ]));

        $response->assertSessionHasErrors(['email', 'subject', 'message']);
        $this->assertEquals(0, ContactMessage::count());
        Mail::assertNothingSent();
    }

    public function test_honeypot_is_stored_as_spam_without_email_and_looks_successful(): void
    {
        Mail::fake();

        $response = $this->post('/id/kontak', $this->payload(['website' => 'http://spam.bot']));

        $response->assertRedirect(route('public.contact.show', 'id'));
        $response->assertSessionHas('status');

        $message = ContactMessage::firstOrFail();
        $this->assertEquals('spam', $message->status);
        Mail::assertNothingSent();
    }

    public function test_rate_limit_blocks_excessive_submits(): void
    {
        Mail::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/id/kontak', $this->payload(['email' => "user{$i}@example.com"]))->assertRedirect();
        }

        $this->post('/id/kontak', $this->payload(['email' => 'user5@example.com']))->assertTooManyRequests();
        $this->assertEquals(5, ContactMessage::count());
    }

    public function test_smtp_failure_keeps_stored_message(): void
    {
        $manager = \Mockery::mock(\Illuminate\Mail\MailManager::class);
        $manager->shouldReceive('to')->once()->andThrow(new \Exception('Connection refused'));
        Mail::swap($manager);

        $response = $this->post('/id/kontak', $this->payload());

        $response->assertRedirect(route('public.contact.show', 'id'));
        $response->assertSessionHas('status');
        $this->assertEquals(1, ContactMessage::count());
        $this->assertEquals('new', ContactMessage::firstOrFail()->status);

        \Mockery::close();
    }

    // --- Admin ---

    public function test_only_permitted_roles_can_manage_messages(): void
    {
        $this->get(route('admin.contact-messages.index'))->assertRedirect(route('login'));

        $this->actingAs($this->editor)->get(route('admin.contact-messages.index'))->assertForbidden();

        $this->actingAs($this->contentAdmin)->get(route('admin.contact-messages.index'))->assertOk();
    }

    public function test_admin_can_filter_update_and_audit_message(): void
    {
        ContactMessage::create([
            'name' => 'A', 'email' => 'a@example.com', 'subject' => 'S1',
            'message' => 'Isi', 'status' => 'new',
        ]);
        ContactMessage::create([
            'name' => 'B', 'email' => 'b@example.com', 'subject' => 'S2',
            'message' => 'Isi', 'status' => 'spam',
        ]);

        $this->actingAs($this->contentAdmin)
            ->get(route('admin.contact-messages.index', ['status' => 'spam']))
            ->assertOk()
            ->assertSee('S2')
            ->assertDontSee('S1');

        $message = ContactMessage::where('subject', 'S1')->firstOrFail();

        // Detail menandai sudah dibaca.
        $this->actingAs($this->contentAdmin)
            ->get(route('admin.contact-messages.show', $message))
            ->assertOk()
            ->assertSee('Isi');
        $this->assertTrue($message->fresh()->is_read);

        // Update status + catatan internal.
        $this->actingAs($this->contentAdmin)
            ->put(route('admin.contact-messages.update', $message), [
                'status' => 'in_progress',
                'internal_note' => 'Ditelepon balik besok.',
            ])
            ->assertRedirect(route('admin.contact-messages.show', $message));

        $message->refresh();
        $this->assertEquals('in_progress', $message->status);
        $this->assertEquals('Ditelepon balik besok.', $message->internal_note);

        $log = \App\Models\AuditLog::where('action', 'contact_message.update')->firstOrFail();
        $this->assertStringNotContainsString('Isi', json_encode($log->new_values));
    }

    public function test_private_notes_and_messages_never_render_publicly(): void
    {
        ContactMessage::create([
            'name' => 'Rahasia', 'email' => 'r@example.com', 'subject' => 'Subjek Privat',
            'message' => 'Isi sangat privat.', 'status' => 'new', 'internal_note' => 'Catatan internal.',
        ]);

        $this->get('/id/kontak')->assertOk()->assertDontSee('Isi sangat privat.');
        $this->get('/id')->assertOk()->assertDontSee('Isi sangat privat.');
    }
}
