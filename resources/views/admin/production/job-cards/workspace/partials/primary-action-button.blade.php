@php
    $action = $action ?? null;
    $completion = $completion ?? ['eligible' => false];
    $size = $size ?? 'md';
    $btnClass = $size === 'lg' ? 'job-360-hero__action erp-btn-primary' : 'erp-btn-primary text-sm';
@endphp

@if ($action)
    @if (($action['type'] ?? '') === 'post')
        <form method="POST" action="{{ $action['url'] }}" class="inline">
            @csrf
            <button type="submit" @class([$btnClass, 'erp-btn-secondary' => ($action['variant'] ?? '') !== 'primary'])>
                {{ $action['label'] }}
            </button>
        </form>
    @elseif (
        ($action['type'] ?? '') === 'link'
        && str_contains((string) ($action['url'] ?? ''), 'tab=outputs')
        && ($completion['eligible'] ?? false)
        && auth()->user()?->can('production.outputs.post')
    )
        <button type="button" @class([$btnClass, 'erp-btn-secondary' => ($action['variant'] ?? '') !== 'primary']) data-open-dialog="complete-fg-modal">
            {{ $action['label'] }}
        </button>
    @elseif (($action['type'] ?? '') === 'link')
        <a
            href="{{ $action['url'] }}"
            @class([$btnClass, 'erp-btn-secondary' => ($action['variant'] ?? '') !== 'primary'])
            data-turbo-frame="erp-main"
        >{{ $action['label'] }}</a>
    @endif
@endif
