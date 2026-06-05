@php
    $currentSession = $sessionWidget['session'] ?? null;
    $sessionMetrics = $sessionWidget['metrics'] ?? null;
@endphp

<x-admin.card class="mb-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h3 class="font-semibold">{{ __('Current session') }}</h3>
            @if ($currentSession)
                <p class="mt-1 text-sm text-slate-500">
                    {{ __('Session :number — opened :time', [
                        'number' => $currentSession->session_number,
                        'time' => $currentSession->opened_at?->format('Y-m-d H:i'),
                    ]) }}
                </p>
            @else
                <p class="mt-1 text-sm text-slate-500">{{ __('No active cashier session.') }}</p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            @can('open', App\Models\Pos\PosSession::class)
                @unless ($currentSession)
                    <a href="{{ route('admin.commercial.pos.sessions.create') }}" class="erp-btn-primary">{{ __('Open session') }}</a>
                @endunless
            @endcan
            @if ($currentSession)
                <a href="{{ route('admin.commercial.pos.sessions.show', $currentSession) }}" class="erp-btn-secondary">{{ __('View session') }}</a>
                @can('close', $currentSession)
                    <a href="{{ route('admin.commercial.pos.sessions.close', $currentSession) }}" class="erp-btn-secondary">{{ __('Close session') }}</a>
                @endcan
            @endif
        </div>
    </div>

    @if ($currentSession && $sessionMetrics)
        <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-5">
            <div class="rounded-lg border border-erp-border bg-slate-50 px-3 py-2">
                <p class="text-xs text-slate-500">{{ __('Cashier') }}</p>
                <p class="font-medium">{{ $currentSession->cashier?->name }}</p>
            </div>
            <div class="rounded-lg border border-erp-border bg-slate-50 px-3 py-2">
                <p class="text-xs text-slate-500">{{ __('Opening float') }}</p>
                <p class="font-medium tabular-nums">{{ number_format($currentSession->opening_float, 2) }}</p>
            </div>
            <div class="rounded-lg border border-erp-border bg-slate-50 px-3 py-2">
                <p class="text-xs text-slate-500">{{ __('Sales count') }}</p>
                <p class="font-medium tabular-nums">{{ $sessionMetrics['sales_count'] }}</p>
            </div>
            <div class="rounded-lg border border-erp-border bg-slate-50 px-3 py-2">
                <p class="text-xs text-slate-500">{{ __('Current sales value') }}</p>
                <p class="font-medium tabular-nums">{{ number_format($sessionMetrics['total_sales_value'], 2) }}</p>
            </div>
            <div class="rounded-lg border border-erp-border bg-slate-50 px-3 py-2">
                <p class="text-xs text-slate-500">{{ __('Terminal') }}</p>
                <p class="font-medium">{{ $currentSession->terminal ?? '—' }}</p>
            </div>
        </div>
    @endif
</x-admin.card>
