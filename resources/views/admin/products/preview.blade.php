@extends('admin.layout')

@section('title', 'Pratinjau: '.$translation->name)

@section('content')
    <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 flex flex-wrap items-center gap-3">
        <span>Pratinjau (status: <strong>{{ $product->status }}</strong>, bahasa: <strong>{{ $locale }}</strong>) — tidak tampil publik kecuali published.</span>

        <span class="flex gap-1">
            @foreach (['id', 'en'] as $previewLocale)
                <a href="{{ route('admin.products.preview', [$product, 'locale' => $previewLocale]) }}"
                   class="rounded-md border border-amber-300 px-2 py-0.5 {{ $locale === $previewLocale ? 'bg-amber-200 font-semibold' : '' }}">
                    {{ strtoupper($previewLocale) }}
                </a>
            @endforeach
        </span>

        <a href="{{ route('admin.products.edit', $product) }}" class="underline">Kembali ubah</a>
    </div>

    @include('products._detail')
@endsection
