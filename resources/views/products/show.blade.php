@extends('layouts.public')

@section('seo_title', $translation->seo_title ?: $translation->name.' — Primainti')

@section('seo_description', $translation->seo_description ?: ($translation->short_description ?? ''))

@section('seo_head')
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="product">
    <meta property="og:title" content="{{ $translation->seo_title ?: $translation->name }}">
    @if ($translation->seo_description)
        <meta property="og:description" content="{{ $translation->seo_description }}">
    @endif
    @if ($translation->ogImage)
        <meta property="og:image" content="{{ url($translation->ogImage->url()) }}">
    @endif
@endsection

@section('content')
    <section class="section">
        @include('products._detail')
    </section>
@endsection
