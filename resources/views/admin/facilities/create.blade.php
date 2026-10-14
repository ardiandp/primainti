@extends('admin.layout')

@section('title', 'Tambah Fasilitas')

@section('content')
    <h1 class="text-xl font-bold text-[#0F2A54] mb-6">Tambah Fasilitas</h1>

    <form method="POST" action="{{ route('admin.facilities.store') }}">
        @csrf

        @include('admin.facilities._form', ['submitLabel' => 'Buat fasilitas'])
    </form>
@endsection
