@extends('admin.layout')

@section('title', 'Ubah Item Galeri')

@section('content')
    <h1 class="text-xl font-bold text-[#0F2A54] mb-6">Ubah Item Galeri</h1>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.gallery-items.update', $item) }}">
        @csrf
        @method('PUT')

        @include('admin.gallery-items._form', ['submitLabel' => 'Simpan perubahan'])
    </form>
@endsection
