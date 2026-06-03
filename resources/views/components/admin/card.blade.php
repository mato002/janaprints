@props([
    'padding' => true,
    'hover' => false,
])

<div {{ $attributes->merge([
    'class' => 'rounded-xl border border-erp-border bg-erp-card shadow-card transition-shadow duration-200'
        .($hover ? ' hover:shadow-card-hover' : '')
        .($padding ? ' p-6' : ''),
]) }}>
    @isset($header)
        <div class="border-b border-erp-border px-6 py-4 {{ $padding ? '' : '' }}">
            {{ $header }}
        </div>
    @endisset

    @isset($body)
        <div class="{{ isset($header) || isset($footer) ? 'px-6 py-5' : '' }}">
            {{ $body }}
        </div>
    @else
        {{ $slot }}
    @endisset

    @isset($footer)
        <div class="border-t border-erp-border px-6 py-4 bg-erp-page/50 rounded-b-xl">
            {{ $footer }}
        </div>
    @endisset
</div>
