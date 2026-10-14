{{-- Baris item menu. Variabel: $item, $depth, $statuses. --}}
@php($status = $statuses[$item->id] ?? ['valid' => false, 'message' => 'Belum diperiksa.'])
<tr class="border-t border-gray-100">
    <td class="px-4 py-2 font-medium">
        <span style="padding-left: {{ $depth * 1.5 }}rem">{{ $depth > 0 ? '↳ ' : '' }}{{ $item->title }}</span>
        @if ($item->translations->isNotEmpty())
            <span class="block text-xs font-normal text-gray-500" style="padding-left: {{ $depth * 1.5 }}rem">
                @foreach ($item->translations as $translation)
                    [{{ $translation->locale }}] {{ $translation->title }}
                @endforeach
            </span>
        @endif
    </td>
    <td class="px-4 py-2 text-gray-500 text-xs">
        {{ $item->link_type }}:
        {{ $item->link_type === 'url' ? $item->url : '' }}
        {{ $item->link_type === 'page' ? 'halaman #'.$item->page_id : '' }}
        {{ $item->link_type === 'product_category' ? 'kategori #'.$item->product_category_id : '' }}
        {{ $item->link_type === 'route' ? $item->route_name : '' }}
        @if ($item->opens_in_new_tab)
            <span class="ml-1 rounded bg-gray-100 px-1">tab baru</span>
        @endif
    </td>
    <td class="px-4 py-2">
        @if ($status['valid'])
            <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">OK</span>
        @else
            <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700"
                  title="{{ $status['message'] }}">rusak</span>
        @endif
    </td>
    <td class="px-4 py-2 text-gray-500">{{ $item->position }}</td>
    <td class="px-4 py-2">
        <div class="flex justify-end gap-2">
            @can('update', $item)
                <a href="{{ route('admin.menu-items.edit', $item) }}" class="text-sm text-[#1E4278] hover:underline">Ubah</a>
            @endcan
            @can('delete', $item)
                <form method="POST" action="{{ route('admin.menu-items.destroy', $item) }}"
                      onsubmit="return confirm('Hapus item ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-600 hover:underline">Hapus</button>
                </form>
            @endcan
        </div>
    </td>
</tr>
