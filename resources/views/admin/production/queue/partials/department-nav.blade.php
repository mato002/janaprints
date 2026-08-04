@php
    use App\Support\Navigation\WorkspaceEmbed;
@endphp

<nav class="production-floor-dept-segments mb-2" aria-label="{{ __('Department queues') }}">
    @foreach ($departmentNav as $item)
        @continue(($item['slug'] ?? '') === '')
        <a
            href="{{ WorkspaceEmbed::url($item['url']) }}"
            @class([
                'production-floor-dept-segment',
                'production-floor-dept-segment--'.$item['slug'] => filled($item['slug'] ?? null),
                'production-floor-dept-segment--active' => $item['active'],
            ])
            data-turbo-frame="{{ WorkspaceEmbed::turboFrame() }}"
            data-turbo-action="advance"
        >{{ $item['label'] }}</a>
    @endforeach
</nav>
