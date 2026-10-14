@extends('admin.layout')

@section('title', 'Tambah Halaman')

@section('content')
    <h1 class="text-xl font-bold text-[#0F2A54] mb-6">Tambah Halaman</h1>

    <form method="POST" action="{{ route('admin.pages.store') }}">
        @csrf

        @include('admin.pages._form', ['submitLabel' => 'Buat halaman'])
    </form>
@endsection
