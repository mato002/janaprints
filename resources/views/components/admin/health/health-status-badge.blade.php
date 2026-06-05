@props([
    'status' => 'unknown',
    'label' => null,
])

@php
    $statusKey = is_object($status) && enum_exists($status::class) ? $status->value : (string) $status;

    $tone = match ($statusKey) {
        'healthy', 'success', 'active', 'connected' => [
            'dot' => 'bg-emerald-500',
            'badge' => 'bg-emerald-50 text-emerald-800 ring-emerald-600/20',
            'label' => $label ?? __('Healthy'),
        ],
        'warning', 'pending' => [
            'dot' => 'bg-amber-500',
            'badge' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
            'label' => $label ?? __('Warning'),
        ],
        'critical', 'danger', 'stopped', 'disconnected' => [
            'dot' => 'bg-red-500',
            'badge' => 'bg-red-50 text-red-700 ring-red-600/20',
            'label' => $label ?? __('Critical'),
        ],
        default => [
            'dot' => 'bg-slate-400',
            'badge' => 'bg-slate-100 text-slate-700 ring-slate-500/20',
            'label' => $label ?? __('Unknown'),
        ],
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-semibold ring-1 ring-inset {$tone['badge']}"]) }}>
    <span class="h-2 w-2 shrink-0 rounded-full {{ $tone['dot'] }}" aria-hidden="true"></span>
    <span>{{ $tone['label'] }}</span>
</span>
