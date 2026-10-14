@extends('admin.layout')

@section('title', 'Tambah Kategori Galeri')

@section('content')
    <h1 class="text-xl font-bold text-[#0F2A54] mb-6">Tambah Kategori Galeri</h1>

    <form method="POST" action="{{ route('admin.gallery-categories.store') }}">
        @csrf

        @include('admin.gallery-categories._form', ['submitLabel' => 'Buat kategori'])
    </form>
@endsection
