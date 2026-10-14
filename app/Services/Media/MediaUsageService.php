<?php

namespace App\Services\Media;

use App\Models\MediaAsset;
use Illuminate\Support\Facades\DB;

/**
 * Pemeriksaan referensi pemakaian media oleh modul lain.
 * Saat modul baru menambah referensi ke media_assets, cukup daftarkan
 * di sources() — proteksi hapus otomatis mengikuti.
 */
class MediaUsageService
{
    /**
     * @return array<string, array{label: string, column: string}>
     */
    public function sources(): array
    {
        return [
            'product_media' => ['label' => 'Produk', 'column' => 'media_asset_id'],
            'facility_media' => ['label' => 'Fasilitas', 'column' => 'media_asset_id'],
            'gallery_items' => ['label' => 'Item Galeri', 'column' => 'media_asset_id'],
            'page_translations' => ['label' => 'Halaman (OG image)', 'column' => 'og_image_id'],
        ];
    }

    /**
     * @return array<int, array{table: string, label: string, count: int}>
     */
    public function usages(MediaAsset $asset): array
    {
        $results = [];

        foreach ($this->sources() as $table => $meta) {
            $count = DB::table($table)->where($meta['column'], $asset->getKey())->count();

            if ($count > 0) {
                $results[] = [
                    'table' => $table,
                    'label' => $meta['label'],
                    'count' => $count,
                ];
            }
        }

        return $results;
    }

    public function isReferenced(MediaAsset $asset): bool
    {
        return $this->referenceCount($asset) > 0;
    }

    public function referenceCount(MediaAsset $asset): int
    {
        return collect($this->usages($asset))->sum('count');
    }
}
