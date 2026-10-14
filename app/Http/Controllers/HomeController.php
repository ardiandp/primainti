<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\PageTranslation;
use App\Models\Product;

/**
 * Beranda (provisional Phase 8).
 *
 * Seluruh seksi CMS-driven dan kondisional: seksi hanya tampil bila datanya
 * ada dan live. Klaim/sertifikat/statistik tak terverifikasi TIDAK ditampilkan.
 */
class HomeController extends Controller
{
    /** Kandidat slug halaman profil per locale. */
    private const PROFILE_SLUGS = ['tentang-kami', 'tentang', 'about-us', 'about'];

    public function index(string $locale)
    {
        abort_unless(in_array($locale, ['id', 'en'], true), 404);

        $profile = $this->profilePage($locale);

        $featured = Product::live()
            ->where('is_featured', true)
            ->whereHas('category', fn ($q) => $q->published())
            ->with(['translations', 'category.translations', 'media.asset'])
            ->ordered()
            ->limit(4)
            ->get();

        $facilities = Facility::live()
            ->with(['translations', 'media.asset'])
            ->ordered()
            ->limit(3)
            ->get();

        return view('home', [
            'locale' => $locale,
            'profile' => $profile,
            'featured' => $featured,
            'facilities' => $facilities,
        ]);
    }

    /** @return array{title: string, summary: ?string, url: string}|null */
    private function profilePage(string $locale): ?array
    {
        $locales = array_unique([$locale, config('app.fallback_locale', 'en')]);

        foreach ($locales as $try) {
            foreach (self::PROFILE_SLUGS as $slug) {
                $translation = PageTranslation::where('locale', $try)
                    ->where('slug', $slug)
                    ->whereHas('page', fn ($q) => $q->live())
                    ->first();

                if ($translation !== null) {
                    return [
                        'title' => $translation->title,
                        'summary' => $translation->summary,
                        'url' => route('public.pages.show', [$translation->locale, $translation->slug]),
                    ];
                }
            }
        }

        return null;
    }
}
