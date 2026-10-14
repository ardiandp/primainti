<?php

namespace App\Http\Controllers;

use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use Illuminate\Http\Request;

/**
 * Galeri publik (provisional Phase 6; disempurnakan Phase 8).
 * Hanya item live yang medianya masih ada; kategori draft menyembunyikan itemnya.
 */
class GalleryController extends Controller
{
    public function index(Request $request, string $locale)
    {
        abort_unless(in_array($locale, ['id', 'en'], true), 404);

        $validated = $request->validate([
            'kategori' => ['nullable', 'string', 'max:255'],
        ]);

        $categories = GalleryCategory::published()
            ->with('translations')
            ->orderBy('id')
            ->get();

        $activeCategory = null;

        if (! empty($validated['kategori'])) {
            $activeCategory = $categories->first(function ($category) use ($validated, $locale) {
                $translation = $category->translationFor($locale)
                    ?? $category->translationFor(config('app.fallback_locale', 'en'));

                return $translation?->slug === $validated['kategori'];
            });

            if ($activeCategory === null) {
                $validated['kategori'] = null;
            }
        }

        $query = GalleryItem::live()
            ->with(['translations', 'asset', 'category.translations'])
            ->whereNotNull('media_asset_id')
            ->where(function ($q) {
                $q->whereNull('gallery_category_id')
                    ->orWhereHas('category', fn ($sub) => $sub->published());
            })
            ->when($activeCategory, fn ($q) => $q->where('gallery_category_id', $activeCategory->id))
            ->ordered();

        return view('gallery.index', [
            'locale' => $locale,
            'items' => $query->paginate(24)->withQueryString(),
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'filters' => $validated,
        ]);
    }
}
