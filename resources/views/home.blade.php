@extends('layouts.public')

@section('seo_title', $settings->get('seo_default_title') ?: 'Primainti')

@section('seo_description', $settings->get('seo_default_description') ?: ($settings->get($locale === 'id' ? 'tagline_id' : 'tagline_en') ?: ''))

@section('seo_head')
    <link rel="canonical" href="{{ route('public.home', $locale) }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $settings->get('seo_default_title') ?: 'Primainti' }}">
@endsection

@section('content')
    {{-- Hero: merek + tagline + CTA dari pengaturan (tanpa klaim karangan). --}}
    @php($tagline = $settings->get($locale === 'id' ? 'tagline_id' : 'tagline_en'))
    @php($heroCtaLabel = $settings->get($locale === 'id' ? 'cta_label_id' : 'cta_label_en'))
    @php($heroCtaUrl = $settings->get('cta_url'))
    @php($heroPhone = $settings->get('contact_phone'))

    <section class="hero -mx-4 px-4">
        <div class="hero-accent -mx-4 px-4">
            <div class="max-w-6xl mx-auto py-16 md:py-24">
                <p class="section-kicker">Primainti</p>
                <h1 class="mt-2 text-4xl md:text-5xl font-bold leading-tight max-w-2xl">
                    {{ $tagline ?: 'Primainti' }}
                </h1>
                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($heroCtaLabel && $heroCtaUrl)
                        <a href="{{ $heroCtaUrl }}" class="btn btn-primary">{{ $heroCtaLabel }}</a>
                    @endif
                    <a href="{{ route('public.products.index', $locale) }}" class="btn border border-white/40 text-white hover:bg-white/10">
                        Lihat Produk
                    </a>
                    @if ($heroPhone)
                        <a href="tel:{{ preg_replace('/\s+/', '', $heroPhone) }}" class="btn border border-white/40 text-white hover:bg-white/10">{{ $heroPhone }}</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Profil ringkas dari halaman CMS bila ada. --}}
    @if ($profile)
        <section class="section">
            <x-section-head kicker="Profil" :title="$profile['title']" :lead="$profile['summary']" />
            <a href="{{ $profile['url'] }}" class="btn btn-outline">Selengkapnya</a>
        </section>
    @endif

    {{-- Produk unggulan: hanya flag featured yang live. --}}
    @if ($featured->isNotEmpty())
        <section class="section section-alt -mx-4 px-4">
            <div class="max-w-6xl mx-auto">
                <x-section-head kicker="Produk" title="Produk Unggulan" />
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($featured as $product)
                        @include('products._card', ['product' => $product])
                    @endforeach
                </div>
                <a href="{{ route('public.products.index', $locale) }}" class="btn btn-secondary mt-6">Semua Produk</a>
            </div>
        </section>
    @endif

    {{-- Fasilitas. --}}
    @if ($facilities->isNotEmpty())
        <section class="section">
            <x-section-head kicker="Fasilitas" title="Fasilitas Kami" />
            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($facilities as $facility)
                    @php($ft = $facility->translationFor($locale)
                        ?? $facility->translationFor(config('app.fallback_locale', 'en'))
                        ?? $facility->translations->first())
                    @php($cover = $facility->coverMedia()?->asset)
                    @if ($ft)
                        <article class="card card-hover">
                            @if ($cover)
                                <div class="h-44 bg-gray-100 flex items-center justify-center overflow-hidden">
                                    <img src="{{ $cover->thumbnailUrl() ?? $cover->url() }}"
                                         alt="{{ $cover->alt ?: $ft->name }}" loading="lazy"
                                         class="max-h-full max-w-full object-contain">
                                </div>
                            @endif
                            <div class="p-4">
                                <h3 class="font-bold text-[#0F2A54]">{{ $ft->name }}</h3>
                                @if ($ft->summary)
                                    <p class="mt-1 text-sm text-gray-600 line-clamp-2">{{ $ft->summary }}</p>
                                @endif
                            </div>
                        </article>
                    @endif
                @endforeach
            </div>
            <a href="{{ route('public.facilities.index', $locale) }}" class="btn btn-outline mt-6">Semua Fasilitas</a>
        </section>
    @endif

    @include('components.cta-band')

    {{-- Kontak ringkas dari pengaturan. --}}
    @php($address = $settings->get('address'))
    @php($email = $settings->get('contact_email'))
    @php($hours = $settings->get('opening_hours'))

    @if ($address || $heroPhone || $email || $hours)
        <section class="section">
            <x-section-head kicker="Kontak" title="Hubungi Kami" />
            <div class="grid gap-4 md:grid-cols-2 text-sm">
                @if ($address)
                    <p><span class="font-semibold">Alamat:</span> {{ $address }}</p>
                @endif
                @if ($heroPhone)
                    <p><span class="font-semibold">Telepon:</span> <a href="tel:{{ preg_replace('/\s+/', '', $heroPhone) }}" class="text-[#1E4278] underline">{{ $heroPhone }}</a></p>
                @endif
                @if ($email)
                    <p><span class="font-semibold">Email:</span> <a href="mailto:{{ $email }}" class="text-[#1E4278] underline">{{ $email }}</a></p>
                @endif
                @if ($hours)
                    <p><span class="font-semibold">Jam operasional:</span> {{ $hours }}</p>
                @endif
            </div>
            <a href="{{ route('public.contact.show', $locale) }}" class="btn btn-secondary mt-6">Halaman Kontak</a>
        </section>
    @endif
@endsection
