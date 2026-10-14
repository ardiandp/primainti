<?php

namespace App\Http\Requests\Admin;

use App\Models\MenuItem;
use App\Services\MenuService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi item menu: parent satu level dalam menu yang sama,
 * target wajib sesuai tipe, URL bebas harus aman.
 */
class SaveMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('content.manage');
    }

    protected function prepareForValidation(): void
    {
        $translations = [];

        foreach (['id', 'en'] as $locale) {
            $entry = $this->input("translations.{$locale}");

            if (! is_array($entry)) {
                continue;
            }

            $title = trim((string) ($entry['title'] ?? ''));

            if ($title !== '') {
                $translations[$locale] = ['title' => $title];
            }
        }

        $this->merge(['translations' => $translations]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var MenuItem|null $item */
        $item = $this->route('menu_item');
        $menuId = $this->input('menu_id', $item?->menu_id);

        return [
            'menu_id' => ['required', 'exists:menus,id'],
            'parent_id' => [
                'nullable',
                'exists:menu_items,id',
                function ($attribute, $value, $fail) use ($menuId, $item) {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $parent = MenuItem::find($value);

                    if ($parent === null || (int) $parent->menu_id !== (int) $menuId) {
                        $fail('Induk harus item dalam menu yang sama.');

                        return;
                    }

                    if ($parent->parent_id !== null) {
                        $fail('Menu hanya mendukung satu level sub-menu.');

                        return;
                    }

                    if ($item && (int) $item->id === (int) $value) {
                        $fail('Item tidak bisa menjadi induk dirinya sendiri.');
                    }
                },
            ],
            'title' => ['required', 'string', 'max:255'],
            'translations' => ['nullable', 'array'],
            'translations.*.title' => ['nullable', 'string', 'max:255'],
            'link_type' => ['required', Rule::in(MenuItem::LINK_TYPES)],
            'url' => [
                Rule::requiredIf($this->input('link_type') === 'url'),
                'nullable', 'string', 'max:500',
                function ($attribute, $value, $fail) {
                    if ($value !== null && $value !== '' && ! MenuService::isSafeUrl($value)) {
                        $fail('URL harus http(s), /path relatif, #anchor, mailto:, atau tel:. javascript: dan data: ditolak.');
                    }
                },
            ],
            'page_id' => [
                Rule::requiredIf($this->input('link_type') === 'page'),
                'nullable', 'exists:pages,id',
            ],
            'product_category_id' => [
                Rule::requiredIf($this->input('link_type') === 'product_category'),
                'nullable', 'exists:product_categories,id',
            ],
            'route_name' => [
                Rule::requiredIf($this->input('link_type') === 'route'),
                'nullable', 'string', Rule::in(MenuService::ROUTE_ALLOWLIST),
            ],
            'opens_in_new_tab' => ['nullable', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }
}
