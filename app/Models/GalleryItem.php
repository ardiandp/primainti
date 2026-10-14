<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['gallery_category_id', 'media_asset_id', 'alt_override', 'taken_at', 'position', 'status', 'published_at'])]
class GalleryItem extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'taken_at' => 'date',
            'position' => 'integer',
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

    public function scopeLive(Builder $query): Builder
    {
        return $query->published()
            ->where(function (Builder $sub) {
                $sub->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }

    public function translationFor(string $locale): ?GalleryItemTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    /**
     * Alt text efektif: override item → alt aset → caption terjemahan.
     * Selalu string (bisa kosong) agar template aman.
     */
    public function altFor(?string $caption = null): string
    {
        return $this->alt_override
            ?? $this->asset?->alt
            ?? $this->asset?->title
            ?? $caption
            ?? '';
    }
}
