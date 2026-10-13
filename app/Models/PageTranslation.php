<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['page_id', 'locale', 'title', 'slug', 'summary', 'content'])]
class PageTranslation extends Model
{
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
