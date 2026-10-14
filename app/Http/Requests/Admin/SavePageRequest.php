<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Validasi simpan halaman CMS (store + update).
 *
 * - Terjemahan dibatasi ke locale id/en; entri yang seluruh field-nya
 *   kosong dibuang. Minimal satu locale wajib terisi.
 * - Slug kosong otomatis diisi dari judul; slug unik per locale.
 * - Section mengikuti locale terjemahannya (position + visibility).
 */
class SavePageRequest extends FormRequest
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
                ->except(['og_image_id'])
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
            'template' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:draft,published,archived'],
            'published_at' => ['nullable', 'date'],
            'translations' => ['required', 'array', 'min:1'],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['required', 'string', 'max:255', 'alpha_dash'],
            'translations.*.summary' => ['nullable', 'string', 'max:2000'],
            'translations.*.content' => ['nullable', 'string'],
            'translations.*.seo_title' => ['nullable', 'string', 'max:255'],
            'translations.*.seo_description' => ['nullable', 'string', 'max:500'],
            'translations.*.og_image_id' => ['nullable', 'exists:media_assets,id'],
            'sections' => ['nullable', 'array'],
            'sections.*' => ['nullable', 'array'],
            'sections.*.*.id' => ['nullable', 'exists:page_sections,id'],
            'sections.*.*.heading' => ['nullable', 'string', 'max:255'],
            'sections.*.*.body' => ['nullable', 'string'],
            'sections.*.*.position' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'sections.*.*.is_visible' => ['nullable', 'boolean'],
            'sections.*.*._remove' => ['nullable', 'boolean'],
        ];

        /** @var \App\Models\Page|null $page */
        $page = $this->route('page');

        foreach (array_keys($this->input('translations', [])) as $locale) {
            $ignoreId = $page?->translations->firstWhere('locale', $locale)?->id;

            $unique = Rule::unique('page_translations', 'slug')
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
            'translations.*.title.required' => 'Judul wajib diisi.',
            'translations.*.slug.unique' => 'Slug sudah dipakai pada bahasa ini.',
        ];
    }
}
