<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['gallery_item_id', 'locale', 'title', 'slug', 'caption'])]
class GalleryItemTranslation extends Model
{
    public function item(): BelongsTo
    {
        return $this->belongsTo(GalleryItem::class, 'gallery_item_id');
    }
}
