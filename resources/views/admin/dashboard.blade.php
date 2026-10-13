@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    <h1 class="text-xl font-bold text-[#0F2A54]">Dashboard</h1>
    <p class="text-gray-600 mt-2">Selamat datang, {{ auth()->user()->name }}.</p>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
        <div class="rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Role Anda</p>
            <p class="font-semibold mt-1">{{ auth()->user()->roleNames()->implode(', ') ?: '—' }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Total User</p>
            <p class="font-semibold mt-1">{{ \App\Models\User::count() }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Total Role</p>
            <p class="font-semibold mt-1">{{ \App\Models\Role::count() }}</p>
        </div>
    </div>
@endsection
