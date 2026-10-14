@extends('admin.layout')

@section('title', 'Tambah Produk')

@section('content')
    <h1 class="text-xl font-bold text-[#0F2A54] mb-6">Tambah Produk</h1>

    <form method="POST" action="{{ route('admin.products.store') }}">
        @csrf

        @include('admin.products._form', ['submitLabel' => 'Buat produk'])
    </form>
@endsection
