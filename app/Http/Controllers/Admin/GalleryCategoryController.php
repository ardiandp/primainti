<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveGalleryCategoryRequest;
use App\Models\GalleryCategory;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GalleryCategoryController extends Controller
{
    public const LOCALE_LABELS = [
        'id' => 'Bahasa Indonesia',
        'en' => 'English',
    ];

    public function index(Request $request)
    {
        $this->authorize('viewAny', GalleryCategory::class);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:draft,published'],
            'trash' => ['nullable', 'in:1'],
        ]);

        $query = GalleryCategory::query()
            ->with('translations')
            ->withCount('items')
            ->when($validated['trash'] ?? null, fn ($q) => $q->onlyTrashed())
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($validated['q'] ?? null, function ($q, $search) {
                $q->whereHas('translations', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->latest();

        return view('admin.gallery-categories.index', [
            'categories' => $query->paginate(20)->withQueryString(),
            'filters' => $validated,
        ]);
    }

    public function create()
    {
        $this->authorize('create', GalleryCategory::class);

        return view('admin.gallery-categories.create', [
            'category' => new GalleryCategory(['status' => 'draft']),
            'locales' => self::LOCALE_LABELS,
        ]);
    }

    public function store(SaveGalleryCategoryRequest $request)
    {
        $this->authorize('create', GalleryCategory::class);

        $validated = $request->validated();

        if ($validated['status'] === 'published') {
            $this->authorize('publish', GalleryCategory::class);
        }

        $category = DB::transaction(function () use ($validated) {
            $category = GalleryCategory::create(['status' => $validated['status']]);

            foreach ($validated['translations'] as $locale => $data) {
                $category->translations()->create([
                    'locale' => $locale,
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                ]);
            }

            return $category;
        });

        AuditLogger::log('gallery_category.create', $category, null, ['status' => $category->status]);

        return redirect()->route('admin.gallery-categories.edit', $category)->with('status', 'Kategori galeri dibuat.');
    }

    public function edit(GalleryCategory $galleryCategory)
    {
        $this->authorize('update', $galleryCategory);

        $galleryCategory->load('translations');

        return view('admin.gallery-categories.edit', [
            'category' => $galleryCategory,
            'locales' => self::LOCALE_LABELS,
        ]);
    }

    public function update(SaveGalleryCategoryRequest $request, GalleryCategory $galleryCategory)
    {
        $this->authorize('update', $galleryCategory);

        $validated = $request->validated();

        if ($validated['status'] === 'published' && $galleryCategory->status !== 'published') {
            $this->authorize('publish', $galleryCategory);
        }

        $old = ['status' => $galleryCategory->status];

        DB::transaction(function () use ($galleryCategory, $validated) {
            $galleryCategory->update(['status' => $validated['status']]);
            $galleryCategory->loadMissing('translations');

            foreach ($validated['translations'] as $locale => $data) {
                $translation = $galleryCategory->translations->firstWhere('locale', $locale);

                $attributes = ['name' => $data['name'], 'slug' => $data['slug']];

                if ($translation === null) {
                    $galleryCategory->translations()->create(array_merge($attributes, ['locale' => $locale]));
                } else {
                    $translation->update($attributes);
                }
            }
        });

        AuditLogger::log('gallery_category.update', $galleryCategory, $old, ['status' => $galleryCategory->status]);

        return redirect()->route('admin.gallery-categories.edit', $galleryCategory)->with('status', 'Kategori galeri diperbarui.');
    }

    public function destroy(GalleryCategory $galleryCategory)
    {
        $this->authorize('delete', $galleryCategory);

        if ($galleryCategory->items()->exists()) {
            return back()->withErrors([
                'category' => 'Kategori masih memiliki item galeri dan tidak bisa dihapus. Pindahkan itemnya terlebih dahulu.',
            ]);
        }

        $galleryCategory->delete();

        AuditLogger::log('gallery_category.delete', $galleryCategory, ['deleted_at' => now()->toDateTimeString()], null);

        return redirect()->route('admin.gallery-categories.index')->with('status', 'Kategori dipindahkan ke trash.');
    }

    public function restore(int $galleryCategory)
    {
        $model = GalleryCategory::withTrashed()->findOrFail($galleryCategory);

        $this->authorize('update', $model);

        $model->restore();

        AuditLogger::log('gallery_category.restore', $model, null, ['restored' => true]);

        return back()->with('status', 'Kategori dipulihkan.');
    }

    public function forceDestroy(int $galleryCategory)
    {
        $model = GalleryCategory::withTrashed()->findOrFail($galleryCategory);

        $this->authorize('delete', $model);

        if ($model->items()->withTrashed()->exists()) {
            return back()->withErrors([
                'category' => 'Kategori masih memiliki item galeri dan tidak bisa dihapus permanen.',
            ]);
        }

        $model->translations()->delete();
        $model->forceDelete();

        AuditLogger::log('gallery_category.force_delete', $model, ['id' => $model->id], null);

        return redirect()->route('admin.gallery-categories.index', ['trash' => 1])->with('status', 'Kategori dihapus permanen.');
    }
}
