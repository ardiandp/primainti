@extends('layouts.public')

@section('seo_title', 'Kontak — Primainti')

@section('seo_head')
    <link rel="canonical" href="{{ route('public.contact.show', $locale) }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Kontak — Primainti">
@endsection

@section('content')
    <section class="section">
        <x-section-head kicker="Kontak" :title="__('contact.title')" />

        @if (session('status'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

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

    <section class="section section-alt -mx-4 px-4">
        <div class="max-w-2xl mx-auto">
            <x-section-head :title="__('contact.form_title')" align="center" />

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('public.contact.store', $locale) }}" class="card p-6 space-y-4">
                @csrf

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="contact-name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('contact.name') }} *</label>
                        <input type="text" id="contact-name" name="name" value="{{ old('name') }}" required maxlength="100"
                               autocomplete="name"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                    </div>

                    <div>
                        <label for="contact-email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('contact.email') }} *</label>
                        <input type="email" id="contact-email" name="email" value="{{ old('email') }}" required maxlength="255"
                               autocomplete="email"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                    </div>

                    <div>
                        <label for="contact-company" class="block text-sm font-medium text-gray-700 mb-1">{{ __('contact.company') }} <span class="text-gray-400">({{ __('contact.optional') }})</span></label>
                        <input type="text" id="contact-company" name="company" value="{{ old('company') }}" maxlength="150"
                               autocomplete="organization"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                    </div>

                    <div>
                        <label for="contact-phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('contact.phone') }} <span class="text-gray-400">({{ __('contact.optional') }})</span></label>
                        <input type="text" id="contact-phone" name="phone" value="{{ old('phone') }}" maxlength="50"
                               autocomplete="tel"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                    </div>
                </div>

                <div>
                    <label for="contact-subject" class="block text-sm font-medium text-gray-700 mb-1">{{ __('contact.subject') }} *</label>
                    <input type="text" id="contact-subject" name="subject" value="{{ old('subject') }}" required maxlength="150"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                </div>

                <div>
                    <label for="contact-message" class="block text-sm font-medium text-gray-700 mb-1">{{ __('contact.message') }} *</label>
                    <textarea id="contact-message" name="message" rows="5" required maxlength="5000"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">{{ old('message') }}</textarea>
                </div>

                {{-- Honeypot anti-spam: wajib kosong, disembunyikan dari manusia. --}}
                <div class="hidden" aria-hidden="true">
                    <label for="contact-website">Website</label>
                    <input type="text" id="contact-website" name="website" value="" tabindex="-1" autocomplete="off">
                </div>

                <button type="submit" class="btn btn-primary w-full md:w-auto">{{ __('contact.send') }}</button>
            </form>
        </div>
    </section>

    @include('components.cta-band')
@endsection
