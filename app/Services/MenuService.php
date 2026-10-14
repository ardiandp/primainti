<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/**
 * Resolusi menu CMS menjadi tree siap render.
 *
 * Item yang targetnya tidak tersedia (draft/terhapus/URL tak aman) dianggap
 * TIDAK valid: disembunyikan di publik, ditandai di admin.
 */
class MenuService
{
    /** Rute bernama yang boleh ditautkan dari menu (tanpa parameter). */
    public const ROUTE_ALLOWLIST = [
        'public.products.index',
        'public.gallery.index',
        'public.facilities.index',
    ];

    public static function isSafeUrl(?string $url): bool
    {
        if ($url === null || trim($url) === '') {
            return false;
        }

        return (bool) preg_match('/^(#|\/|https?:\/\/|mailto:|tel:)/i', trim($url));
    }

    public function menu(string $location): Menu
    {
        return Menu::firstOrCreate(['location' => $location]);
    }

    /**
     * Status target item untuk ditampilkan di admin.
     *
     * @return array{valid: bool, message: ?string}
     */
    public function validity(MenuItem $item): array
    {
        return match ($item->link_type) {
            'url' => self::isSafeUrl($item->url)
                ? ['valid' => true, 'message' => null]
                : ['valid' => false, 'message' => 'URL kosong atau tidak aman.'],
            'page' => $this->pageTarget($item) === null
                ? ['valid' => false, 'message' => 'Halaman tidak tersedia (draft/arsip/terhapus).']
                : ['valid' => true, 'message' => null],
            'product_category' => $this->categoryTarget($item) === null
                ? ['valid' => false, 'message' => 'Kategori tidak tersedia (draft/terhapus).']
                : ['valid' => true, 'message' => null],
            'route' => in_array($item->route_name, self::ROUTE_ALLOWLIST, true)
                && Route::has($item->route_name)
                ? ['valid' => true, 'message' => null]
                : ['valid' => false, 'message' => 'Rute tidak diizinkan.'],
            default => ['valid' => false, 'message' => 'Tipe tautan tidak dikenal.'],
        };
    }

    /** Hapus cache tree menu (dipanggil setiap perubahan item). */
    public static function flushTrees(): void
    {
        foreach (Menu::LOCATIONS as $location) {
            foreach (['id', 'en'] as $locale) {
                Cache::forget("menu_tree_{$location}_{$locale}");
            }
        }
    }

    /**
     * Tree item VALID untuk locale tertentu (di-cache + invalidasi eksplisit).
     *
     * @return array<int, array{label: string, url: string, blank: bool, children: array}>
     */
    public function tree(string $location, string $locale): array
    {
        return Cache::rememberForever("menu_tree_{$location}_{$locale}", function () use ($location, $locale) {
            return $this->buildTree($location, $locale);
        });
    }

    /** @return array<int, array{label: string, url: string, blank: bool, children: array}> */
    private function buildTree(string $location, string $locale): array
    {
        $menu = Menu::where('location', $location)->first();

        if ($menu === null) {
            return [];
        }

        $items = $menu->items()
            ->with(['translations', 'page.translations', 'productCategory.translations'])
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $byParent = $items->groupBy(fn ($item) => $item->parent_id ?? 0);

        $build = function ($parentId) use (&$build, $byParent, $locale) {
            $nodes = [];

            foreach ($byParent->get($parentId, []) as $item) {
                $resolved = $this->resolve($item, $locale);

                if ($resolved === null) {
                    continue;
                }

                $resolved['children'] = $item->parent_id === null
                    ? $build($item->id)
                    : [];

                $nodes[] = $resolved;
            }

            return $nodes;
        };

        return $build(0);
    }

    /** @return array{label: string, url: string, blank: bool}|null */
    public function resolve(MenuItem $item, string $locale): ?array
    {
        $url = match ($item->link_type) {
            'url' => self::isSafeUrl($item->url) ? trim((string) $item->url) : null,
            'page' => $this->pageUrl($item, $locale),
            'product_category' => $this->categoryUrl($item, $locale),
            'route' => in_array($item->route_name, self::ROUTE_ALLOWLIST, true)
                && Route::has($item->route_name)
                ? route($item->route_name, $locale)
                : null,
            default => null,
        };

        if ($url === null) {
            return null;
        }

        return [
            'label' => $item->labelFor($locale),
            'url' => $url,
            'blank' => (bool) $item->opens_in_new_tab,
        ];
    }

    private function pageTarget(MenuItem $item): ?\App\Models\Page
    {
        $page = $item->relationLoaded('page') ? $item->page : $item->page()->first();

        return $page && $page->status === 'published' ? $page : null;
    }

    private function pageUrl(MenuItem $item, string $locale): ?string
    {
        $page = $this->pageTarget($item);

        if ($page === null) {
            return null;
        }

        $translations = $page->relationLoaded('translations')
            ? $page->translations
            : $page->translations()->get();

        $translation = $translations->firstWhere('locale', $locale)
            ?? $translations->firstWhere('locale', config('app.fallback_locale', 'en'));

        if ($translation === null) {
            return null;
        }

        return route('public.pages.show', [$translation->locale, $translation->slug]);
    }

    private function categoryTarget(MenuItem $item): ?\App\Models\ProductCategory
    {
        $category = $item->relationLoaded('productCategory')
            ? $item->productCategory
            : $item->productCategory()->first();

        return $category && $category->status === 'published' ? $category : null;
    }

    private function categoryUrl(MenuItem $item, string $locale): ?string
    {
        $category = $this->categoryTarget($item);

        if ($category === null) {
            return null;
        }

        $translations = $category->relationLoaded('translations')
            ? $category->translations
            : $category->translations()->get();

        $translation = $translations->firstWhere('locale', $locale)
            ?? $translations->firstWhere('locale', config('app.fallback_locale', 'en'));

        if ($translation === null) {
            return null;
        }

        return route('public.products.index', [$locale, 'kategori' => $translation->slug]);
    }
}
