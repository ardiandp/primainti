@extends('layouts.public')

@section('seo_title', 'Halaman tidak ditemukan — Primainti')

@section('content')
    <section class="section text-center">
        <p class="section-kicker">404</p>
        <h1 class="section-title mx-auto">Halaman tidak ditemukan</h1>
        <p class="section-lead mx-auto">Tautan yang Anda buka tidak tersedia atau sudah dipindahkan. / The page you are looking for does not exist.</p>
        <div class="mt-6 flex justify-center gap-3">
            <a href="{{ url('/'.($locale ?? config('app.locale'))) }}" class="btn btn-secondary">Beranda</a>
            <a href="{{ route('public.products.index', $locale ?? config('app.locale')) }}" class="btn btn-outline">Produk</a>
        </div>
    </section>
@endsection
