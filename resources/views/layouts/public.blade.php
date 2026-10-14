{{-- Layout publik: header/footer dari CMS (menu + pengaturan). --}}
<!DOCTYPE html>
<html lang="{{ $locale ?? config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('seo_title', $settings->get('seo_default_title') ?: 'Primainti')</title>
    <meta name="description" content="@yield('seo_description', $settings->get('seo_default_description') ?: '')">
    @if ($settings->media('favicon_id'))
        <link rel="icon" href="{{ $settings->media('favicon_id')->url() }}">
    @endif
    @yield('seo_head')
    <script type="application/ld+json">
        {!! json_encode(['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => 'Primainti', 'url' => url('/')], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @yield('jsonld')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-gray-900 flex flex-col">
    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-[60] focus:rounded-lg focus:bg-[#0F2A54] focus:px-4 focus:py-2 focus:text-white">Lewati ke konten</a>
    @include('layouts._header')

    <main id="konten" class="flex-1 max-w-6xl mx-auto px-4 w-full">
        @yield('content')
    </main>

    @include('layouts._footer')
    @stack('scripts')
</body>
</html>
