@extends('admin.layout')

@section('title', 'Menu Navigasi')

@section('content')
    <h1 class="text-xl font-bold text-[#0F2A54] mb-6">Menu Navigasi</h1>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid md:grid-cols-2 gap-4">
        @foreach ($menus as $menu)
            <div class="rounded-xl border border-gray-200 p-5">
                <h2 class="font-bold text-[#0F2A54] capitalize">Menu {{ $menu->location }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $menu->items()->count() }} item</p>
                <a href="{{ route('admin.menus.edit', $menu) }}"
                   class="mt-3 inline-block rounded-lg bg-[#0F2A54] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1E4278]">
                    Kelola
                </a>
            </div>
        @endforeach
    </div>
@endsection
