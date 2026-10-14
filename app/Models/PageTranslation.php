<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'page_id',
    'locale',
    'title',
    'slug',
    'summary',
    'content',
    'seo_title',
    'seo_description',
    'og_image_id',
])]
class PageTranslation extends Model
{
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'og_image_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->ordered();
    }
}
