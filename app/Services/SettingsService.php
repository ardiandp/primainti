<?php

namespace App\Services;

use App\Models\MediaAsset;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Pengaturan global situs (non-secret).
 *
 * Kunci TETAP didefinisikan di DEFINITIONS — form admin hanya menyimpan
 * kunci yang dikenal sehingga secret/API key tidak bisa masuk lewat UI.
 * Nilai di-cache selamanya dan di-flush setiap penulisan.
 */
class SettingsService
{
    public const CACHE_KEY = 'site_settings_map';

    /**
     * @return array<string, array<string, array{type: string, label: string, max?: int}>>
     */
    public static function definitions(): array
    {
        return [
            'Identitas' => [
                'tagline_id' => ['type' => 'text', 'label' => 'Tagline (ID)', 'max' => 255],
                'tagline_en' => ['type' => 'text', 'label' => 'Tagline (EN)', 'max' => 255],
                'logo_id' => ['type' => 'media_image', 'label' => 'Logo'],
                'favicon_id' => ['type' => 'media_image', 'label' => 'Favicon'],
            ],
            'Kontak' => [
                'contact_phone' => ['type' => 'text', 'label' => 'Telepon', 'max' => 50],
                'contact_whatsapp' => ['type' => 'text', 'label' => 'WhatsApp', 'max' => 50],
                'contact_email' => ['type' => 'email', 'label' => 'Email', 'max' => 255],
                'address' => ['type' => 'textarea', 'label' => 'Alamat', 'max' => 1000],
                'opening_hours' => ['type' => 'textarea', 'label' => 'Jam operasional', 'max' => 1000],
            ],
            'Media Sosial' => [
                'social_facebook' => ['type' => 'url', 'label' => 'Facebook URL', 'max' => 500],
                'social_instagram' => ['type' => 'url', 'label' => 'Instagram URL', 'max' => 500],
                'social_linkedin' => ['type' => 'url', 'label' => 'LinkedIn URL', 'max' => 500],
                'social_youtube' => ['type' => 'url', 'label' => 'YouTube URL', 'max' => 500],
            ],
            'CTA Global' => [
                'cta_label_id' => ['type' => 'text', 'label' => 'Label CTA (ID)', 'max' => 100],
                'cta_label_en' => ['type' => 'text', 'label' => 'Label CTA (EN)', 'max' => 100],
                'cta_url' => ['type' => 'safe_url', 'label' => 'Tautan CTA', 'max' => 500],
            ],
            'SEO Default' => [
                'seo_default_title' => ['type' => 'text', 'label' => 'Judul default', 'max' => 255],
                'seo_default_description' => ['type' => 'textarea', 'label' => 'Deskripsi default', 'max' => 500],
            ],
        ];
    }

    /** @return array<string, string> key => type */
    public static function knownKeys(): array
    {
        $keys = [];

        foreach (self::definitions() as $fields) {
            foreach ($fields as $key => $meta) {
                $keys[$key] = $meta['type'];
            }
        }

        return $keys;
    }

    /** @return array<string, ?string> */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $stored = SiteSetting::whereIn('key', array_keys(self::knownKeys()))
                ->pluck('value', 'key')
                ->all();

            $map = [];

            foreach (array_keys(self::knownKeys()) as $key) {
                $map[$key] = $stored[$key] ?? null;
            }

            return $map;
        });
    }

    public function get(string $key, ?string $default = null): ?string
    {
        if (! array_key_exists($key, self::knownKeys())) {
            return $default;
        }

        return $this->all()[$key] ?? $default;
    }

    /** @param array<string, mixed> $values */
    public function setMany(array $values): void
    {
        $known = self::knownKeys();

        foreach ($values as $key => $value) {
            if (! array_key_exists($key, $known)) {
                continue;
            }

            $normalized = is_string($value) ? trim($value) : $value;
            $normalized = $normalized === '' ? null : $normalized;

            SiteSetting::updateOrCreate(['key' => $key], ['value' => $normalized]);
        }

        Cache::forget(self::CACHE_KEY);
    }

    /** @var array<string, ?MediaAsset> */
    private array $mediaMemo = [];

    public function media(string $key): ?MediaAsset
    {
        if (array_key_exists($key, $this->mediaMemo)) {
            return $this->mediaMemo[$key];
        }

        $id = $this->get($key);

        return $this->mediaMemo[$key] = $id ? MediaAsset::find($id) : null;
    }

    /** @return array<int, array{key: string, label: string, url: string}> */
    public function socialLinks(): array
    {
        $links = [];

        foreach (['facebook', 'instagram', 'linkedin', 'youtube'] as $network) {
            $url = $this->get("social_{$network}");

            if ($url) {
                $links[] = ['key' => $network, 'label' => ucfirst($network), 'url' => $url];
            }
        }

        return $links;
    }
}
