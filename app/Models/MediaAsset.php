<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'disk',
    'path',
    'original_name',
    'mime_type',
    'size',
    'width',
    'height',
    'alt',
    'uploaded_by',
])]
class MediaAsset extends Model
{
    use SoftDeletes;

    public function variants(): HasMany
    {
        return $this->hasMany(MediaVariant::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
