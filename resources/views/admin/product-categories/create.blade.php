@extends('admin.layout')

@section('title', 'Tambah Kategori Produk')

@section('content')
    <h1 class="text-xl font-bold text-[#0F2A54] mb-6">Tambah Kategori Produk</h1>

    <form method="POST" action="{{ route('admin.product-categories.store') }}">
        @csrf

        @include('admin.product-categories._form', ['submitLabel' => 'Buat kategori'])
    </form>
@endsection
