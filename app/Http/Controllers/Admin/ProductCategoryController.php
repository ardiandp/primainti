<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveProductCategoryRequest;
use App\Models\ProductCategory;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductCategoryController extends Controller
{
    public const LOCALE_LABELS = [
        'id' => 'Bahasa Indonesia',
        'en' => 'English',
    ];

    public function index(Request $request)
    {
        $this->authorize('viewAny', ProductCategory::class);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:draft,published'],
            'trash' => ['nullable', 'in:1'],
        ]);

        $query = ProductCategory::query()
            ->with('translations')
            ->withCount('products')
            ->when($validated['trash'] ?? null, fn ($q) => $q->onlyTrashed())
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($validated['q'] ?? null, function ($q, $search) {
                $q->whereHas('translations', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->latest();

        return view('admin.product-categories.index', [
            'categories' => $query->paginate(20)->withQueryString(),
            'filters' => $validated,
        ]);
    }

    public function create()
    {
        $this->authorize('create', ProductCategory::class);

        return view('admin.product-categories.create', [
            'category' => new ProductCategory(['status' => 'draft']),
            'locales' => self::LOCALE_LABELS,
        ]);
    }

    public function store(SaveProductCategoryRequest $request)
    {
        $this->authorize('create', ProductCategory::class);

        $validated = $request->validated();

        if ($validated['status'] === 'published') {
            $this->authorize('publish', ProductCategory::class);
        }

        $category = DB::transaction(function () use ($validated) {
            $category = ProductCategory::create(['status' => $validated['status']]);

            foreach ($validated['translations'] as $locale => $data) {
                $category->translations()->create([
                    'locale' => $locale,
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                ]);
            }

            return $category;
        });

        AuditLogger::log('product_category.create', $category, null, ['status' => $category->status]);

        return redirect()->route('admin.product-categories.edit', $category)->with('status', 'Kategori dibuat.');
    }

    public function edit(ProductCategory $productCategory)
    {
        $this->authorize('update', $productCategory);

        $productCategory->load('translations');

        return view('admin.product-categories.edit', [
            'category' => $productCategory,
            'locales' => self::LOCALE_LABELS,
        ]);
    }

    public function update(SaveProductCategoryRequest $request, ProductCategory $productCategory)
    {
        $this->authorize('update', $productCategory);

        $validated = $request->validated();

        if ($validated['status'] === 'published' && $productCategory->status !== 'published') {
            $this->authorize('publish', $productCategory);
        }

        $old = ['status' => $productCategory->status];

        DB::transaction(function () use ($productCategory, $validated) {
            $productCategory->update(['status' => $validated['status']]);
            $productCategory->loadMissing('translations');

            foreach ($validated['translations'] as $locale => $data) {
                $translation = $productCategory->translations->firstWhere('locale', $locale);

                $attributes = ['name' => $data['name'], 'slug' => $data['slug']];

                if ($translation === null) {
                    $productCategory->translations()->create(array_merge($attributes, ['locale' => $locale]));
                } else {
                    $translation->update($attributes);
                }
            }
        });

        AuditLogger::log('product_category.update', $productCategory, $old, ['status' => $productCategory->status]);

        return redirect()->route('admin.product-categories.edit', $productCategory)->with('status', 'Kategori diperbarui.');
    }

    public function destroy(ProductCategory $productCategory)
    {
        $this->authorize('delete', $productCategory);

        if ($productCategory->products()->exists()) {
            return back()->withErrors([
                'category' => 'Kategori masih memiliki produk dan tidak bisa dihapus. Pindahkan produknya terlebih dahulu.',
            ]);
        }

        $productCategory->delete();

        AuditLogger::log('product_category.delete', $productCategory, ['deleted_at' => now()->toDateTimeString()], null);

        return redirect()->route('admin.product-categories.index')->with('status', 'Kategori dipindahkan ke trash.');
    }

    public function restore(int $productCategory)
    {
        $model = ProductCategory::withTrashed()->findOrFail($productCategory);

        $this->authorize('update', $model);

        $model->restore();

        AuditLogger::log('product_category.restore', $model, null, ['restored' => true]);

        return back()->with('status', 'Kategori dipulihkan.');
    }

    public function forceDestroy(int $productCategory)
    {
        $model = ProductCategory::withTrashed()->findOrFail($productCategory);

        $this->authorize('delete', $model);

        if ($model->products()->withTrashed()->exists()) {
            return back()->withErrors([
                'category' => 'Kategori masih memiliki produk dan tidak bisa dihapus permanen.',
            ]);
        }

        $model->translations()->delete();
        $model->forceDelete();

        AuditLogger::log('product_category.force_delete', $model, ['id' => $model->id], null);

        return redirect()->route('admin.product-categories.index', ['trash' => 1])->with('status', 'Kategori dihapus permanen.');
    }
}
