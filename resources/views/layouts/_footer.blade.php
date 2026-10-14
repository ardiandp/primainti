{{-- Footer publik dari CMS: tagline, menu footer, kontak, sosmed. --}}
@php($footerLogo = $settings->media('logo_id'))
@php($tagline = $locale === 'id' ? $settings->get('tagline_id') : $settings->get('tagline_en'))
@php($address = $settings->get('address'))
@php($footerPhone = $settings->get('contact_phone'))
@php($whatsapp = $settings->get('contact_whatsapp'))
@php($email = $settings->get('contact_email'))
@php($hours = $settings->get('opening_hours'))
@php($socials = $settings->socialLinks())

<footer class="bg-[#0A1D3A] text-gray-300">
    <div class="max-w-6xl mx-auto px-4 py-10 grid gap-8 md:grid-cols-3 text-sm">
        <div>
            <div class="flex items-center gap-2">
                @if ($footerLogo)
                    <img src="{{ $footerLogo->url() }}" alt="Primainti" class="h-10 w-auto">
                @else
                    <span class="font-bold tracking-wide text-lg text-white">Primainti</span>
                @endif
            </div>
            @if ($tagline)
                <p class="mt-3 text-gray-400">{{ $tagline }}</p>
            @endif
            @if (! empty($socials))
                <div class="mt-4 flex gap-2">
                    @foreach ($socials as $social)
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener"
                           aria-label="{{ $social['label'] }}"
                           class="rounded-lg border border-white/20 px-3 py-1.5 hover:bg-white/10">{{ $social['label'] }}</a>
                    @endforeach
                </div>
            @endif
        </div>

        <nav aria-label="Navigasi footer">
            <h2 class="font-bold text-white mb-3">Navigasi</h2>
            <ul class="space-y-1.5">
                @forelse ($footerMenu as $node)
                    <li>
                        <a href="{{ $node['url'] }}"
                           @if ($node['blank']) target="_blank" rel="noopener" @endif
                           class="hover:text-white">{{ $node['label'] }}</a>
                    </li>
                    @foreach ($node['children'] as $child)
                        <li class="pl-4">
                            <a href="{{ $child['url'] }}"
                               @if ($child['blank']) target="_blank" rel="noopener" @endif
                               class="text-gray-400 hover:text-white">↳ {{ $child['label'] }}</a>
                        </li>
                    @endforeach
                @empty
                    <li class="text-gray-500">—</li>
                @endforelse
            </ul>
        </nav>

        <div>
            <h2 class="font-bold text-white mb-3">Kontak</h2>
            <address class="not-italic space-y-1.5">
                @if ($address)
                    <p>{{ $address }}</p>
                @endif
                @if ($footerPhone)
                    <p>Telp: <a href="tel:{{ preg_replace('/\s+/', '', $footerPhone) }}" class="hover:text-white">{{ $footerPhone }}</a></p>
                @endif
                @if ($whatsapp)
                    <p>WA: <a href="https://wa.me/{{ preg_replace('/\D+/', '', $whatsapp) }}" target="_blank" rel="noopener" class="hover:text-white">{{ $whatsapp }}</a></p>
                @endif
                @if ($email)
                    <p>Email: <a href="mailto:{{ $email }}" class="hover:text-white">{{ $email }}</a></p>
                @endif
                @if ($hours)
                    <p class="text-gray-400">{{ $hours }}</p>
                @endif
                @if (! $address && ! $footerPhone && ! $email)
                    <p class="text-gray-500">—</p>
                @endif
            </address>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="max-w-6xl mx-auto px-4 py-4 text-xs text-gray-500">
            © {{ date('Y') }} Primainti
        </div>
    </div>
</footer>
