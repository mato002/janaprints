@props([
    'title',
    'description' => null,
    'compact' => false,
])

<div {{ $attributes->merge(['class' => 'exec-empty-state' . ($compact ? ' exec-empty-state--compact' : '')]) }}>
    <svg class="exec-empty-state__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
    </svg>
    <p class="exec-empty-state__title">{{ $title }}</p>
    @if ($description)
        <p class="exec-empty-state__desc">{{ $description }}</p>
    @endif
</div>
