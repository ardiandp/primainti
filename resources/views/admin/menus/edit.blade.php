@extends('admin.layout')

@section('title', 'Kelola Menu '.ucfirst($menu->location))

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-[#0F2A54]">Menu {{ ucfirst($menu->location) }}</h1>

        @can('create', App\Models\MenuItem::class)
            <a href="{{ route('admin.menu-items.create', $menu) }}"
               class="rounded-lg bg-[#F97316] hover:bg-[#EA580C] text-white text-sm font-semibold px-4 py-2 transition">
                + Item
            </a>
        @endcan
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    @if ($items->isEmpty())
        <p class="rounded-xl border border-dashed border-gray-300 px-4 py-12 text-center text-gray-500">
            Belum ada item. Tambahkan item pertama untuk mengisi navigasi {{ $menu->location }}.
        </p>
    @else
        <div class="overflow-x-auto rounded-xl border border-gray-200">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-gray-600">
                    <tr>
                        <th class="px-4 py-2 font-medium">Label</th>
                        <th class="px-4 py-2 font-medium">Target</th>
                        <th class="px-4 py-2 font-medium">Status target</th>
                        <th class="px-4 py-2 font-medium">Urutan</th>
                        <th class="px-4 py-2 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items->where('parent_id', null) as $item)
                        @include('admin.menus._row', ['item' => $item, 'depth' => 0])
                        @foreach ($items->where('parent_id', $item->id) as $child)
                            @include('admin.menus._row', ['item' => $child, 'depth' => 1])
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <a href="{{ route('admin.menus.index') }}" class="mt-4 inline-block text-sm text-gray-600 hover:text-gray-900">← Semua menu</a>
@endsection
