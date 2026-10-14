<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveFacilityRequest;
use App\Models\Facility;
use App\Models\MediaAsset;
use App\Services\AuditLogger;
use App\Services\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacilityController extends Controller
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
        $this->authorize('viewAny', Facility::class);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:draft,published,archived'],
            'trash' => ['nullable', 'in:1'],
        ]);

        $query = Facility::query()
            ->with('translations')
            ->when($validated['trash'] ?? null, fn ($q) => $q->onlyTrashed())
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($validated['q'] ?? null, function ($q, $search) {
                $q->whereHas('translations', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->ordered();

        return view('admin.facilities.index', [
            'facilities' => $query->paginate(20)->withQueryString(),
            'filters' => $validated,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Facility::class);

        $facility = new Facility(['status' => 'draft', 'sort_order' => 0]);

        return view('admin.facilities.create', [
            'facility' => $facility,
            'locales' => self::LOCALE_LABELS,
            'assets' => $this->assetsFor($facility),
        ]);
    }

    public function store(SaveFacilityRequest $request)
    {
        $this->authorize('create', Facility::class);

        $validated = $request->validated();

        if ($validated['status'] === 'published') {
            $this->authorize('publish', Facility::class);
        }

        $facility = DB::transaction(function () use ($validated) {
            $facility = Facility::create([
                'status' => $validated['status'],
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
                'published_at' => $this->resolvePublishedAt($validated),
            ]);

            $this->syncTranslations($facility, $validated);
            $this->syncMedia($facility, $validated['media'] ?? []);

            return $facility;
        });

        AuditLogger::log('facility.create', $facility, null, ['status' => $facility->status]);

        return redirect()->route('admin.facilities.edit', $facility)->with('status', 'Fasilitas dibuat.');
    }

    public function edit(Facility $facility)
    {
        $this->authorize('update', $facility);

        $facility->load(['translations', 'media.asset']);

        return view('admin.facilities.edit', [
            'facility' => $facility,
            'locales' => self::LOCALE_LABELS,
            'assets' => $this->assetsFor($facility),
        ]);
    }

    public function update(SaveFacilityRequest $request, Facility $facility)
    {
        $this->authorize('update', $facility);

        $validated = $request->validated();

        if ($validated['status'] === 'published' && $facility->status !== 'published') {
            $this->authorize('publish', $facility);
        }

        $old = ['status' => $facility->status];

        DB::transaction(function () use ($facility, $validated) {
            $facility->update([
                'status' => $validated['status'],
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
                'published_at' => $this->resolvePublishedAt($validated, $facility),
            ]);

            $this->syncTranslations($facility, $validated);
            $this->syncMedia($facility, $validated['media'] ?? []);
        });

        AuditLogger::log('facility.update', $facility, $old, ['status' => $facility->status]);

        return redirect()->route('admin.facilities.edit', $facility)->with('status', 'Fasilitas diperbarui.');
    }

    public function destroy(Facility $facility)
    {
        $this->authorize('delete', $facility);

        $facility->delete();

        AuditLogger::log('facility.delete', $facility, ['deleted_at' => now()->toDateTimeString()], null);

        return redirect()->route('admin.facilities.index')->with('status', 'Fasilitas dipindahkan ke trash.');
    }

    public function restore(int $facility)
    {
        $model = Facility::withTrashed()->findOrFail($facility);

        $this->authorize('update', $model);

        $model->restore();

        AuditLogger::log('facility.restore', $model, null, ['restored' => true]);

        return back()->with('status', 'Fasilitas dipulihkan.');
    }

    public function forceDestroy(int $facility)
    {
        $model = Facility::withTrashed()->findOrFail($facility);

        $this->authorize('delete', $model);

        // Pivot foto ikut terhapus; file media TIDAK (reuse lintas modul).
        $model->translations()->delete();
        $model->media()->delete();
        $model->forceDelete();

        AuditLogger::log('facility.force_delete', $model, ['id' => $model->id], null);

        return redirect()->route('admin.facilities.index', ['trash' => 1])->with('status', 'Fasilitas dihapus permanen.');
    }

    public function preview(Request $request, Facility $facility)
    {
        $this->authorize('view', $facility);

        $locale = $request->validate([
            'locale' => ['nullable', 'in:id,en'],
        ])['locale'] ?? config('app.fallback_locale', 'en');

        $facility->load(['translations', 'media.asset']);

        $translation = $facility->translationFor($locale)
            ?? $facility->translationFor(config('app.fallback_locale', 'en'))
            ?? $facility->translations->first();

        abort_if($translation === null, 404, 'Fasilitas belum memiliki terjemahan.');

        return view('admin.facilities.preview', [
            'facility' => $facility,
            'translation' => $translation,
            'locale' => $translation->locale,
        ]);
    }

    private function resolvePublishedAt(array $validated, ?Facility $facility = null): ?string
    {
        if ($validated['status'] !== 'published') {
            return $facility?->published_at?->toDateTimeString();
        }

        return $validated['published_at'] ?? $facility?->published_at?->toDateTimeString() ?? now()->toDateTimeString();
    }

    private function syncTranslations(Facility $facility, array $validated): void
    {
        $facility->loadMissing('translations');

        foreach ($validated['translations'] as $locale => $data) {
            $translation = $facility->translations->firstWhere('locale', $locale);

            $attributes = [
                'name' => $data['name'],
                'slug' => $data['slug'],
                'summary' => $data['summary'] ?? null,
                'description' => $this->sanitizer->clean($data['description'] ?? null),
            ];

            if ($translation === null) {
                $facility->translations()->create(array_merge($attributes, ['locale' => $locale]));
            } else {
                $translation->update($attributes);
            }
        }
    }

    /** @param array<int, array<string, mixed>> $rows */
    private function syncMedia(Facility $facility, array $rows): void
    {
        foreach ($rows as $row) {
            if (! empty($row['_remove'])) {
                if (! empty($row['id'])) {
                    $facility->media()->whereKey($row['id'])->delete();
                }

                continue;
            }

            if (empty($row['media_asset_id'])) {
                continue;
            }

            $facility->media()->updateOrCreate(
                ['media_asset_id' => $row['media_asset_id']],
                ['position' => (int) ($row['position'] ?? 0)]
            );
        }
    }

    /** @return \Illuminate\Support\Collection<int, MediaAsset> */
    private function assetsFor(Facility $facility)
    {
        $ids = $facility->media->pluck('media_asset_id')->filter();

        foreach ((array) old('media', []) as $row) {
            if (! empty($row['media_asset_id'])) {
                $ids->push($row['media_asset_id']);
            }
        }

        if ($ids->isEmpty()) {
            return collect();
        }

        return MediaAsset::whereIn('id', $ids->unique()->all())->get()->keyBy('id');
    }
}
