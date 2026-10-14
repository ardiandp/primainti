@extends('admin.layout')

@section('title', 'Tambah Item Menu')

@section('content')
    <h1 class="text-xl font-bold text-[#0F2A54] mb-6">Tambah Item — Menu {{ ucfirst($menu->location) }}</h1>

    @include('admin.menu-items._form', [
        'submitLabel' => 'Tambah item',
        'action' => route('admin.menu-items.store'),
    ])
@endsection
