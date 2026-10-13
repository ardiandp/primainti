@extends('admin.layout')

@section('title', 'Edit User')

@section('content')
    <h1 class="text-xl font-bold text-[#0F2A54] mb-6">Edit User: {{ $user->name }}</h1>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="max-w-lg">
        @csrf
        @method('PUT')
        @include('admin.users._form')

        <div class="mt-6 flex gap-3">
            <button type="submit"
                    class="rounded-lg bg-[#F97316] hover:bg-[#EA580C] text-white text-sm font-semibold px-4 py-2 transition">
                Perbarui
            </button>
            <a href="{{ route('admin.users.index') }}"
               class="rounded-lg border border-gray-300 text-sm px-4 py-2 hover:bg-gray-50">Batal</a>
        </div>
    </form>
@endsection
