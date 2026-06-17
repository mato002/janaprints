@php
    $health = $dashboard['communication_health'] ?? null;
    $statusColors = [
        'healthy' => 'bg-emerald-500',
        'warning' => 'bg-amber-400',
        'critical' => 'bg-red-500',
    ];
@endphp

@if ($health)
    <section class="exec-integration-health rounded-lg border border-erp-border bg-white p-4" aria-label="{{ __('Communication health') }}">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-erp-primary">{{ __('Communication Health') }}</h2>
            <a href="{{ $health['url'] ?? route('admin.communications.email.settings') }}" class="text-xs text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ __('Diagnostics') }}</a>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-md border border-erp-border px-3 py-2 text-xs">
                <span class="mb-2 flex items-center gap-2 font-medium text-slate-700">
                    <span class="h-2 w-2 rounded-full {{ $statusColors[$health['level']] ?? $statusColors['warning'] }}"></span>
                    {{ $health['label'] }}
                </span>
                <span class="text-slate-500">{{ __('Overall status') }}</span>
            </div>
            <div class="rounded-md border border-erp-border px-3 py-2 text-xs">
                <span class="block font-medium text-slate-700">{{ $health['failure_rate'] }}%</span>
                <span class="text-slate-500">{{ __('Failure rate (7d)') }}</span>
            </div>
            <div class="rounded-md border border-erp-border px-3 py-2 text-xs">
                <span class="block font-medium text-slate-700">{{ $health['queue_backlog'] }}</span>
                <span class="text-slate-500">{{ __('Queue backlog') }}</span>
            </div>
            <div class="rounded-md border border-erp-border px-3 py-2 text-xs">
                <span class="block font-medium text-slate-700">{{ $health['smtp_available'] ? __('Available') : __('Missing') }}</span>
                <span class="text-slate-500">{{ __('SMTP') }}</span>
            </div>
        </div>
    </section>
@endif
