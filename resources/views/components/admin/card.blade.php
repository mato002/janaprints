@props([
    'padding' => true,
    'hover' => false,
])

<div {{ $attributes->merge([
    'class' => 'rounded-lg border border-erp-border bg-erp-card shadow-card transition-shadow duration-200'
        .($hover ? ' hover:shadow-card-hover' : '')
        .($padding ? ' p-4' : ''),
]) }}>
    @isset($header)
        <div class="border-b border-erp-border px-4 py-3 {{ $padding ? '' : '' }}">
            {{ $header }}
        </div>
    @endisset

    @isset($body)
        <div class="{{ isset($header) || isset($footer) ? 'px-4 py-3' : '' }}">
            {{ $body }}
        </div>
    @else
        {{ $slot }}
    @endisset

    @isset($footer)
        <div class="border-t border-erp-border px-4 py-3 bg-erp-page/50 rounded-b-lg">
            {{ $footer }}
        </div>
    @endisset
</div>
