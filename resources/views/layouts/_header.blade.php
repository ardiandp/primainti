{{-- Header publik dari CMS: logo, menu header, CTA, switcher bahasa. --}}
@php($logo = $settings->media('logo_id'))
@php($ctaLabel = $locale === 'id' ? $settings->get('cta_label_id') : $settings->get('cta_label_en'))
@php($ctaUrl = $settings->get('cta_url'))
@php($phone = $settings->get('contact_phone'))

<header class="bg-[#0F2A54] text-white">
    <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
        <a href="{{ url("/{$locale}") }}" class="flex items-center gap-2 shrink-0" aria-label="Primainti">
            @if ($logo)
                <img src="{{ $logo->url() }}" alt="Primainti" class="h-10 w-auto">
            @else
                <span class="font-bold tracking-wide text-lg">Primainti</span>
            @endif
        </a>

        <nav class="hidden md:flex items-center gap-1 text-sm" aria-label="Navigasi utama">
            @foreach ($headerMenu as $node)
                @if (empty($node['children']))
                    <a href="{{ $node['url'] }}"
                       @if ($node['blank']) target="_blank" rel="noopener" @endif
                       class="rounded-lg px-3 py-2 hover:bg-white/10">{{ $node['label'] }}</a>
                @else
                    <div class="relative group">
                        <a href="{{ $node['url'] }}"
                           @if ($node['blank']) target="_blank" rel="noopener" @endif
                           class="rounded-lg px-3 py-2 hover:bg-white/10 inline-flex items-center gap-1"
                           aria-haspopup="true">
                            {{ $node['label'] }} <span aria-hidden="true">▾</span>
                        </a>
                        <div class="absolute left-0 top-full pt-1 hidden group-hover:block group-focus-within:block">
                            <div class="min-w-48 rounded-xl bg-white text-gray-900 shadow-xl py-1 overflow-hidden">
                                @foreach ($node['children'] as $child)
                                    <a href="{{ $child['url'] }}"
                                       @if ($child['blank']) target="_blank" rel="noopener" @endif
                                       class="block px-4 py-2 text-sm hover:bg-gray-100">{{ $child['label'] }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>

        <div class="hidden md:flex items-center gap-3 text-sm shrink-0">
            @if ($phone)
                <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="text-gray-200 hover:text-white">{{ $phone }}</a>
            @endif

            @if ($ctaLabel && $ctaUrl)
                <a href="{{ $ctaUrl }}"
                   class="rounded-lg bg-[#F97316] hover:bg-[#EA580C] px-4 py-2 font-semibold transition">{{ $ctaLabel }}</a>
            @endif

            <span class="flex items-center gap-1 border border-white/30 rounded-lg px-2 py-1" role="group" aria-label="Bahasa">
                @foreach (['id' => 'ID', 'en' => 'EN'] as $code => $short)
                    @if ($code === $locale)
                        <span class="font-bold" aria-current="true">{{ $short }}</span>
                    @else
                        <a href="{{ $localeSwitcher->swap($code) }}" class="text-gray-300 hover:text-white">{{ $short }}</a>
                    @endif
                    @if (! $loop->last)
                        <span class="text-gray-500">|</span>
                    @endif
                @endforeach
            </span>
        </div>

        <div class="md:hidden flex items-center gap-2">
            <span class="flex items-center gap-1 text-sm border border-white/30 rounded-lg px-2 py-1">
                @foreach (['id' => 'ID', 'en' => 'EN'] as $code => $short)
                    @if ($code === $locale)
                        <span class="font-bold">{{ $short }}</span>
                    @else
                        <a href="{{ $localeSwitcher->swap($code) }}" class="text-gray-300">{{ $short }}</a>
                    @endif
                    @if (! $loop->last)
                        <span class="text-gray-500">|</span>
                    @endif
                @endforeach
            </span>
            <button type="button" data-mobile-toggle aria-expanded="false" aria-controls="mobile-menu"
                    aria-label="Buka menu" class="rounded-lg border border-white/30 px-3 py-1.5 text-xl leading-none">☰</button>
        </div>
    </div>

    <nav id="mobile-menu" class="hidden md:hidden border-t border-white/10 px-4 py-3 space-y-1 text-sm" aria-label="Navigasi seluler">
        @foreach ($headerMenu as $node)
            <a href="{{ $node['url'] }}"
               @if ($node['blank']) target="_blank" rel="noopener" @endif
               class="block rounded-lg px-3 py-2 hover:bg-white/10 font-medium">{{ $node['label'] }}</a>
            @foreach ($node['children'] as $child)
                <a href="{{ $child['url'] }}"
                   @if ($child['blank']) target="_blank" rel="noopener" @endif
                   class="block rounded-lg px-3 py-2 pl-8 text-gray-200 hover:bg-white/10">↳ {{ $child['label'] }}</a>
            @endforeach
        @endforeach

        @if ($ctaLabel && $ctaUrl)
            <a href="{{ $ctaUrl }}"
               class="block rounded-lg bg-[#F97316] px-3 py-2 font-semibold text-center">{{ $ctaLabel }}</a>
        @endif
    </nav>
</header>

<script>
// Toggle menu seluler (tanpa dependensi build).
(function () {
    const toggle = document.querySelector('[data-mobile-toggle]');
    const menu = document.getElementById('mobile-menu');

    if (!toggle || !menu) {
        return;
    }

    toggle.addEventListener('click', () => {
        const open = menu.classList.toggle('hidden');
        toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
        toggle.setAttribute('aria-label', open ? 'Buka menu' : 'Tutup menu');
    });
})();
</script>
