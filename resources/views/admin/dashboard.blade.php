<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin — Primainti</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100">
    <main class="max-w-4xl mx-auto py-12 px-4">
        <h1 class="text-2xl font-bold text-[#0F2A54]">Dashboard Admin</h1>
        <p class="text-gray-600 mt-2">Selamat datang, {{ auth()->user()->name }}.</p>

        <form method="POST" action="{{ route('logout') }}" class="mt-6">
            @csrf
            <button type="submit" class="rounded-lg border border-[#0F2A54] text-[#0F2A54] px-4 py-2 hover:bg-[#0F2A54] hover:text-white transition">
                Logout
            </button>
        </form>
    </main>
</body>
</html>
