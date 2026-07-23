@php
    $operatorMode = (bool) ($operatorMode ?? false);
    $action = $action ?? [];
    $jobKey = $jobKey ?? null;
    $buttonClass = $buttonClass ?? 'erp-btn-primary text-xs py-1 px-2';
@endphp

@if (($action['type'] ?? '') === 'post')
    <form method="POST" action="{{ $action['url'] }}" @if ($operatorMode) data-erp-desk-form @endif>
        @csrf
        @if ($operatorMode)
            <input type="hidden" name="from" value="production-floor">
        @endif
        <button type="submit" class="{{ $buttonClass }}">{{ $action['label'] }}</button>
    </form>
@elseif (in_array($action['type'] ?? '', ['modal', 'qc'], true))
    @php
        $target = $action['target'] ?? (($action['type'] ?? '') === 'qc' ? 'qc' : '');
    @endphp
    <button
        type="button"
        class="{{ $buttonClass }}"
        @click="openActionModal(@js($jobKey), @js($target))"
    >{{ $action['label'] }}</button>
@elseif (($action['type'] ?? '') === 'panel')
    @php
        $panelFragment = parse_url($action['url'], PHP_URL_FRAGMENT) ?: '';
        $panelTarget = match ($panelFragment) {
            'quality' => 'qc',
            'outsource' => str_contains(strtolower($action['label']), 'return') ? 'outsource-return' : 'outsource-send',
            'fulfilment' => 'fulfilment',
            default => null,
        };
        $panelLinkUrl = $action['url'];
        if ($operatorMode) {
            $panelLinkUrl .= str_contains($panelLinkUrl, '?') ? '&' : '?';
            $panelLinkUrl .= 'from=production-floor';
        }
    @endphp
    @if ($panelTarget)
        <button type="button" class="{{ $buttonClass }}" @click="openActionModal(@js($jobKey), @js($panelTarget))">{{ $action['label'] }}</button>
    @else
        <a
            href="{{ $panelLinkUrl }}"
            class="{{ $buttonClass }}"
            @if ($operatorMode) data-erp-modal-open @else data-turbo-frame="erp-main" @endif
            @click.stop
        >{{ $action['label'] }}</a>
    @endif
@else
    @php
        $linkUrl = $action['url'];
        if ($operatorMode) {
            $linkUrl .= str_contains($linkUrl, '?') ? '&' : '?';
            $linkUrl .= 'from=production-floor';
        }
    @endphp
    <a href="{{ $linkUrl }}" class="{{ str_replace('erp-btn-primary', 'erp-btn-secondary', $buttonClass) }}" @if ($operatorMode) data-erp-modal-open @else data-turbo-frame="erp-main" @endif>{{ $action['label'] }}</a>
@endif
