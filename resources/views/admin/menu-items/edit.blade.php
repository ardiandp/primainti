@extends('admin.layout')

@section('title', 'Ubah Item Menu')

@section('content')
    <h1 class="text-xl font-bold text-[#0F2A54] mb-6">Ubah Item — Menu {{ ucfirst($menu->location) }}</h1>

    @include('admin.menu-items._form', [
        'submitLabel' => 'Simpan perubahan',
        'action' => route('admin.menu-items.update', $item),
    ])
@endsection
