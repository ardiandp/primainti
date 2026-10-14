<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use App\Models\SiteSetting;
use App\Services\AuditLogger;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {
    }

    public function index()
    {
        $this->authorize('viewAny', SiteSetting::class);

        $values = $this->settings->all();

        $mediaIds = collect($values)->only(['logo_id', 'favicon_id'])->filter()->all();

        return view('admin.settings.index', [
            'groups' => SettingsService::definitions(),
            'values' => $values,
            'assets' => $mediaIds === []
                ? collect()
                : MediaAsset::whereIn('id', $mediaIds)->get()->keyBy('id'),
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('update', SiteSetting::class);

        $rules = [];

        foreach (SettingsService::knownKeys() as $key => $type) {
            $rules["settings.{$key}"] = match ($type) {
                'email' => ['nullable', 'email', 'max:255'],
                'url' => ['nullable', 'url', 'max:500'],
                'safe_url' => ['nullable', 'string', 'max:500', function ($attribute, $value, $fail) {
                    if (! preg_match('/^(#|\/|https?:\/\/|mailto:|tel:)/i', (string) $value)) {
                        $fail('Tautan harus berupa URL http(s), /path relatif, #anchor, mailto:, atau tel:.');
                    }
                }],
                'textarea' => ['nullable', 'string', 'max:2000'],
                'media_image' => ['nullable', 'exists:media_assets,id'],
                default => ['nullable', 'string', 'max:255'],
            };
        }

        $validated = $request->validate($rules);
        $input = $validated['settings'] ?? [];

        // Logo/favicon wajib berupa gambar.
        foreach (['logo_id', 'favicon_id'] as $mediaKey) {
            if (! empty($input[$mediaKey]) && ! MediaAsset::find($input[$mediaKey])?->isImage()) {
                return back()
                    ->withErrors(["settings.{$mediaKey}" => 'Berkas harus berupa gambar (JPG/PNG/WebP/GIF).'])
                    ->withInput();
            }
        }

        $old = $this->settings->all();

        // Hanya kunci dikenal yang disimpan (guard anti-secret).
        $this->settings->setMany($input);

        AuditLogger::log('settings.update', null, $old, $this->settings->all());

        return redirect()->route('admin.settings.index')->with('status', 'Pengaturan disimpan.');
    }
}
