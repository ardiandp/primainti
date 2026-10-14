@extends('layouts.public')

@section('seo_title', 'Kontak — Primainti')

@section('content')
    <section class="section">
        <x-section-head kicker="Kontak" title="Hubungi Kami" />

        @php($address = $settings->get('address'))
        @php($phone = $settings->get('contact_phone'))
        @php($whatsapp = $settings->get('contact_whatsapp'))
        @php($email = $settings->get('contact_email'))
        @php($hours = $settings->get('opening_hours'))

        @if (! $address && ! $phone && ! $email)
            <div class="rounded-xl border border-dashed border-gray-300 px-4 py-12 text-center text-gray-500">
                Informasi kontak belum tersedia.
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2">
                @if ($address)
                    <div class="card p-5">
                        <h2 class="font-bold text-[#0F2A54]">Alamat</h2>
                        <p class="mt-1 text-sm text-gray-600">{{ $address }}</p>
                    </div>
                @endif
                @if ($phone || $whatsapp)
                    <div class="card p-5">
                        <h2 class="font-bold text-[#0F2A54]">Telepon</h2>
                        @if ($phone)
                            <p class="mt-1 text-sm"><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="text-[#1E4278] underline">{{ $phone }}</a></p>
                        @endif
                        @if ($whatsapp)
                            <p class="mt-1 text-sm">WA: <a href="https://wa.me/{{ preg_replace('/\D+/', '', $whatsapp) }}" target="_blank" rel="noopener" class="text-[#1E4278] underline">{{ $whatsapp }}</a></p>
                        @endif
                    </div>
                @endif
                @if ($email)
                    <div class="card p-5">
                        <h2 class="font-bold text-[#0F2A54]">Email</h2>
                        <p class="mt-1 text-sm"><a href="mailto:{{ $email }}" class="text-[#1E4278] underline">{{ $email }}</a></p>
                    </div>
                @endif
                @if ($hours)
                    <div class="card p-5">
                        <h2 class="font-bold text-[#0F2A54]">Jam Operasional</h2>
                        <p class="mt-1 text-sm text-gray-600">{{ $hours }}</p>
                    </div>
                @endif
            </div>
        @endif
    </section>

    @include('components.cta-band')
@endsection
