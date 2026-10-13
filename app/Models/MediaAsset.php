<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'disk',
    'path',
    'original_name',
    'mime_type',
    'size',
    'width',
    'height',
    'alt',
    'title',
    'caption',
    'description',
    'uploaded_by',
])]
class MediaAsset extends Model
{
    use SoftDeletes;

    public const IMAGE_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    public const DOCUMENT_MIMES = [
        'application/pdf' => 'pdf',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(MediaVariant::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isImage(): bool
    {
        return array_key_exists($this->mime_type, self::IMAGE_MIMES);
    }

    public function isDocument(): bool
    {
        return array_key_exists($this->mime_type, self::DOCUMENT_MIMES);
    }

    /** URL publik file (atau varian tertentu bila diminta & ada). */
    public function url(?string $variant = null): string
    {
        if ($variant !== null) {
            $variantModel = $this->variants->firstWhere('variant', $variant);

            if ($variantModel !== null) {
                return Storage::disk($this->disk)->url($variantModel->path);
            }
        }

        return Storage::disk($this->disk)->url($this->path);
    }

    /** URL thumbnail bila ada; null untuk non-gambar tanpa varian. */
    public function thumbnailUrl(): ?string
    {
        return $this->variants->firstWhere('variant', 'thumb')?->path
            ? $this->url('thumb')
            : null;
    }
}

