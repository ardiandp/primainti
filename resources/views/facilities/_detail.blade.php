{{-- Detail fasilitas: dipakai halaman publik + pratinjau admin.
     Variabel: $facility, $translation, $locale. --}}
@php($photos = $facility->media->sortBy([['position', 'asc'], ['id', 'asc']])->filter(fn ($m) => $m->asset))
@php($cover = $photos->first()?->asset)

<h1 class="text-3xl font-bold text-[#0F2A54]">{{ $translation->name }}</h1>

@if ($translation->summary)
    <p class="mt-3 text-lg text-gray-600">{{ $translation->summary }}</p>
@endif

@if ($cover)
    <div class="mt-6 rounded-xl border border-gray-200 bg-gray-50 h-72 flex items-center justify-center overflow-hidden">
        <img src="{{ $cover->url() }}" alt="{{ $cover->alt ?: $translation->name }}"
             class="max-h-full max-w-full object-contain">
    </div>
@endif

@if ($translation->description)
    <div class="rte-content mt-6">{!! $translation->description !!}</div>
@endif

@if ($photos->count() > 1)
    <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 gap-3">
        @foreach ($photos->skip(1) as $item)
            <div class="h-40 rounded-xl border border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden">
                <img src="{{ $item->asset->thumbnailUrl() ?? $item->asset->url() }}"
                     alt="{{ $item->asset->alt ?: $translation->name }}" loading="lazy"
                     class="max-h-full max-w-full object-contain">
            </div>
        @endforeach
    </div>
@endif
