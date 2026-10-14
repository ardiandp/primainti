@extends('admin.layout')

@section('title', 'Tambah Item Galeri')

@section('content')
    <h1 class="text-xl font-bold text-[#0F2A54] mb-6">Tambah Item Galeri</h1>

    <form method="POST" action="{{ route('admin.gallery-items.store') }}">
        @csrf

        @include('admin.gallery-items._form', ['submitLabel' => 'Buat item'])
    </form>
@endsection
