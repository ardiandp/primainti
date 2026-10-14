<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveProductRequest;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductTranslation;
use App\Services\AuditLogger;
use App\Services\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public const LOCALE_LABELS = [
        'id' => 'Bahasa Indonesia',
        'en' => 'English',
    ];

    public function __construct(
        private readonly HtmlSanitizer $sanitizer,
    ) {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:draft,published,archived'],
            'category' => ['nullable', 'integer', 'exists:product_categories,id'],
            'trash' => ['nullable', 'in:1'],
        ]);

        $query = Product::query()
            ->with(['translations', 'category.translations'])
            ->when($validated['trash'] ?? null, fn ($q) => $q->onlyTrashed())
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($validated['category'] ?? null, fn ($q, $category) => $q->where('product_category_id', $category))
            ->when($validated['q'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('sku', 'like', "%{$search}%")
                        ->orWhereHas('translations', function ($t) use ($search) {
                            $t->where('name', 'like', "%{$search}%")
                                ->orWhere('slug', 'like', "%{$search}%");
                        });
                });
            })
            ->ordered();

        return view('admin.products.index', [
            'products' => $query->paginate(20)->withQueryString(),
            'filters' => $validated,
            'categories' => ProductCategory::with('translations')->orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Product::class);

        $product = new Product(['status' => 'draft', 'sort_order' => 0]);

        return view('admin.products.create', [
            'product' => $product,
            'locales' => self::LOCALE_LABELS,
            'categories' => $this->categoryOptions(),
            'assets' => $this->assetsFor($product),
        ]);
    }

    public function store(SaveProductRequest $request)
    {
        $this->authorize('create', Product::class);

        $validated = $request->validated();

        if ($validated['status'] === 'published') {
            $this->authorize('publish', Product::class);
        }

        $product = DB::transaction(function () use ($validated) {
            $product = Product::create($this->pageAttributes($validated));

            $this->syncTranslations($product, $validated);
            $this->syncSpecs($product, $validated['specs'] ?? []);
            $this->syncMedia($product, $validated);
            $this->syncDocuments($product, $validated['documents'] ?? []);

            return $product;
        });

        AuditLogger::log('product.create', $product, null, [
            'sku' => $product->sku,
            'status' => $product->status,
        ]);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Produk dibuat.');
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        $product->load([
            'translations.ogImage',
            'specifications.translations',
            'media.asset',
            'documents.asset',
        ]);

        return view('admin.products.edit', [
            'product' => $product,
            'locales' => self::LOCALE_LABELS,
            'categories' => $this->categoryOptions(),
            'assets' => $this->assetsFor($product),
        ]);
    }

    public function update(SaveProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validated();

        if ($validated['status'] === 'published' && $product->status !== 'published') {
            $this->authorize('publish', $product);
        }

        $old = ['status' => $product->status, 'sku' => $product->sku];

        DB::transaction(function () use ($product, $validated) {
            $product->update($this->pageAttributes($validated, $product));

            $this->syncTranslations($product, $validated);
            $this->syncSpecs($product, $validated['specs'] ?? []);
            $this->syncMedia($product, $validated);
            $this->syncDocuments($product, $validated['documents'] ?? []);
        });

        AuditLogger::log('product.update', $product, $old, ['status' => $product->status]);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Produk diperbarui.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $product->delete();

        AuditLogger::log('product.delete', $product, ['deleted_at' => now()->toDateTimeString()], null);

        return redirect()->route('admin.products.index')->with('status', 'Produk dipindahkan ke trash.');
    }

    public function restore(int $product)
    {
        $model = Product::withTrashed()->findOrFail($product);

        $this->authorize('update', $model);

        $model->restore();

        AuditLogger::log('product.restore', $model, null, ['restored' => true]);

        return back()->with('status', 'Produk dipulihkan.');
    }

    public function forceDestroy(int $product)
    {
        $model = Product::withTrashed()->findOrFail($product);

        $this->authorize('delete', $model);

        // Pivot galeri/dokumen ikut terhapus; file media TIDAK dihapus
        // (dipakai lintas modul, dijaga MediaUsageService).
        $model->translations()->delete();
        $model->specifications()->delete();
        $model->media()->delete();
        $model->documents()->delete();
        $model->forceDelete();

        AuditLogger::log('product.force_delete', $model, ['id' => $model->id], null);

        return redirect()->route('admin.products.index', ['trash' => 1])->with('status', 'Produk dihapus permanen.');
    }

    public function preview(Request $request, Product $product)
    {
        $this->authorize('view', $product);

        $locale = $request->validate([
            'locale' => ['nullable', 'in:id,en'],
        ])['locale'] ?? config('app.fallback_locale', 'en');

        $product->load([
            'translations.ogImage',
            'specifications.translations',
            'media.asset',
            'documents.asset',
            'category.translations',
        ]);

        $translation = $product->translationFor($locale)
            ?? $product->translationFor(config('app.fallback_locale', 'en'))
            ?? $product->translations->first();

        abort_if($translation === null, 404, 'Produk belum memiliki terjemahan.');

        // Produk terkait untuk pratinjau: kategori sama, live, kecuali diri sendiri.
        $related = Product::live()
            ->where('product_category_id', $product->product_category_id)
            ->whereKeyNot($product->id)
            ->with(['translations', 'media.asset'])
            ->ordered()
            ->limit(4)
            ->get();

        return view('admin.products.preview', [
            'product' => $product,
            'translation' => $translation,
            'locale' => $translation->locale,
            'related' => $related,
        ]);
    }

    /** @return array<string, mixed> */
    private function pageAttributes(array $validated, ?Product $product = null): array
    {
        return [
            'product_category_id' => $validated['product_category_id'],
            'sku' => $validated['sku'] ?? null,
            'status' => $validated['status'],
            'is_featured' => (bool) ($validated['is_featured'] ?? false),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'published_at' => $this->resolvePublishedAt($validated, $product),
        ];
    }

    private function resolvePublishedAt(array $validated, ?Product $product = null): ?string
    {
        if ($validated['status'] !== 'published') {
            return $product?->published_at?->toDateTimeString();
        }

        return $validated['published_at'] ?? $product?->published_at?->toDateTimeString() ?? now()->toDateTimeString();
    }

    private function syncTranslations(Product $product, array $validated): void
    {
        $product->loadMissing('translations');

        foreach ($validated['translations'] as $locale => $data) {
            $translation = $product->translations->firstWhere('locale', $locale);

            $attributes = [
                'name' => $data['name'],
                'slug' => $data['slug'],
                'short_description' => $data['short_description'] ?? null,
                'description' => $this->sanitizer->clean($data['description'] ?? null),
                'functions' => $this->sanitizer->clean($data['functions'] ?? null),
                'applications' => $this->sanitizer->clean($data['applications'] ?? null),
                'seo_title' => $data['seo_title'] ?? null,
                'seo_description' => $data['seo_description'] ?? null,
                'og_image_id' => $data['og_image_id'] ?? null,
            ];

            if ($translation === null) {
                $product->translations()->create(array_merge($attributes, ['locale' => $locale]));
            } else {
                $translation->update($attributes);
            }
        }
    }

    /** @param array<int, array<string, mixed>> $rows */
    private function syncSpecs(Product $product, array $rows): void
    {
        foreach ($rows as $row) {
            if (! empty($row['_remove'])) {
                if (! empty($row['id'])) {
                    $product->specifications()->whereKey($row['id'])->delete();
                }

                continue;
            }

            $filled = collect($row['translations'] ?? [])
                ->filter(fn ($t) => trim((string) ($t['label'] ?? '')) !== '' || trim((string) ($t['value'] ?? '')) !== '');

            if ($filled->isEmpty()) {
                continue;
            }

            $attributes = [
                'sort_order' => (int) ($row['sort_order'] ?? 0),
                'unit' => ($row['unit'] ?? '') !== '' ? trim((string) $row['unit']) : null,
            ];

            if (! empty($row['id'])) {
                $spec = $product->specifications()->whereKey($row['id'])->first();

                if ($spec === null) {
                    continue;
                }

                $spec->update($attributes);
            } else {
                $spec = $product->specifications()->create($attributes);
            }

            $spec->loadMissing('translations');

            foreach ($filled as $locale => $t) {
                if (! in_array($locale, SaveProductRequest::LOCALES, true)) {
                    continue;
                }

                $translation = $spec->translations->firstWhere('locale', $locale);
                $values = [
                    'label' => trim((string) ($t['label'] ?? '')) ?: null,
                    'value' => trim((string) ($t['value'] ?? '')) ?: null,
                ];

                if ($translation === null) {
                    $spec->translations()->create(array_merge($values, ['locale' => $locale]));
                } else {
                    $translation->update($values);
                }
            }
        }
    }

    private function syncMedia(Product $product, array $validated): void
    {
        $attached = [];

        foreach ($validated['media'] ?? [] as $row) {
            if (! empty($row['_remove'])) {
                if (! empty($row['id'])) {
                    $product->media()->whereKey($row['id'])->delete();
                }

                continue;
            }

            if (empty($row['media_asset_id'])) {
                continue;
            }

            // Unik (product_id, media_asset_id): updateOrCreate menelan duplikat.
            $product->media()->updateOrCreate(
                ['media_asset_id' => $row['media_asset_id']],
                ['position' => (int) ($row['position'] ?? 0)]
            );

            $attached[] = (int) $row['media_asset_id'];
        }

        $product->media()->update(['is_primary' => false]);

        $primaryId = isset($validated['primary_media_asset_id'])
            ? (int) $validated['primary_media_asset_id']
            : null;

        if ($primaryId !== null && in_array($primaryId, $attached, true)) {
            $product->media()->where('media_asset_id', $primaryId)->update(['is_primary' => true]);
        } elseif ($product->media()->exists()) {
            // Jaminan selalu ada satu media utama bila galeri tidak kosong.
            $product->media()->orderBy('position')->orderBy('id')->limit(1)->update(['is_primary' => true]);
        }
    }

    /** @param array<int, array<string, mixed>> $rows */
    private function syncDocuments(Product $product, array $rows): void
    {
        foreach ($rows as $row) {
            if (! empty($row['_remove'])) {
                if (! empty($row['id'])) {
                    $product->documents()->whereKey($row['id'])->delete();
                }

                continue;
            }

            if (empty($row['media_asset_id'])) {
                continue;
            }

            $attributes = [
                'media_asset_id' => $row['media_asset_id'],
                'type' => $row['type'] ?? 'datasheet',
                'label' => ($row['label'] ?? '') !== '' ? trim((string) $row['label']) : null,
                'position' => (int) ($row['position'] ?? 0),
            ];

            if (! empty($row['id'])) {
                $document = $product->documents()->whereKey($row['id'])->first();

                if ($document === null) {
                    continue;
                }

                $document->update($attributes);
            } else {
                $product->documents()->create($attributes);
            }
        }
    }

    /** @return \Illuminate\Support\Collection<int, string> */
    private function categoryOptions()
    {
        return ProductCategory::with('translations')->orderBy('id')->get()
            ->mapWithKeys(fn ($category) => [
                $category->id => $category->translationFor('id')?->name
                    ?? $category->translations->first()?->name
                    ?? ('Kategori #'.$category->id),
            ]);
    }

    /** @return \Illuminate\Support\Collection<int, MediaAsset> */
    private function assetsFor(Product $product)
    {
        $ids = $product->media->pluck('media_asset_id')
            ->merge($product->documents->pluck('media_asset_id'))
            ->merge($product->translations->pluck('og_image_id'))
            ->filter();

        foreach (['media', 'documents'] as $group) {
            foreach ((array) old($group, []) as $row) {
                if (! empty($row['media_asset_id'])) {
                    $ids->push($row['media_asset_id']);
                }
            }
        }

        foreach (array_keys(self::LOCALE_LABELS) as $locale) {
            $oldOg = old("translations.{$locale}.og_image_id");

            if ($oldOg) {
                $ids->push($oldOg);
            }
        }

        if ($ids->isEmpty()) {
            return collect();
        }

        return MediaAsset::whereIn('id', $ids->unique()->all())->get()->keyBy('id');
    }
}
