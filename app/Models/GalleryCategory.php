<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['status'])]
class GalleryCategory extends Model
{
    use SoftDeletes;

    public function translations(): HasMany
    {
        return $this->hasMany(GalleryCategoryTranslation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(GalleryItem::class);
    }

    /** Konten publik hanya boleh mengambil status published. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
