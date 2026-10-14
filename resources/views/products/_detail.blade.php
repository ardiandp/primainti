{{-- Detail produk: dipakai halaman publik + pratinjau admin.
     Variabel: $product, $translation, $locale, $related (opsional). --}}
@php($categoryName = $product->category?->translationFor($locale)?->name
    ?? $product->category?->translations->first()?->name)
@php($gallery = $product->media->sortBy([['is_primary', 'desc'], ['position', 'asc'], ['id', 'asc']]))
@php($specs = $product->specifications->sortBy([['sort_order', 'asc'], ['id', 'asc']]))
@php($documents = $product->documents->sortBy([['position', 'asc'], ['id', 'asc']])->filter(fn ($d) => $d->asset))

<nav aria-label="Breadcrumb" class="text-sm text-gray-500">
    <a href="{{ route('public.products.index', $locale) }}" class="hover:underline">Produk</a>
    @if ($categoryName)
        <span class="mx-1">/</span><span>{{ $categoryName }}</span>
    @endif
    <span class="mx-1">/</span><span class="text-gray-900 font-medium">{{ $translation->name }}</span>
</nav>

<div class="mt-4 grid gap-8 md:grid-cols-2">
    <div>
        @php($primary = $product->primaryMedia()?->asset)
        <div class="rounded-xl border border-gray-200 bg-gray-50 h-72 flex items-center justify-center overflow-hidden">
            @if ($primary)
                <img src="{{ $primary->url() }}" alt="{{ $primary->alt ?: $translation->name }}"
                     class="max-h-full max-w-full object-contain">
            @else
                <span class="text-sm text-gray-400">Tanpa foto</span>
            @endif
        </div>

        @if ($gallery->count() > 1)
            <div class="mt-3 grid grid-cols-4 gap-2">
                @foreach ($gallery as $item)
                    @if ($item->asset)
                        <div class="h-16 rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden">
                            <img src="{{ $item->asset->thumbnailUrl() ?? $item->asset->url() }}"
                                 alt="{{ $item->asset->alt ?: $translation->name }}" loading="lazy"
                                 class="max-h-full max-w-full object-contain">
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    <div>
        @if ($categoryName)
            <p class="text-xs font-semibold uppercase tracking-wide text-[#F97316]">{{ $categoryName }}</p>
        @endif

        <h1 class="mt-1 text-3xl font-bold text-[#0F2A54]">{{ $translation->name }}</h1>

        @if ($product->sku)
            <p class="mt-1 text-sm text-gray-500">Kode: {{ $product->sku }}</p>
        @endif

        @if ($translation->short_description)
            <p class="mt-3 text-gray-700">{{ $translation->short_description }}</p>
        @endif

        <a href="{{ url("/{$locale}/kontak") }}"
           class="mt-5 inline-block rounded-lg bg-[#F97316] hover:bg-[#EA580C] px-5 py-2.5 text-sm font-semibold text-white transition">
            Minta Penawaran
        </a>
    </div>
</div>

@if ($translation->description)
    <section class="mt-10">
        <h2 class="text-xl font-bold text-[#0F2A54]">Deskripsi</h2>
        <div class="rte-content mt-3">{!! $translation->description !!}</div>
    </section>
@endif

@if ($translation->functions)
    <section class="mt-8">
        <h2 class="text-xl font-bold text-[#0F2A54]">Fungsi</h2>
        <div class="rte-content mt-3">{!! $translation->functions !!}</div>
    </section>
@endif

@if ($translation->applications)
    <section class="mt-8">
        <h2 class="text-xl font-bold text-[#0F2A54]">Aplikasi</h2>
        <div class="rte-content mt-3">{!! $translation->applications !!}</div>
    </section>
@endif

@if ($specs->isNotEmpty())
    <section class="mt-8">
        <h2 class="text-xl font-bold text-[#0F2A54]">Spesifikasi Teknis</h2>
        <div class="mt-3 overflow-x-auto rounded-xl border border-gray-200">
            <table class="w-full text-sm">
                <tbody>
                    @foreach ($specs as $spec)
                        @php($st = $spec->translations->firstWhere('locale', $locale)
                            ?? $spec->translations->firstWhere('locale', config('app.fallback_locale', 'en'))
                            ?? $spec->translations->first())
                        @if ($st && ($st->label || $st->value))
                            <tr class="border-t border-gray-100 first:border-t-0">
                                <th scope="row" class="px-4 py-2 text-left bg-gray-50 font-medium w-1/3">{{ $st->label }}</th>
                                <td class="px-4 py-2">{{ $st->value }}@if ($spec->unit) {{ $spec->unit }}@endif</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endif

@if ($documents->isNotEmpty())
    <section class="mt-8">
        <h2 class="text-xl font-bold text-[#0F2A54]">Dokumen</h2>
        <ul class="mt-3 space-y-2">
            @foreach ($documents as $document)
                <li>
                    <a href="{{ $document->asset->url() }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm hover:border-[#F97316] hover:text-[#EA580C]">
                        <span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-semibold uppercase">{{ $document->type }}</span>
                        {{ $document->label ?: $document->asset->original_name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </section>
@endif

@if (! empty($related) && $related->isNotEmpty())
    <section class="mt-10">
        <h2 class="text-xl font-bold text-[#0F2A54]">Produk Terkait</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($related as $relatedProduct)
                @include('products._card', ['product' => $relatedProduct])
            @endforeach
        </div>
    </section>
@endif
