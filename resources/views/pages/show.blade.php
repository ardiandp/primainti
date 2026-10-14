@extends('layouts.public')

@section('seo_title', $translation->seo_title ?: $translation->title)

@section('seo_description', $translation->seo_description ?: ($translation->summary ?? ''))

@section('seo_head')
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $translation->seo_title ?: $translation->title }}">
    @if ($translation->seo_description)
        <meta property="og:description" content="{{ $translation->seo_description }}">
    @endif
    @if ($translation->ogImage)
        <meta property="og:image" content="{{ url($translation->ogImage->url()) }}">
    @endif
@endsection

@section('content')
    <section class="section">
        @include('pages._body')
    </section>
@endsection
