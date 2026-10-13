<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['gallery_category_id', 'media_asset_id', 'status', 'published_at'])]
class GalleryItem extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(GalleryCategory::class, 'gallery_category_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(GalleryItemTranslation::class);
    }

    /** Konten publik hanya boleh mengambil status published. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
