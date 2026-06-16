@props([
    'icon' => 'inbox',
    'message',
    'actionLabel' => null,
    'actionRoute' => null,
])

<div {{ $attributes->merge(['class' => 'client-empty-state']) }}>
    <div class="client-empty-state__icon">
        <x-client.icon :name="$icon" class="h-6 w-6" />
    </div>
    <p class="client-empty-state__message">{{ $message }}</p>
    @if ($actionLabel && $actionRoute)
        <a href="{{ $actionRoute }}" class="client-empty-state__action">
            {{ $actionLabel }}
            <x-client.icon name="arrow-right" class="h-4 w-4" />
        </a>
    @endif
</div>
