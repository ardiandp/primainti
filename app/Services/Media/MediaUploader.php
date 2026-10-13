<?php

namespace App\Services\Media;

use App\Models\MediaAsset;
use App\Models\MediaVariant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Upload media aman: allowlist MIME, nama file dinamis (bukan nama asli),
 * batas ukuran per jenis, dimensi terdeteksi, dan thumbnail dasar (GD).
 */
class MediaUploader
{
    public const MAX_IMAGE_BYTES = 5_242_880; // 5 MB

    public const MAX_DOCUMENT_BYTES = 10_485_760; // 10 MB

    public const THUMB_MAX_DIMENSION = 400;

    public function __construct(
        private readonly string $disk = 'public',
    ) {
    }

    /** @return array<string, string> mime => ekstensi */
    public static function allowedMimeTypes(): array
    {
        return array_merge(MediaAsset::IMAGE_MIMES, MediaAsset::DOCUMENT_MIMES);
    }

    /**
     * @throws ValidationException
     */
    public function upload(UploadedFile $file, ?User $uploader = null, ?string $title = null): MediaAsset
    {
        $this->validate($file);

        $mime = (string) $file->getMimeType();
        $extension = self::allowedMimeTypes()[$mime];
        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $filename = uniqid('m_', false).($safeName !== '' ? '-'.$safeName : '').'.'.$extension;
        $directory = 'media/'.now()->format('Y/m');

        $path = $file->storeAs($directory, $filename, $this->disk);

        $width = null;
        $height = null;

        if (str_starts_with($mime, 'image/')) {
            [$width, $height] = @getimagesize($file->getPathname()) ?: [null, null];
        }

        $asset = MediaAsset::create([
            'disk' => $this->disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'size' => (int) $file->getSize(),
            'width' => $width,
            'height' => $height,
            'alt' => null,
            'title' => $title,
            'uploaded_by' => $uploader?->getKey(),
        ]);

        if ($asset->isImage()) {
            $this->generateThumbnail($asset);
        }

        return $asset;
    }

    /**
     * @throws ValidationException
     */
    private function validate(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'files' => 'File gagal diunggah. Periksa koneksi dan coba lagi.',
            ]);
        }

        $mime = (string) $file->getMimeType();

        if (! array_key_exists($mime, self::allowedMimeTypes())) {
            throw ValidationException::withMessages([
                'files' => "Jenis file tidak diizinkan ({$mime}). Yang diizinkan: JPG, PNG, WebP, GIF, dan PDF.",
            ]);
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        $expected = self::allowedMimeTypes()[$mime];

        $extensionMatches = $extension === $expected
            || ($expected === 'jpg' && $extension === 'jpeg');

        if (! $extensionMatches) {
            throw ValidationException::withMessages([
                'files' => "Ekstensi file (.{$extension}) tidak cocok dengan jenis berkasnya.",
            ]);
        }

        $maxBytes = str_starts_with($mime, 'image/')
            ? self::MAX_IMAGE_BYTES
            : self::MAX_DOCUMENT_BYTES;

        if ($file->getSize() > $maxBytes) {
            $maxMb = $maxBytes / 1_048_576;
            throw ValidationException::withMessages([
                'files' => "Ukuran file melebihi batas {$maxMb} MB.",
            ]);
        }
    }

    private function generateThumbnail(MediaAsset $asset): void
    {
        $absolutePath = Storage::disk($asset->disk)->path($asset->path);

        $source = match ($asset->mime_type) {
            'image/jpeg' => @imagecreatefromjpeg($absolutePath),
            'image/png' => @imagecreatefrompng($absolutePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolutePath) : false,
            'image/gif' => @imagecreatefromgif($absolutePath),
            default => false,
        };

        if ($source === false) {
            return; // thumbnail gagal dibuat; file asli tetap tersimpan
        }

        $width = imagesx($source);
        $height = imagesy($source);

        $scale = min(1, self::THUMB_MAX_DIMENSION / max($width, $height));
        $thumbWidth = max(1, (int) round($width * $scale));
        $thumbHeight = max(1, (int) round($height * $scale));

        $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);

        // PNG/GIF/WebP: pertahankan transparansi
        if (in_array($asset->mime_type, ['image/png', 'image/gif', 'image/webp'], true)) {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
            imagefilledrectangle($thumb, 0, 0, $thumbWidth, $thumbHeight, $transparent);
        }

        imagecopyresampled(
            $thumb,
            $source,
            0,
            0,
            0,
            0,
            $thumbWidth,
            $thumbHeight,
            $width,
            $height
        );

        $directory = dirname($asset->path);
        $basename = pathinfo($asset->path, PATHINFO_FILENAME);
        $thumbFilename = 'thumb_'.$basename.'.'.$this->extensionFor($asset->mime_type);
        $thumbPath = $directory.'/'.$thumbFilename;

        ob_start();

        match ($asset->mime_type) {
            'image/jpeg' => imagejpeg($thumb, null, 82),
            'image/png' => imagepng($thumb, null, 6),
            'image/webp' => function_exists('imagewebp') ? imagewebp($thumb, null, 82) : imagepng($thumb, null, 6),
            'image/gif' => imagegif($thumb),
        };

        $binary = ob_get_clean();

        imagedestroy($source);
        imagedestroy($thumb);

        if ($binary === false || $binary === '') {
            return;
        }

        Storage::disk($asset->disk)->put($thumbPath, $binary);

        MediaVariant::updateOrCreate(
            ['media_asset_id' => $asset->id, 'variant' => 'thumb'],
            [
                'path' => $thumbPath,
                'mime_type' => $asset->mime_type,
                'size' => strlen($binary),
                'width' => $thumbWidth,
                'height' => $thumbHeight,
            ]
        );
    }

    private function extensionFor(string $mime): string
    {
        return MediaAsset::IMAGE_MIMES[$mime] ?? 'jpg';
    }
}
