<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Validasi simpan kategori produk (store + update).
 * Nama + slug per locale id/en; slug unik per locale, auto dari nama.
 */
class SaveProductCategoryRequest extends FormRequest
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

            if (($entry['name'] ?? '') === '' && ($entry['slug'] ?? '') === '') {
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
            'status' => ['required', 'in:draft,published'],
            'translations' => ['required', 'array', 'min:1'],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['required', 'string', 'max:255', 'alpha_dash'],
        ];

        /** @var \App\Models\ProductCategory|null $category */
        $category = $this->route('product_category');

        foreach (array_keys($this->input('translations', [])) as $locale) {
            $ignoreId = $category?->translations->firstWhere('locale', $locale)?->id;

            $unique = Rule::unique('product_category_translations', 'slug')
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
            'translations.*.name.required' => 'Nama kategori wajib diisi.',
            'translations.*.slug.unique' => 'Slug sudah dipakai pada bahasa ini.',
        ];
    }
}
