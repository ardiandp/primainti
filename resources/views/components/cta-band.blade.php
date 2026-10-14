{{-- Pita CTA global dari pengaturan. Sembunyi bila label/tautan kosong. --}}
@php($ctaLabel = ($locale ?? 'id') === 'id' ? $settings->get('cta_label_id') : $settings->get('cta_label_en'))
@php($ctaUrl = $settings->get('cta_url'))
@php($ctaPhone = $settings->get('contact_phone'))

@if ($ctaLabel && $ctaUrl)
    <section class="hero">
        <div class="hero-accent">
            <div class="max-w-6xl mx-auto px-4 py-12 flex flex-col md:flex-row md:items-center gap-6 justify-between">
                <h2 class="text-2xl font-bold">{{ $ctaLabel }}</h2>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ $ctaUrl }}" class="btn btn-primary">{{ $ctaLabel }}</a>
                    @if ($ctaPhone)
                        <a href="tel:{{ preg_replace('/\s+/', '', $ctaPhone) }}"
                           class="btn border border-white/40 text-white hover:bg-white/10">{{ $ctaPhone }}</a>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
