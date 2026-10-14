<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

/**
 * Katalog + detail produk publik (provisional Phase 5; disempurnakan Phase 8).
 *
 * Hanya produk live (published + jadwal) dari kategori published yang tampil.
 * Locale dari segmen URL; konten mengikuti locale dengan fallback.
 */
class ProductCatalogController extends Controller
{
    public function index(Request $request, string $locale)
    {
        abort_unless(in_array($locale, ['id', 'en'], true), 404);

        $validated = $request->validate([
            'kategori' => ['nullable', 'string', 'max:255'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $categories = ProductCategory::published()
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

            // Slug kategori tak dikenal → anggap tidak ada hasil, bukan 404.
            if ($activeCategory === null) {
                $validated['kategori'] = null;
            }
        }

        $query = Product::live()
            ->with(['translations', 'category.translations', 'media.asset'])
            ->when($activeCategory, fn ($q) => $q->where('product_category_id', $activeCategory->id))
            ->when($validated['q'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('sku', 'like', "%{$search}%")
                        ->orWhereHas('translations', function ($t) use ($search) {
                            $t->where('name', 'like', "%{$search}%")
                                ->orWhere('short_description', 'like', "%{$search}%");
                        });
                });
            })
            ->ordered();

        // Kategori draft tidak boleh membocorkan produknya via filter langsung.
        $query->whereHas('category', fn ($q) => $q->published());

        return view('products.index', [
            'locale' => $locale,
            'products' => $query->paginate(12)->withQueryString(),
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'filters' => $validated,
        ]);
    }

    public function show(string $locale, string $slug)
    {
        abort_unless(in_array($locale, ['id', 'en'], true), 404);

        $translation = $this->findLive($locale, $slug)
            ?? $this->findLive(config('app.fallback_locale', 'en'), $slug);

        abort_if($translation === null, 404);

        $translation->loadMissing(['ogImage']);

        $product = $translation->product;

        $product->loadMissing([
            'category.translations',
            'specifications.translations',
            'media.asset',
            'documents.asset',
        ]);

        $related = Product::live()
            ->where('product_category_id', $product->product_category_id)
            ->whereKeyNot($product->id)
            ->whereHas('category', fn ($q) => $q->published())
            ->with(['translations', 'category.translations', 'media.asset'])
            ->ordered()
            ->limit(4)
            ->get();

        return view('products.show', [
            'locale' => $translation->locale,
            'product' => $product,
            'translation' => $translation,
            'related' => $related,
        ]);
    }

    private function findLive(string $locale, string $slug): ?\App\Models\ProductTranslation
    {
        return \App\Models\ProductTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->whereHas('product', function ($query) {
                $query->live()->whereHas('category', fn ($q) => $q->published());
            })
            ->first();
    }
}
