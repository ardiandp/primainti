@extends('admin.layout')

@section('title', 'Pesan Kontak')

@section('content')
    <h1 class="text-xl font-bold text-[#0F2A54] mb-6">Pesan Kontak</h1>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex flex-wrap gap-2 mb-4 text-sm">
        <a href="{{ route('admin.contact-messages.index') }}"
           class="rounded-full px-3 py-1 border {{ empty($filters['status']) ? 'bg-[#0F2A54] text-white border-[#0F2A54]' : 'border-gray-300' }}">
            Semua
        </a>
        @foreach (\App\Models\ContactMessage::STATUSES as $status)
            <a href="{{ route('admin.contact-messages.index', ['status' => $status] + request()->except('status', 'page')) }}"
               class="rounded-full px-3 py-1 border {{ ($filters['status'] ?? '') === $status ? 'bg-[#0F2A54] text-white border-[#0F2A54]' : 'border-gray-300' }}">
                {{ $status }} ({{ $counts[$status] ?? 0 }})
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.contact-messages.index') }}" class="flex flex-wrap items-center gap-3 mb-6 text-sm">
        @if (! empty($filters['status']))
            <input type="hidden" name="status" value="{{ $filters['status'] }}">
        @endif
        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama / email / subjek…"
               class="rounded-lg border border-gray-300 px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
        <button type="submit" class="rounded-lg bg-[#0F2A54] text-white px-4 py-2">Cari</button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-200">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-2 font-medium">Tanggal</th>
                    <th class="px-4 py-2 font-medium">Nama</th>
                    <th class="px-4 py-2 font-medium">Subjek</th>
                    <th class="px-4 py-2 font-medium">Status</th>
                    <th class="px-4 py-2 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($messages as $message)
                    <tr class="border-t border-gray-100 {{ $message->is_read ? '' : 'bg-amber-50/50 font-medium' }}">
                        <td class="px-4 py-2 text-gray-500 whitespace-nowrap">{{ $message->created_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-2">{{ $message->name }}</td>
                        <td class="px-4 py-2 text-gray-600">{{ \Illuminate\Support\Str::limit($message->subject, 50) }}</td>
                        <td class="px-4 py-2">
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold
                                {{ $message->status === 'new' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $message->status === 'in_progress' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $message->status === 'resolved' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $message->status === 'spam' ? 'bg-gray-200 text-gray-600' : '' }}">
                                {{ $message->status }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('admin.contact-messages.show', $message) }}" class="text-sm text-[#1E4278] hover:underline">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr class="border-t border-gray-100">
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada pesan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $messages->links() }}
    </div>
@endsection
