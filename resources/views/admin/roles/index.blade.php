@extends('admin.layout')

@section('title', 'Roles')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-[#0F2A54]">Roles &amp; Permissions</h1>

        @can('create', App\Models\Role::class)
            <a href="{{ route('admin.roles.create') }}"
               class="rounded-lg bg-[#F97316] hover:bg-[#EA580C] text-white text-sm font-semibold px-4 py-2 transition">
                + Tambah Role
            </a>
        @endcan
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left text-gray-500">
                    <th class="py-2 pr-4">Nama</th>
                    <th class="py-2 pr-4">Deskripsi</th>
                    <th class="py-2 pr-4">Permissions</th>
                    <th class="py-2 pr-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                    <tr class="border-b border-gray-100 align-top">
                        <td class="py-3 pr-4 font-medium">{{ $role->name }}</td>
                        <td class="py-3 pr-4 text-gray-600">{{ $role->description ?: '—' }}</td>
                        <td class="py-3 pr-4">
                            <span class="text-xs text-gray-500">{{ $role->permissions->count() }} permission</span>
                        </td>
                        <td class="py-3 pr-4">
                            <div class="flex justify-end items-center gap-2">
                                @can('update', $role)
                                    <a href="{{ route('admin.roles.edit', $role) }}"
                                       class="text-[#0F2A54] hover:underline text-xs">Edit</a>
                                @endcan

                                @can('delete', $role)
                                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                                          onsubmit="return confirm('Hapus role {{ $role->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $roles->links() }}
    </div>
@endsection
