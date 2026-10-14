<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Validasi simpan produk (store + update).
 *
 * Terjemahan id/en (nama + slug unik per locale, auto-slug), spesifikasi
 * label/nilai/unit, galeri media + primary, dokumen, dan SEO.
 */
class SaveProductRequest extends FormRequest
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
            'product_category_id' => ['required', 'exists:product_categories,id'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($this->route('product'))],
            'status' => ['required', 'in:draft,published,archived'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'published_at' => ['nullable', 'date'],
            'translations' => ['required', 'array', 'min:1'],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['required', 'string', 'max:255', 'alpha_dash'],
            'translations.*.short_description' => ['nullable', 'string', 'max:500'],
            'translations.*.description' => ['nullable', 'string'],
            'translations.*.functions' => ['nullable', 'string'],
            'translations.*.applications' => ['nullable', 'string'],
            'translations.*.seo_title' => ['nullable', 'string', 'max:255'],
            'translations.*.seo_description' => ['nullable', 'string', 'max:500'],
            'translations.*.og_image_id' => ['nullable', 'exists:media_assets,id'],
            'specs' => ['nullable', 'array'],
            'specs.*.id' => ['nullable', 'exists:product_specifications,id'],
            'specs.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'specs.*.unit' => ['nullable', 'string', 'max:50'],
            'specs.*.translations' => ['nullable', 'array'],
            'specs.*.translations.*.label' => ['nullable', 'string', 'max:255'],
            'specs.*.translations.*.value' => ['nullable', 'string', 'max:2000'],
            'specs.*._remove' => ['nullable', 'boolean'],
            'media' => ['nullable', 'array'],
            'media.*.id' => ['nullable', 'exists:product_media,id'],
            'media.*.media_asset_id' => ['nullable', 'exists:media_assets,id'],
            'media.*.position' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'media.*._remove' => ['nullable', 'boolean'],
            'primary_media_asset_id' => ['nullable', 'exists:media_assets,id'],
            'documents' => ['nullable', 'array'],
            'documents.*.id' => ['nullable', 'exists:product_documents,id'],
            'documents.*.media_asset_id' => ['nullable', 'exists:media_assets,id'],
            'documents.*.type' => ['nullable', Rule::in(ProductDocument::TYPES)],
            'documents.*.label' => ['nullable', 'string', 'max:255'],
            'documents.*.position' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'documents.*._remove' => ['nullable', 'boolean'],
        ];

        /** @var \App\Models\Product|null $product */
        $product = $this->route('product');

        foreach (array_keys($this->input('translations', [])) as $locale) {
            $ignoreId = $product?->translations->firstWhere('locale', $locale)?->id;

            $unique = Rule::unique('product_translations', 'slug')
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
            'translations.*.name.required' => 'Nama produk wajib diisi.',
            'translations.*.slug.unique' => 'Slug sudah dipakai pada bahasa ini.',
        ];
    }
}
