<?php

namespace App\Services;

use App\Models\PageTranslation;
use App\Models\ProductTranslation;
use Illuminate\Support\Facades\Route;

/**
 * URL padanan bahasa lain untuk switcher.
 *
 * Untuk halaman konten (page/produk), cari terjemahan yang sesuai di bahasa
 * target (fallback bila perlu); untuk halaman indeks, tukar segmen locale
 * dengan query tetap dipertahankan.
 */
class LocaleUrl
{
    public function swap(string $target): string
    {
        if (! in_array($target, ['id', 'en'], true)) {
            $target = config('app.fallback_locale', 'en');
        }

        $route = request()->route();

        if ($route === null) {
            return url("/{$target}");
        }

        $name = $route->getName();
        $params = $route->parameters();

        $translated = match ($name) {
            'public.pages.show' => $this->pageUrl($params, $target),
            'public.products.show' => $this->productUrl($params, $target),
            default => null,
        };

        if ($translated !== null) {
            return $translated;
        }

        // Rute ber-segmen locale: tukar locale, pertahankan parameter + query.
        if (isset($params['locale']) && in_array($params['locale'], ['id', 'en'], true)) {
            $params['locale'] = $target;

            if (is_string($name) && Route::has($name)) {
                $url = route($name, $params);
                $query = request()->getQueryString();

                return $query ? "{$url}?{$query}" : $url;
            }
        }

        return url("/{$target}");
    }

    private function pageUrl(array $params, string $target): ?string
    {
        $translation = PageTranslation::where('locale', $params['locale'] ?? null)
            ->where('slug', $params['slug'] ?? null)
            ->first();

        if ($translation === null) {
            return null;
        }

        $page = $translation->page;

        if ($page === null || $page->status !== 'published') {
            return null;
        }

        $other = $page->translations->firstWhere('locale', $target)
            ?? $page->translations->firstWhere('locale', config('app.fallback_locale', 'en'));

        if ($other === null) {
            return null;
        }

        return route('public.pages.show', [$other->locale, $other->slug]);
    }

    private function productUrl(array $params, string $target): ?string
    {
        $translation = ProductTranslation::where('locale', $params['locale'] ?? null)
            ->where('slug', $params['slug'] ?? null)
            ->first();

        if ($translation === null) {
            return null;
        }

        $product = $translation->product;

        if ($product === null || $product->status !== 'published') {
            return null;
        }

        $other = $product->translations->firstWhere('locale', $target)
            ?? $product->translations->firstWhere('locale', config('app.fallback_locale', 'en'));

        if ($other === null) {
            return null;
        }

        return route('public.products.show', [$target, $other->slug]);
    }
}
