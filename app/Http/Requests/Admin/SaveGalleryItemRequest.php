<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Validasi simpan item galeri (store + update).
 * Media wajib dipilih dari Media Library (tanpa upload baru).
 */
class SaveGalleryItemRequest extends FormRequest
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

            if (($entry['slug'] ?? '') === '' && ($entry['title'] ?? '') !== '') {
                $entry['slug'] = Str::slug($entry['title']);
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
            'gallery_category_id' => ['nullable', 'exists:gallery_categories,id'],
            'media_asset_id' => ['required', 'exists:media_assets,id'],
            'alt_override' => ['nullable', 'string', 'max:255'],
            'taken_at' => ['nullable', 'date'],
            'position' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'status' => ['required', 'in:draft,published,archived'],
            'published_at' => ['nullable', 'date'],
            'translations' => ['required', 'array', 'min:1'],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['required', 'string', 'max:255', 'alpha_dash'],
            'translations.*.caption' => ['nullable', 'string', 'max:2000'],
        ];

        /** @var \App\Models\GalleryItem|null $item */
        $item = $this->route('gallery_item');

        foreach (array_keys($this->input('translations', [])) as $locale) {
            $ignoreId = $item?->translations->firstWhere('locale', $locale)?->id;

            $unique = Rule::unique('gallery_item_translations', 'slug')
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
            'media_asset_id.required' => 'Pilih gambar dari Media Library.',
            'translations.required' => 'Minimal satu terjemahan (ID atau EN) wajib diisi.',
            'translations.*.title.required' => 'Judul wajib diisi.',
            'translations.*.slug.unique' => 'Slug sudah dipakai pada bahasa ini.',
        ];
    }
}
