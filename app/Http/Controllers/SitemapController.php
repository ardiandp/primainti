<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * sitemap.xml (hanya URL live) dan robots.txt.
 * Dibangun dinamis tanpa cache agar selalu mencerminkan status publikasi.
 */
class SitemapController extends Controller
{
    /** @return array<int, array{loc: string, lastmod: ?string}> */
    private function urls(): array
    {
        $urls = [];

        foreach (['id', 'en'] as $locale) {
            $urls[] = ['loc' => route('public.home', $locale), 'lastmod' => null];
            $urls[] = ['loc' => route('public.contact.show', $locale), 'lastmod' => null];
            $urls[] = ['loc' => route('public.products.index', $locale), 'lastmod' => null];
            $urls[] = ['loc' => route('public.gallery.index', $locale), 'lastmod' => null];
            $urls[] = ['loc' => route('public.facilities.index', $locale), 'lastmod' => null];
        }

        Page::live()
            ->with('translations')
            ->orderBy('id')
            ->chunk(200, function ($pages) use (&$urls) {
                foreach ($pages as $page) {
                    foreach ($page->translations as $translation) {
                        $urls[] = [
                            'loc' => route('public.pages.show', [$translation->locale, $translation->slug]),
                            'lastmod' => $translation->updated_at->toAtomString(),
                        ];
                    }
                }
            });

        Product::live()
            ->whereHas('category', fn ($q) => $q->published())
            ->with('translations')
            ->orderBy('id')
            ->chunk(200, function ($products) use (&$urls) {
                foreach ($products as $product) {
                    foreach ($product->translations as $translation) {
                        $urls[] = [
                            'loc' => route('public.products.show', [$translation->locale, $translation->slug]),
                            'lastmod' => $translation->updated_at->toAtomString(),
                        ];
                    }
                }
            });

        return $urls;
    }

    public function sitemap()
    {
        return response()
            ->view('sitemap', ['urls' => $this->urls()])
            ->header('Content-Type', 'text/xml');
    }

    public function robots(Request $request)
    {
        $content = "User-agent: *\nDisallow: /admin\n\nSitemap: ".route('public.sitemap')."\n";

        return response($content)->header('Content-Type', 'text/plain');
    }
}
