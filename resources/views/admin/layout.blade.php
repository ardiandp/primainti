<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — Primainti Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 text-gray-900">
    <header class="bg-[#0F2A54] text-white">
        <div class="max-w-6xl mx-auto px-4 h-14 flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="font-bold tracking-wide">Primainti Admin</a>

            <div class="flex items-center gap-4 text-sm">
                <span class="text-gray-200">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="rounded-lg border border-white/60 px-3 py-1 hover:bg-white hover:text-[#0F2A54] transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="max-w-6xl mx-auto px-4 flex flex-col md:flex-row gap-6 py-8">
        <aside class="w-full md:w-56 shrink-0">
            <nav class="bg-white rounded-xl shadow p-4 space-y-1 text-sm">
                <a href="{{ route('admin.dashboard') }}"
                   class="block rounded-lg px-3 py-2 hover:bg-gray-100 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-100 font-semibold' : '' }}">
                    Dashboard
                </a>

                @if (auth()->user()->hasPermission('user.view'))
                    <a href="/admin/users"
                       class="block rounded-lg px-3 py-2 hover:bg-gray-100 {{ request()->is('admin/users*') ? 'bg-gray-100 font-semibold' : '' }}">
                        Users
                    </a>
                @endif

                @if (auth()->user()->hasPermission('role.view'))
                    <a href="/admin/roles"
                       class="block rounded-lg px-3 py-2 hover:bg-gray-100 {{ request()->is('admin/roles*') ? 'bg-gray-100 font-semibold' : '' }}">
                        Roles &amp; Permissions
                    </a>
                @endif
            </nav>
        </aside>

        <main class="flex-1 bg-white rounded-xl shadow p-6">
            @yield('content')
        </main>
    </div>
</body>
</html>
