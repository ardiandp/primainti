@extends('admin.layout')

@section('title', 'Ubah Halaman')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-[#0F2A54]">Ubah Halaman</h1>

        <a href="{{ route('admin.pages.preview', $page) }}"
           class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">
            Pratinjau
        </a>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.pages.update', $page) }}">
        @csrf
        @method('PUT')

        @include('admin.pages._form', ['submitLabel' => 'Simpan perubahan'])
    </form>
@endsection
