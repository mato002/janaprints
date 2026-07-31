@php
    $snapshot = $panel['snapshot'] ?? [];
    $estimate = $panel['estimate'] ?? [];
    $ready = (bool) ($panel['ready'] ?? false);
@endphp

<section class="mb-4">
    <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('Order snapshot') }}</p>
    <dl class="space-y-1.5 text-sm">
        <div class="flex justify-between gap-2">
            <dt class="text-slate-500">{{ __('Order') }}</dt>
            <dd class="font-mono font-medium text-slate-900">{{ $snapshot['order_number'] ?? '—' }}</dd>
        </div>
        <div class="flex justify-between gap-2">
            <dt class="text-slate-500">{{ __('Customer') }}</dt>
            <dd class="text-right font-medium text-slate-900">{{ $snapshot['customer'] ?? '—' }}</dd>
        </div>
        <div class="flex justify-between gap-2">
            <dt class="text-slate-500">{{ __('Product') }}</dt>
            <dd class="text-right font-medium text-slate-900">{{ $snapshot['product'] ?? '—' }}</dd>
        </div>
        <div class="flex justify-between gap-2">
            <dt class="text-slate-500">{{ __('Quantity') }}</dt>
            <dd class="font-mono text-slate-900">{{ $snapshot['quantity'] ?? '—' }}</dd>
        </div>
        <div class="flex justify-between gap-2">
            <dt class="text-slate-500">{{ __('Due') }}</dt>
            <dd class="text-slate-900">{{ $snapshot['due'] ?? '—' }}</dd>
        </div>
        <div class="flex justify-between gap-2">
            <dt class="text-slate-500">{{ __('Priority') }}</dt>
            <dd class="text-slate-900">{{ $snapshot['priority'] ?? '—' }}</dd>
        </div>
    </dl>
</section>

<section class="mb-4 border-t border-erp-border pt-3">
    <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('Production status') }}</p>
    <ul class="space-y-1.5 text-sm">
        @foreach ($panel['dashboard'] ?? [] as $row)
            <li class="flex items-start justify-between gap-2">
                <span class="text-slate-700">{{ $row['label'] }}</span>
                <span @class([
                    'shrink-0 text-xs font-semibold',
                    'text-emerald-700' => ($row['passed'] ?? false),
                    'text-amber-700' => ! ($row['passed'] ?? false) && ($row['severity'] ?? '') === 'warning',
                    'text-rose-700' => ! ($row['passed'] ?? false) && ($row['severity'] ?? '') !== 'warning',
                ])>
                    {{ ($row['passed'] ?? false) ? '✓' : '!' }}
                </span>
            </li>
        @endforeach
    </ul>

    @if ($ready)
        <p class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-emerald-800">
            {{ __('Ready for production') }}
        </p>
    @endif
</section>

@if (($estimate['department'] ?? null) || ($estimate['work_center'] ?? null) || ($estimate['job_card_number'] ?? null))
    <section class="mb-4 border-t border-erp-border pt-3">
        <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('Estimated production') }}</p>
        <dl class="space-y-1.5 text-sm">
            @if ($estimate['department'] ?? null)
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-500">{{ __('Department') }}</dt>
                    <dd class="text-slate-900">{{ $estimate['department'] }}</dd>
                </div>
            @endif
            @if ($estimate['work_center'] ?? null)
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-500">{{ __('Work center') }}</dt>
                    <dd class="text-slate-900">{{ $estimate['work_center'] }}</dd>
                </div>
            @endif
            @if ($estimate['queue_status'] ?? null)
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-500">{{ __('Queue') }}</dt>
                    <dd class="text-slate-900">{{ $estimate['queue_status'] }}</dd>
                </div>
            @endif
            @if ($estimate['job_card_number'] ?? null)
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-500">{{ __('Job') }}</dt>
                    <dd class="font-mono text-slate-900">{{ $estimate['job_card_number'] }}</dd>
                </div>
            @endif
        </dl>
    </section>
@else
    <section class="mb-4 border-t border-erp-border pt-3">
        <p class="mb-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('Estimated production') }}</p>
        <p class="text-xs text-slate-500">{{ __('Department and work center are assigned when the job is released to the queue.') }}</p>
    </section>
@endif

<section class="border-t border-erp-border pt-3">
    <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('Warnings') }}</p>
    @if (count($panel['warnings'] ?? []) === 0 && $ready)
        <p class="text-sm text-emerald-700">{{ __('None — safe to release.') }}</p>
    @elseif (count($panel['warnings'] ?? []) === 0)
        <p class="text-sm text-slate-600">{{ __('Resolve readiness items on the left before releasing.') }}</p>
    @else
        <ul class="space-y-2">
            @foreach ($panel['warnings'] as $warning)
                <li @class([
                    'rounded-lg border px-3 py-2 text-xs',
                    'border-rose-200 bg-rose-50 text-rose-900' => ($warning['severity'] ?? '') === 'blocker',
                    'border-amber-200 bg-amber-50 text-amber-900' => ($warning['severity'] ?? '') !== 'blocker',
                ])>
                    <p class="font-semibold">⚠ {{ $warning['title'] ?? __('Attention') }}</p>
                    @if (! empty($warning['message']))
                        <p class="mt-0.5">{{ $warning['message'] }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</section>
