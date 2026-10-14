<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['status', 'sort_order', 'published_at'])]
class Facility extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(FacilityTranslation::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(FacilityMedia::class);
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
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function translationFor(string $locale): ?FacilityTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    /** Foto sampul: media pertama menurut position. */
    public function coverMedia(): ?FacilityMedia
    {
        return $this->media->sortBy([['position', 'asc'], ['id', 'asc']])->first();
    }
}
