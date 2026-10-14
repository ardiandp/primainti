{{-- Isi halaman CMS: dipakai pratinjau admin + halaman publik. --}}
@php($translation->loadMissing(['sections', 'ogImage']))

<h1 class="text-3xl font-bold text-[#0F2A54]">{{ $translation->title }}</h1>

@if ($translation->summary)
    <p class="mt-3 text-lg text-gray-600">{{ $translation->summary }}</p>
@endif

@if ($translation->content)
    <div class="rte-content mt-6">{!! $translation->content !!}</div>
@endif

@foreach ($translation->sections->where('is_visible', true) as $section)
    <section class="mt-8">
        @if ($section->heading)
            <h2 class="text-2xl font-bold text-[#0F2A54]">{{ $section->heading }}</h2>
        @endif

        @if ($section->body)
            <div class="rte-content mt-3">{!! $section->body !!}</div>
        @endif
    </section>
@endforeach
