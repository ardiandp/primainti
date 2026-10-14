@extends('layouts.public')

@section('seo_title', 'Galeri — Primainti')

@section('content')
    <h1 class="text-3xl font-bold text-[#0F2A54]">Galeri</h1>

    <form method="GET" action="{{ route('public.gallery.index', $locale) }}" class="mt-6 flex flex-wrap items-center gap-2 text-sm">
        <a href="{{ route('public.gallery.index', $locale) }}"
           class="rounded-full px-4 py-1.5 border {{ $activeCategory === null ? 'bg-[#0F2A54] text-white border-[#0F2A54]' : 'border-gray-300 hover:border-[#F97316]' }}">
            Semua
        </a>
        @foreach ($categories as $category)
            @php($ct = $category->translationFor($locale)
                ?? $category->translationFor(config('app.fallback_locale', 'en'))
                ?? $category->translations->first())
            @if ($ct)
                <a href="{{ route('public.gallery.index', [$locale, 'kategori' => $ct->slug]) }}"
                   class="rounded-full px-4 py-1.5 border {{ $activeCategory?->id === $category->id ? 'bg-[#0F2A54] text-white border-[#0F2A54]' : 'border-gray-300 hover:border-[#F97316]' }}">
                    {{ $ct->name }}
                </a>
            @endif
        @endforeach
    </form>

    @if ($items->isEmpty())
        <div class="mt-10 rounded-xl border border-dashed border-gray-300 px-4 py-12 text-center text-gray-500">
            Belum ada foto pada kategori ini.
        </div>
    @else
        <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3" data-lightbox-gallery>
            @foreach ($items as $index => $item)
                @php($t = $item->translationFor($locale)
                    ?? $item->translationFor(config('app.fallback_locale', 'en'))
                    ?? $item->translations->first())
                @if ($item->asset && $t)
                    <button type="button" data-lightbox-item="{{ $index }}"
                            data-full="{{ $item->asset->url() }}"
                            data-caption="{{ $t->caption ?: $t->title }}"
                            data-alt="{{ $item->altFor($t->caption ?: $t->title) }}"
                            class="group rounded-xl border border-gray-200 overflow-hidden bg-gray-50 hover:border-[#F97316] hover:shadow transition text-left">
                            <span class="block h-40 flex items-center justify-center overflow-hidden">
                            <img src="{{ $item->asset->thumbnailUrl() ?? $item->asset->url() }}"
                                 alt="{{ $item->altFor($t->caption ?: $t->title) }}"
                                 loading="lazy"
                                 class="max-h-full max-w-full object-contain group-hover:scale-105 transition">
                        </span>
                        <span class="block px-3 py-2">
                            <span class="block text-sm font-medium truncate">{{ $t->caption ?: $t->title }}</span>
                        </span>
                    </button>
                @endif
            @endforeach
        </div>

        <div class="mt-6">
            {{ $items->links() }}
        </div>

        {{-- Lightbox accessible: dialog + keyboard + fokus. --}}
        <div data-lightbox class="hidden fixed inset-0 z-50 items-center justify-center p-4" role="dialog"
             aria-modal="true" aria-label="Pratinjau foto">
            <div data-lightbox-backdrop class="absolute inset-0 bg-black/80"></div>

            <figure class="relative max-w-4xl w-full">
                <img data-lightbox-image src="" alt=""
                     class="max-h-[75vh] w-full object-contain rounded-xl bg-black">
                <figcaption data-lightbox-caption class="mt-2 text-center text-sm text-white"></figcaption>

                <div class="mt-3 flex items-center justify-center gap-2">
                    <button type="button" data-lightbox-prev aria-label="Foto sebelumnya"
                            class="rounded-full bg-white/10 hover:bg-white/20 text-white px-4 py-2 text-sm">‹ Sebelumnya</button>
                    <button type="button" data-lightbox-close
                            class="rounded-full bg-[#F97316] hover:bg-[#EA580C] text-white px-4 py-2 text-sm">Tutup (Esc)</button>
                    <button type="button" data-lightbox-next aria-label="Foto berikutnya"
                            class="rounded-full bg-white/10 hover:bg-white/20 text-white px-4 py-2 text-sm">Berikutnya ›</button>
                </div>
            </figure>
        </div>

        @push('scripts')
            @vite(['resources/js/lightbox.js'])
        @endpush
    @endif
@endsection
