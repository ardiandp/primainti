@extends('layouts.public')

@section('seo_title', 'Fasilitas — Primainti')

@section('content')
    <h1 class="text-3xl font-bold text-[#0F2A54]">Fasilitas</h1>

    @if ($facilities->isEmpty())
        <div class="mt-10 rounded-xl border border-dashed border-gray-300 px-4 py-12 text-center text-gray-500">
            Belum ada fasilitas yang dipublikasikan.
        </div>
    @else
        <div class="mt-8 space-y-12">
            @foreach ($facilities as $facility)
                @php($t = $facility->translationFor($locale)
                    ?? $facility->translationFor(config('app.fallback_locale', 'en'))
                    ?? $facility->translations->first())
                @if ($t)
                    <section>
                        @include('facilities._detail', [
                            'facility' => $facility,
                            'translation' => $t,
                            'locale' => $t->locale,
                        ])
                    </section>
                @endif
            @endforeach
        </div>

        <div class="mt-8">
            {{ $facilities->links() }}
        </div>
    @endif
@endsection
