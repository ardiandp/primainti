<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SavePageRequest;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageTranslation;
use App\Services\AuditLogger;
use App\Services\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
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
        $this->authorize('viewAny', Page::class);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:draft,published,archived'],
            'trash' => ['nullable', 'in:1'],
        ]);

        $query = Page::query()
            ->with('translations')
            ->when($validated['trash'] ?? null, fn ($q) => $q->onlyTrashed())
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($validated['q'] ?? null, function ($q, $search) {
                $q->whereHas('translations', function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->latest();

        return view('admin.pages.index', [
            'pages' => $query->paginate(20)->withQueryString(),
            'filters' => $validated,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Page::class);

        $page = new Page(['status' => 'draft']);

        return view('admin.pages.create', [
            'page' => $page,
            'locales' => self::LOCALE_LABELS,
            'ogAssets' => $this->ogAssetsFor($page),
        ]);
    }

    public function store(SavePageRequest $request)
    {
        $this->authorize('create', Page::class);

        $validated = $request->validated();

        if ($validated['status'] === 'published') {
            $this->authorize('publish', Page::class);
        }

        $page = DB::transaction(function () use ($validated) {
            $page = Page::create([
                'template' => $validated['template'] ?? null,
                'status' => $validated['status'],
                'published_at' => $this->resolvePublishedAt($validated),
            ]);

            $this->syncTranslations($page, $validated);

            return $page;
        });

        AuditLogger::log('page.create', $page, null, [
            'status' => $page->status,
            'slugs' => $page->translations->map(fn ($t) => "{$t->locale}:{$t->slug}")->all(),
        ]);

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Halaman dibuat.');
    }

    public function edit(Page $page)
    {
        $this->authorize('update', $page);

        $page->load(['translations.sections', 'translations.ogImage']);

        return view('admin.pages.edit', [
            'page' => $page,
            'locales' => self::LOCALE_LABELS,
            'ogAssets' => $this->ogAssetsFor($page),
        ]);
    }

    public function update(SavePageRequest $request, Page $page)
    {
        $this->authorize('update', $page);

        $validated = $request->validated();

        if ($validated['status'] === 'published' && $page->status !== 'published') {
            $this->authorize('publish', $page);
        }

        $old = ['status' => $page->status];

        DB::transaction(function () use ($page, $validated) {
            $page->update([
                'template' => $validated['template'] ?? null,
                'status' => $validated['status'],
                'published_at' => $this->resolvePublishedAt($validated, $page),
            ]);

            $this->syncTranslations($page, $validated);
        });

        AuditLogger::log('page.update', $page, $old, ['status' => $page->status]);

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Halaman diperbarui.');
    }

    public function destroy(Page $page)
    {
        $this->authorize('delete', $page);

        $page->delete();

        AuditLogger::log('page.delete', $page, ['deleted_at' => now()->toDateTimeString()], null);

        return redirect()->route('admin.pages.index')->with('status', 'Halaman dipindahkan ke trash.');
    }

    public function restore(int $page)
    {
        $model = Page::withTrashed()->findOrFail($page);

        $this->authorize('update', $model);

        $model->restore();

        AuditLogger::log('page.restore', $model, null, ['restored' => true]);

        return back()->with('status', 'Halaman dipulihkan.');
    }

    public function forceDestroy(int $page)
    {
        $model = Page::withTrashed()->findOrFail($page);

        $this->authorize('delete', $model);

        $model->translations()->delete();
        $model->forceDelete();

        AuditLogger::log('page.force_delete', $model, ['id' => $model->id], null);

        return redirect()->route('admin.pages.index', ['trash' => 1])->with('status', 'Halaman dihapus permanen.');
    }

    public function preview(Request $request, Page $page)
    {
        $this->authorize('view', $page);

        $locale = $request->validate([
            'locale' => ['nullable', 'in:id,en'],
        ])['locale'] ?? config('app.fallback_locale', 'en');

        $page->load(['translations.sections', 'translations.ogImage']);

        $translation = $page->translationFor($locale)
            ?? $page->translationFor(config('app.fallback_locale', 'en'))
            ?? $page->translations->first();

        abort_if($translation === null, 404, 'Halaman belum memiliki terjemahan.');

        return view('admin.pages.preview', [
            'page' => $page,
            'translation' => $translation,
            'locale' => $translation->locale,
        ]);
    }

    /** Aset OG untuk preview form: milik model + sisa old input validasi gagal. */
    private function ogAssetsFor(Page $page)
    {
        $ids = $page->translations->pluck('og_image_id')->filter();

        foreach (array_keys(self::LOCALE_LABELS) as $locale) {
            $oldId = old("translations.{$locale}.og_image_id");

            if ($oldId) {
                $ids->push($oldId);
            }
        }

        if ($ids->isEmpty()) {
            return collect();
        }

        return MediaAsset::whereIn('id', $ids->unique()->all())->get()->keyBy('id');
    }

    private function resolvePublishedAt(array $validated, ?Page $page = null): ?string
    {
        if ($validated['status'] !== 'published') {
            return $page?->published_at?->toDateTimeString();
        }

        return $validated['published_at'] ?? $page?->published_at?->toDateTimeString() ?? now()->toDateTimeString();
    }

    private function syncTranslations(Page $page, array $validated): void
    {
        $page->loadMissing('translations');

        foreach ($validated['translations'] as $locale => $data) {
            $translation = $page->translations->firstWhere('locale', $locale);

            $attributes = [
                'title' => $data['title'],
                'slug' => $data['slug'],
                'summary' => $data['summary'] ?? null,
                'content' => $this->sanitizer->clean($data['content'] ?? null),
                'seo_title' => $data['seo_title'] ?? null,
                'seo_description' => $data['seo_description'] ?? null,
                'og_image_id' => $data['og_image_id'] ?? null,
            ];

            if ($translation === null) {
                $translation = $page->translations()->create(
                    array_merge($attributes, ['locale' => $locale])
                );
            } else {
                $translation->update($attributes);
            }

            $this->syncSections($page, $translation, $validated['sections'][$locale] ?? []);
        }
    }

    private function syncSections(Page $page, PageTranslation $translation, array $rows): void
    {
        foreach ($rows as $row) {
            if (! empty($row['_remove'])) {
                if (! empty($row['id'])) {
                    $translation->sections()->whereKey($row['id'])->delete();
                }

                continue;
            }

            $heading = trim((string) ($row['heading'] ?? ''));
            $body = $this->sanitizer->clean($row['body'] ?? null);

            if ($heading === '' && $body === null) {
                continue;
            }

            $attributes = [
                'position' => (int) ($row['position'] ?? 0),
                'heading' => $heading === '' ? null : $heading,
                'body' => $body,
                'is_visible' => (bool) ($row['is_visible'] ?? false),
            ];

            if (! empty($row['id'])) {
                // Batasi pada section milik terjemahan ini (anti lintas-halaman).
                $section = $translation->sections()->whereKey($row['id'])->first();

                if ($section === null) {
                    continue;
                }

                $section->update($attributes);
            } else {
                $translation->sections()->create($attributes);
            }
        }
    }
}
