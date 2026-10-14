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

@section('jsonld')
    @php($primaryImage = $product->primaryMedia()?->asset)
    <script type="application/ld+json">
        {!! json_encode(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $translation->name,
            'description' => $translation->short_description,
            'sku' => $product->sku,
            'url' => url()->current(),
            'image' => $primaryImage ? url($primaryImage->url()) : null,
        ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('content')
    <section class="section">
        @include('products._detail')
    </section>
@endsection
