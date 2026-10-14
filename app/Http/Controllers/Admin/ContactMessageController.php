<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ContactMessage::class);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(ContactMessage::STATUSES)],
        ]);

        $query = ContactMessage::query()
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($validated['q'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->latest();

        $counts = ContactMessage::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return view('admin.contact-messages.index', [
            'messages' => $query->paginate(20)->withQueryString(),
            'filters' => $validated,
            'counts' => $counts,
        ]);
    }

    public function show(ContactMessage $contactMessage)
    {
        $this->authorize('view', $contactMessage);

        if (! $contactMessage->is_read) {
            $contactMessage->update(['is_read' => true]);
        }

        return view('admin.contact-messages.show', ['message' => $contactMessage->fresh()]);
    }

    public function update(Request $request, ContactMessage $contactMessage)
    {
        $this->authorize('update', $contactMessage);

        $validated = $request->validate([
            'status' => ['required', Rule::in(ContactMessage::STATUSES)],
            'internal_note' => ['nullable', 'string', 'max:5000'],
        ]);

        $old = ['status' => $contactMessage->status];

        $contactMessage->update($validated);

        AuditLogger::log('contact_message.update', $contactMessage, $old, [
            'status' => $contactMessage->status,
        ]);

        return redirect()->route('admin.contact-messages.show', $contactMessage)
            ->with('status', 'Pesan diperbarui.');
    }
}
