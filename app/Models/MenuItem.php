<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    public const LINK_TYPES = ['url', 'page', 'product_category', 'route'];

    protected $fillable = [
        'menu_id',
        'parent_id',
        'page_id',
        'product_category_id',
        'route_name',
        'link_type',
        'title',
        'url',
        'opens_in_new_tab',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'opens_in_new_tab' => 'boolean',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(MenuItemTranslation::class);
    }

    public function labelFor(string $locale): string
    {
        return $this->translations->firstWhere('locale', $locale)?->title
            ?? $this->translations->first()?->title
            ?? $this->title;
    }
}
