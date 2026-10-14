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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-gray-900 flex flex-col">
    @include('layouts._header')

    <main class="flex-1 max-w-6xl mx-auto px-4 w-full">
        @yield('content')
    </main>

    @include('layouts._footer')
    @stack('scripts')
</body>
</html>
