{{-- Layout publik minimal (provisional Phase 4; frontend penuh di Phase 8). --}}
<!DOCTYPE html>
<html lang="{{ $locale ?? config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('seo_title', 'Primainti')</title>
    @hasSection('seo_description')
        <meta name="description" content="@yield('seo_description')">
    @endif
    @yield('seo_head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-gray-900 flex flex-col">
    <header class="bg-[#0F2A54] text-white">
        <div class="max-w-4xl mx-auto px-4 h-14 flex items-center">
            <span class="font-bold tracking-wide">Primainti</span>
        </div>
    </header>

    <main class="flex-1 max-w-4xl mx-auto px-4 py-10 w-full">
        @yield('content')
    </main>

    <footer class="border-t border-gray-200">
        <div class="max-w-4xl mx-auto px-4 py-6 text-sm text-gray-500">
            Primainti
        </div>
    </footer>
</body>
</html>
