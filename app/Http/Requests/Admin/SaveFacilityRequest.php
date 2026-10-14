<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Validasi simpan fasilitas (store + update).
 * Terjemahan id/en (nama + slug unik per locale, auto-slug),
 * ringkasan, body RTE, dan galeri foto dari Media Library.
 */
class SaveFacilityRequest extends FormRequest
{
    public const LOCALES = ['id', 'en'];

    public function authorize(): bool
    {
        return $this->user()->hasPermission('content.manage');
    }

    protected function prepareForValidation(): void
    {
        $translations = [];

        foreach (self::LOCALES as $locale) {
            $entry = $this->input("translations.{$locale}");

            if (! is_array($entry)) {
                continue;
            }

            $entry = array_map(
                fn ($value) => is_string($value) ? trim($value) : $value,
                $entry
            );

            $hasContent = collect($entry)
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->isNotEmpty();

            if (! $hasContent) {
                continue;
            }

            if (($entry['slug'] ?? '') === '' && ($entry['name'] ?? '') !== '') {
                $entry['slug'] = Str::slug($entry['name']);
            }

            $translations[$locale] = $entry;
        }

        $this->merge(['translations' => $translations]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'status' => ['required', 'in:draft,published,archived'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'published_at' => ['nullable', 'date'],
            'translations' => ['required', 'array', 'min:1'],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['required', 'string', 'max:255', 'alpha_dash'],
            'translations.*.summary' => ['nullable', 'string', 'max:2000'],
            'translations.*.description' => ['nullable', 'string'],
            'media' => ['nullable', 'array'],
            'media.*.id' => ['nullable', 'exists:facility_media,id'],
            'media.*.media_asset_id' => ['nullable', 'exists:media_assets,id'],
            'media.*.position' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'media.*._remove' => ['nullable', 'boolean'],
        ];

        /** @var \App\Models\Facility|null $facility */
        $facility = $this->route('facility');

        foreach (array_keys($this->input('translations', [])) as $locale) {
            $ignoreId = $facility?->translations->firstWhere('locale', $locale)?->id;

            $unique = Rule::unique('facility_translations', 'slug')
                ->where(fn ($query) => $query->where('locale', $locale));

            if ($ignoreId !== null) {
                $unique->ignore($ignoreId);
            }

            $rules["translations.{$locale}.slug"] = ['required', 'string', 'max:255', 'alpha_dash', $unique];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'translations.required' => 'Minimal satu terjemahan (ID atau EN) wajib diisi.',
            'translations.*.name.required' => 'Nama fasilitas wajib diisi.',
            'translations.*.slug.unique' => 'Slug sudah dipakai pada bahasa ini.',
        ];
    }
}
