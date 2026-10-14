@extends('admin.layout')

@section('title', 'Detail Pesan')

@section('content')
    <a href="{{ route('admin.contact-messages.index') }}" class="text-sm text-gray-600 hover:text-gray-900">← Semua pesan</a>

    <h1 class="text-xl font-bold text-[#0F2A54] mt-2 mb-6">{{ $message->subject }}</h1>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 space-y-1">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <dl class="rounded-xl border border-gray-200 p-5 grid md:grid-cols-2 gap-x-6 gap-y-3 text-sm mb-6">
        <div><dt class="text-gray-500">Nama</dt><dd class="font-medium">{{ $message->name }}</dd></div>
        <div><dt class="text-gray-500">Email</dt><dd><a href="mailto:{{ $message->email }}" class="text-[#1E4278] underline">{{ $message->email }}</a></dd></div>
        <div><dt class="text-gray-500">Perusahaan</dt><dd>{{ $message->company ?: '—' }}</dd></div>
        <div><dt class="text-gray-500">Telepon</dt><dd>{{ $message->phone ?: '—' }}</dd></div>
        <div><dt class="text-gray-500">Diterima</dt><dd>{{ $message->created_at->format('d M Y H:i') }} (IP: {{ $message->ip_address ?: '—' }})</dd></div>
        <div><dt class="text-gray-500">Status</dt><dd class="font-medium">{{ $message->status }}</dd></div>
        <div class="md:col-span-2"><dt class="text-gray-500">Pesan</dt><dd class="mt-1 whitespace-pre-wrap">{{ $message->message }}</dd></div>
    </dl>

    @can('update', $message)
        <form method="POST" action="{{ route('admin.contact-messages.update', $message) }}"
              class="rounded-xl border border-gray-200 p-5 space-y-4">
            @csrf
            @method('PUT')

            <div class="max-w-xs">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status" name="status"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                    @foreach (\App\Models\ContactMessage::STATUSES as $status)
                        <option value="{{ $status }}" @selected(old('status', $message->status) === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="internal_note" class="block text-sm font-medium text-gray-700 mb-1">Catatan internal</label>
                <textarea id="internal_note" name="internal_note" rows="3" maxlength="5000"
                          placeholder="Hanya terlihat oleh admin"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">{{ old('internal_note', $message->internal_note) }}</textarea>
            </div>

            <button type="submit"
                    class="rounded-lg bg-[#F97316] hover:bg-[#EA580C] text-white text-sm font-semibold px-5 py-2.5 transition">
                Simpan
            </button>
        </form>
    @endcan
@endsection
