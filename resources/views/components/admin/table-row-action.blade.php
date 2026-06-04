@props([
    'href' => null,
    'method' => null,
    'action' => null,
    'variant' => 'default',
    'confirm' => null,
])

@php
    $classes = match ($variant) {
        'danger' => 'text-red-700 hover:bg-red-50',
        default => 'text-slate-700 hover:bg-erp-page',
    };
@endphp

@if ($action && $method)
    <form method="POST" action="{{ $action }}" class="block" @if($confirm) onsubmit="return confirm(@js($confirm))" @endif>
        @csrf
        @if (in_array(strtoupper($method), ['PUT', 'PATCH', 'DELETE']))
            @method($method)
        @endif
        <button type="submit" {{ $attributes->merge(['class' => "flex w-full items-center gap-2 px-3 py-2 text-left text-sm {$classes}"]) }}>
            {{ $slot }}
        </button>
    </form>
@elseif ($href)
    <a
        href="{{ $href }}"
        {{ $attributes->merge(['class' => "flex w-full items-center gap-2 px-3 py-2 text-sm {$classes}"]) }}
        data-turbo-action="advance"
    >
        {{ $slot }}
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => "flex w-full items-center gap-2 px-3 py-2 text-left text-sm {$classes}"]) }}>
        {{ $slot }}
    </button>
@endif
