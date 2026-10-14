{{-- Kartu produk katalog. Variabel: $product, $locale. --}}
@php($t = $product->translationFor($locale)
    ?? $product->translationFor(config('app.fallback_locale', 'en'))
    ?? $product->translations->first())
@php($primary = $product->primaryMedia()?->asset)
@php($categoryName = $product->category?->translationFor($locale)?->name
    ?? $product->category?->translations->first()?->name)

<article class="rounded-xl border border-gray-200 bg-white overflow-hidden shadow-sm hover:shadow-md transition">
    @if ($t)
        <a href="{{ route('public.products.show', [$locale, $t->slug]) }}" class="block h-44 bg-gray-100 flex items-center justify-center overflow-hidden">
            @if ($primary)
                <img src="{{ $primary->thumbnailUrl() ?? $primary->url() }}" alt="{{ $primary->alt ?: $t->name }}"
                     loading="lazy" class="max-h-full max-w-full object-contain">
            @else
                <span class="text-sm text-gray-400">Tanpa foto</span>
            @endif
        </a>

        <div class="p-4">
            @if ($categoryName)
                <p class="text-xs font-semibold uppercase tracking-wide text-[#F97316]">{{ $categoryName }}</p>
            @endif

            <h3 class="mt-1 font-bold text-[#0F2A54]">
                <a href="{{ route('public.products.show', [$locale, $t->slug]) }}">{{ $t->name }}</a>
            </h3>

            @if ($t->short_description)
                <p class="mt-1 text-sm text-gray-600 line-clamp-2">{{ $t->short_description }}</p>
            @endif

            <a href="{{ route('public.products.show', [$locale, $t->slug]) }}"
               class="mt-3 inline-block rounded-lg bg-[#0F2A54] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1E4278]">
                Lihat Detail
            </a>
        </div>
    @endif
</article>
