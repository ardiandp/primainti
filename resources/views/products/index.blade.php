@extends('layouts.public')

@section('seo_title', 'Produk — Primainti')

@section('seo_head')
    <link rel="canonical" href="{{ route('public.products.index', $locale) }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Produk — Primainti">
@endsection

@section('content')
    <section class="section">
        <x-section-head kicker="Katalog" title="Produk" />

    <form method="GET" action="{{ route('public.products.index', $locale) }}" class="mb-6 flex flex-wrap items-center gap-3 text-sm">
        <select name="kategori" class="rounded-lg border border-gray-300 px-3 py-2" onchange="this.form.submit()">
            <option value="">Semua kategori</option>
            @foreach ($categories as $category)
                @php($ct = $category->translationFor($locale)
                    ?? $category->translationFor(config('app.fallback_locale', 'en'))
                    ?? $category->translations->first())
                @if ($ct)
                    <option value="{{ $ct->slug }}" @selected($activeCategory?->id === $category->id)>{{ $ct->name }}</option>
                @endif
            @endforeach
        </select>

        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari produk…"
               class="rounded-lg border border-gray-300 px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-[#F97316]">

        <button type="submit" class="btn btn-secondary !py-2">Cari</button>
    </form>

    @if ($products->isEmpty())
        <div class="mt-10 rounded-xl border border-dashed border-gray-300 px-4 py-12 text-center text-gray-500">
            @if (! empty($filters['q']) || ! empty($filters['kategori']))
                Tidak ada produk yang cocok dengan pencarian Anda.
            @else
                Belum ada produk yang dipublikasikan.
            @endif
        </div>
    @else
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($products as $product)
                @include('products._card', ['product' => $product])
            @endforeach
        </div>

        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @endif
    </section>
@endsection
