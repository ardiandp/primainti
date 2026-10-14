<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveGalleryItemRequest;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\MediaAsset;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GalleryItemController extends Controller
{
    public const LOCALE_LABELS = [
        'id' => 'Bahasa Indonesia',
        'en' => 'English',
    ];

    public function index(Request $request)
    {
        $this->authorize('viewAny', GalleryItem::class);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:draft,published,archived'],
            'category' => ['nullable', 'integer', 'exists:gallery_categories,id'],
            'trash' => ['nullable', 'in:1'],
        ]);

        $query = GalleryItem::query()
            ->with(['translations', 'asset', 'category.translations'])
            ->when($validated['trash'] ?? null, fn ($q) => $q->onlyTrashed())
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($validated['category'] ?? null, fn ($q, $category) => $q->where('gallery_category_id', $category))
            ->when($validated['q'] ?? null, function ($q, $search) {
                $q->whereHas('translations', function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('caption', 'like', "%{$search}%");
                });
            })
            ->ordered();

        return view('admin.gallery-items.index', [
            'items' => $query->paginate(24)->withQueryString(),
            'filters' => $validated,
            'categories' => GalleryCategory::with('translations')->orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', GalleryItem::class);

        return view('admin.gallery-items.create', [
            'item' => new GalleryItem(['status' => 'draft', 'position' => 0]),
            'locales' => self::LOCALE_LABELS,
            'categories' => $this->categoryOptions(),
            'asset' => $this->resolveAsset(null),
        ]);
    }

    public function store(SaveGalleryItemRequest $request)
    {
        $this->authorize('create', GalleryItem::class);

        $validated = $request->validated();

        if ($validated['status'] === 'published') {
            $this->authorize('publish', GalleryItem::class);
        }

        $item = DB::transaction(function () use ($validated) {
            $item = GalleryItem::create([
                'gallery_category_id' => $validated['gallery_category_id'] ?? null,
                'media_asset_id' => $validated['media_asset_id'],
                'alt_override' => $validated['alt_override'] ?? null,
                'taken_at' => $validated['taken_at'] ?? null,
                'position' => (int) ($validated['position'] ?? 0),
                'status' => $validated['status'],
                'published_at' => $this->resolvePublishedAt($validated),
            ]);

            foreach ($validated['translations'] as $locale => $data) {
                $item->translations()->create([
                    'locale' => $locale,
                    'title' => $data['title'],
                    'slug' => $data['slug'],
                    'caption' => $data['caption'] ?? null,
                ]);
            }

            return $item;
        });

        AuditLogger::log('gallery_item.create', $item, null, ['status' => $item->status]);

        return redirect()->route('admin.gallery-items.edit', $item)->with('status', 'Item galeri dibuat.');
    }

    public function edit(GalleryItem $galleryItem)
    {
        $this->authorize('update', $galleryItem);

        $galleryItem->load(['translations', 'asset', 'category.translations']);

        return view('admin.gallery-items.edit', [
            'item' => $galleryItem,
            'locales' => self::LOCALE_LABELS,
            'categories' => $this->categoryOptions(),
            'asset' => $this->resolveAsset($galleryItem),
        ]);
    }

    public function update(SaveGalleryItemRequest $request, GalleryItem $galleryItem)
    {
        $this->authorize('update', $galleryItem);

        $validated = $request->validated();

        if ($validated['status'] === 'published' && $galleryItem->status !== 'published') {
            $this->authorize('publish', $galleryItem);
        }

        $old = ['status' => $galleryItem->status];

        DB::transaction(function () use ($galleryItem, $validated) {
            $galleryItem->update([
                'gallery_category_id' => $validated['gallery_category_id'] ?? null,
                'media_asset_id' => $validated['media_asset_id'],
                'alt_override' => $validated['alt_override'] ?? null,
                'taken_at' => $validated['taken_at'] ?? null,
                'position' => (int) ($validated['position'] ?? 0),
                'status' => $validated['status'],
                'published_at' => $this->resolvePublishedAt($validated, $galleryItem),
            ]);

            $galleryItem->loadMissing('translations');

            foreach ($validated['translations'] as $locale => $data) {
                $translation = $galleryItem->translations->firstWhere('locale', $locale);

                $attributes = [
                    'title' => $data['title'],
                    'slug' => $data['slug'],
                    'caption' => $data['caption'] ?? null,
                ];

                if ($translation === null) {
                    $galleryItem->translations()->create(array_merge($attributes, ['locale' => $locale]));
                } else {
                    $translation->update($attributes);
                }
            }
        });

        AuditLogger::log('gallery_item.update', $galleryItem, $old, ['status' => $galleryItem->status]);

        return redirect()->route('admin.gallery-items.edit', $galleryItem)->with('status', 'Item galeri diperbarui.');
    }

    public function destroy(GalleryItem $galleryItem)
    {
        $this->authorize('delete', $galleryItem);

        $galleryItem->delete();

        AuditLogger::log('gallery_item.delete', $galleryItem, ['deleted_at' => now()->toDateTimeString()], null);

        return redirect()->route('admin.gallery-items.index')->with('status', 'Item dipindahkan ke trash.');
    }

    public function restore(int $galleryItem)
    {
        $model = GalleryItem::withTrashed()->findOrFail($galleryItem);

        $this->authorize('update', $model);

        $model->restore();

        AuditLogger::log('gallery_item.restore', $model, null, ['restored' => true]);

        return back()->with('status', 'Item dipulihkan.');
    }

    public function forceDestroy(int $galleryItem)
    {
        $model = GalleryItem::withTrashed()->findOrFail($galleryItem);

        $this->authorize('delete', $model);

        // Terjemahan ikut terhapus; file media TIDAK (reuse lintas modul).
        $model->translations()->delete();
        $model->forceDelete();

        AuditLogger::log('gallery_item.force_delete', $model, ['id' => $model->id], null);

        return redirect()->route('admin.gallery-items.index', ['trash' => 1])->with('status', 'Item dihapus permanen.');
    }

    private function resolvePublishedAt(array $validated, ?GalleryItem $item = null): ?string
    {
        if ($validated['status'] !== 'published') {
            return $item?->published_at?->toDateTimeString();
        }

        return $validated['published_at'] ?? $item?->published_at?->toDateTimeString() ?? now()->toDateTimeString();
    }

    /** @return \Illuminate\Support\Collection<int, string> */
    private function categoryOptions()
    {
        return GalleryCategory::with('translations')->orderBy('id')->get()
            ->mapWithKeys(fn ($category) => [
                $category->id => $category->translationFor('id')?->name
                    ?? $category->translations->first()?->name
                    ?? ('Kategori #'.$category->id),
            ]);
    }

    private function resolveAsset(?GalleryItem $item): ?MediaAsset
    {
        $id = old('media_asset_id', $item?->media_asset_id);

        if (! $id) {
            return null;
        }

        return MediaAsset::find($id);
    }
}
