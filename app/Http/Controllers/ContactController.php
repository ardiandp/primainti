<?php

namespace App\Http\Controllers;

use App\Mail\ContactNotification;
use App\Models\ContactMessage;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Halaman kontak publik (Phase 8 info, Phase 9 formulir).
 *
 * Anti-spam: rate limit di route + honeypot. Pesan honeypot disimpan
 * sebagai spam (bisa ditinjau admin) tanpa mengirim email, dengan respon
 * sukses yang sama agar deteksi tidak bocor ke bot.
 */
class ContactController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {
    }

    public function show(string $locale)
    {
        abort_unless(in_array($locale, ['id', 'en'], true), 404);

        app()->setLocale($locale);

        return view('contact.show', ['locale' => $locale]);
    }

    public function store(Request $request, string $locale)
    {
        abort_unless(in_array($locale, ['id', 'en'], true), 404);

        app()->setLocale($locale);

        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email', 'max:255'],
                'company' => ['nullable', 'string', 'max:150'],
                'phone' => ['nullable', 'string', 'max:50'],
                'subject' => ['required', 'string', 'max:150'],
                'message' => ['required', 'string', 'max:5000'],
                'website' => ['nullable', 'string', 'max:255'],
            ],
            [],
            [
                'name' => __('contact.name'),
                'email' => __('contact.email'),
                'company' => __('contact.company'),
                'phone' => __('contact.phone'),
                'subject' => __('contact.subject'),
                'message' => __('contact.message'),
            ]
        );

        $isSpam = trim((string) ($validated['website'] ?? '')) !== '';

        $contactMessage = ContactMessage::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'company' => $validated['company'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'ip_address' => $request->ip(),
            'status' => $isSpam ? 'spam' : 'new',
        ]);

        if (! $isSpam) {
            $this->notify($contactMessage);
        }

        return redirect()->route('public.contact.show', $locale)->with('status', __('contact.sent'));
    }

    private function notify(ContactMessage $contactMessage): void
    {
        // Interim Q-04: penerima = email kontak di pengaturan, fallback mail.from.
        $recipient = $this->settings->get('contact_email') ?? config('mail.from.address');

        if (! $recipient) {
            Log::warning('Notifikasi pesan kontak dilewati: penerima belum dikonfigurasi.', [
                'contact_message_id' => $contactMessage->id,
            ]);

            return;
        }

        try {
            Mail::to($recipient)->send(new ContactNotification($contactMessage));
        } catch (\Throwable $e) {
            // Pesan tetap tersimpan; catat kegagalan tanpa data sensitif.
            Log::warning('Notifikasi pesan kontak gagal dikirim.', [
                'contact_message_id' => $contactMessage->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
