@extends('admin.layout')

@section('title', 'Users')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-[#0F2A54]">Users</h1>

        @can('create', App\Models\User::class)
            <a href="{{ route('admin.users.create') }}"
               class="rounded-lg bg-[#F97316] hover:bg-[#EA580C] text-white text-sm font-semibold px-4 py-2 transition">
                + Tambah User
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
                    <th class="py-2 pr-4">Email</th>
                    <th class="py-2 pr-4">Role</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="border-b border-gray-100">
                        <td class="py-3 pr-4 font-medium">{{ $user->name }}</td>
                        <td class="py-3 pr-4 text-gray-600">{{ $user->email }}</td>
                        <td class="py-3 pr-4">
                            @foreach ($user->roles as $role)
                                <span class="inline-block rounded-full bg-[#0F2A54]/10 text-[#0F2A54] text-xs px-2 py-0.5 mr-1">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </td>
                        <td class="py-3 pr-4">
                            @if ($user->is_active)
                                <span class="text-green-600 text-xs font-semibold">Aktif</span>
                            @else
                                <span class="text-red-600 text-xs font-semibold">Nonaktif</span>
                            @endif
                        </td>
                        <td class="py-3 pr-4">
                            <div class="flex justify-end items-center gap-2">
                                @can('update', $user)
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="text-[#0F2A54] hover:underline text-xs">Edit</a>
                                @endcan

                                @can($user->is_active ? 'deactivate' : 'activate', $user)
                                    <form method="POST"
                                          action="{{ $user->is_active
                                              ? route('admin.users.deactivate', $user)
                                              : route('admin.users.activate', $user) }}">
                                        @csrf
                                        <button type="submit" class="text-xs {{ $user->is_active ? 'text-red-600 hover:underline' : 'text-green-600 hover:underline' }}">
                                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                @endcan

                                @can('resetAccess', $user)
                                    <form method="POST" action="{{ route('admin.users.reset-access', $user) }}"
                                          onsubmit="return confirm('Reset semua role user ini?')">
                                        @csrf
                                        <button type="submit" class="text-xs text-gray-500 hover:underline">Reset Akses</button>
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
        {{ $users->links() }}
    </div>
@endsection
