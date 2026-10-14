{{-- Kepala seksi konsisten. Props: kicker, title, lead (opsional), align. --}}
@props(['kicker' => null, 'title', 'lead' => null, 'align' => 'left'])

<div class="mb-8 {{ $align === 'center' ? 'text-center mx-auto' : '' }} max-w-2xl">
    @if ($kicker)
        <p class="section-kicker">{{ $kicker }}</p>
    @endif
    <h2 class="section-title">{{ $title }}</h2>
    @if ($lead)
        <p class="section-lead {{ $align === 'center' ? 'mx-auto' : '' }}">{{ $lead }}</p>
    @endif
</div>
