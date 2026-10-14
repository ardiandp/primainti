<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageTranslation;

/**
 * Tampilan publik halaman CMS.
 *
 * Hanya halaman live (published + jadwal tayang terpenuhi) yang tampil;
 * draft/archived/terjadwal menghasilkan 404. Bila slug tidak ada pada
 * locale diminta, fallback ke fallback locale (keputusan interim Q-01).
 */
class PageShowController extends Controller
{
    public function show(string $locale, string $slug)
    {
        abort_unless(in_array($locale, ['id', 'en'], true), 404);

        $translation = $this->findLive($locale, $slug)
            ?? $this->findLive(config('app.fallback_locale', 'en'), $slug);

        abort_if($translation === null, 404);

        $translation->loadMissing(['sections', 'ogImage', 'page']);

        return view('pages.show', [
            'page' => $translation->page,
            'translation' => $translation,
            'locale' => $translation->locale,
        ]);
    }

    private function findLive(string $locale, string $slug): ?PageTranslation
    {
        return PageTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->whereHas('page', fn ($query) => $query->live())
            ->first();
    }
}
