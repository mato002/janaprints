@php
    $presentation = $dispatchPresentation ?? $tabData['dispatch_presentation'] ?? [];
    $summary = $presentation['summary'] ?? [];
    $timeline = $presentation['timeline'] ?? [];
    $actions = $presentation['actions'] ?? ['primary' => null, 'secondary' => [], 'danger' => []];
    $courierIcon = $presentation['courier_icon'] ?? '🚚';
    $history = $tabData['delivery_history'] ?? collect();
    $invoiceStatus = $tabData['invoice_status'] ?? ['label' => '—', 'state' => 'na'];
@endphp

<div class="mb-6">
    <x-admin.card class="job-360-dispatch-summary">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-erp-border pb-4">
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Dispatch summary') }}</p>
                <div class="mt-1 flex flex-wrap items-center gap-2">
                    <span class="text-2xl" aria-hidden="true">{{ $courierIcon }}</span>
                    <h3 class="text-lg font-semibold text-slate-900">{{ $summary['delivery_note_number'] ?? '—' }}</h3>
                    <x-admin.enum-status-badge :status="$summary['status'] ?? 'draft'" />
                </div>
                <p class="mt-1 text-sm text-slate-600">{{ $presentation['next_action'] ?? '' }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if ($actions['primary'] ?? null)
                    <a
                        href="{{ $actions['primary']['url'] }}"
                        class="erp-btn-primary text-sm"
                        data-turbo-frame="erp-main"
                    >{{ $actions['primary']['label'] }}</a>
                @endif
                @foreach ($actions['secondary'] ?? [] as $action)
                    <a
                        href="{{ $action['url'] }}"
                        class="erp-btn-secondary text-sm"
                        @if (($action['target'] ?? null) === '_blank') target="_blank" rel="noopener" @else data-turbo-frame="erp-main" @endif
                    >{{ $action['label'] }}</a>
                @endforeach
                @foreach ($actions['danger'] ?? [] as $action)
                    <a
                        href="{{ $action['url'] }}"
                        class="text-sm font-medium text-red-600 hover:underline"
                        data-turbo-frame="erp-main"
                    >{{ $action['label'] }}</a>
                @endforeach
            </div>
        </div>

        <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <dt class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Dispatch status') }}</dt>
                <dd class="mt-0.5 font-medium text-slate-900">{{ $summary['status_label'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Dispatch date') }}</dt>
                <dd class="mt-0.5 font-medium text-slate-900">{{ $summary['dispatch_date'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Courier') }}</dt>
                <dd class="mt-0.5 font-medium text-slate-900">{{ $summary['courier'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Driver') }}</dt>
                <dd class="mt-0.5 font-medium text-slate-900">{{ $summary['driver'] ?? __('Not assigned') }}</dd>
            </div>
            <div>
                <dt class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Tracking number') }}</dt>
                <dd class="mt-0.5 font-mono text-sm font-medium text-slate-900">
                    @if (! empty($summary['track_url']))
                        <a href="{{ $summary['track_url'] }}" class="text-indigo-600 hover:underline" target="_blank" rel="noopener">{{ $summary['tracking_number'] }}</a>
                    @else
                        {{ $summary['tracking_number'] ?? '—' }}
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Recipient') }}</dt>
                <dd class="mt-0.5 font-medium text-slate-900">
                    {{ $summary['recipient_name'] ?? '—' }}
                    @if (! empty($summary['recipient_phone']))
                        <span class="block text-xs text-slate-500">{{ $summary['recipient_phone'] }}</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Package count') }}</dt>
                <dd class="mt-0.5 font-medium tabular-nums text-slate-900">{{ $summary['package_count'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Delivery date') }}</dt>
                <dd class="mt-0.5 font-medium text-slate-900">{{ $summary['delivery_date'] ?? '—' }}</dd>
            </div>
        </dl>

        @if (! empty($summary['delivery_address']))
            <p class="mt-4 text-sm text-slate-600">
                <span class="font-medium text-slate-800">{{ __('Delivery address') }}:</span>
                {{ $summary['delivery_address'] }}
            </p>
        @endif
    </x-admin.card>
</div>

<nav class="job-360-stage-timeline mb-6" aria-label="{{ __('Dispatch timeline') }}">
    <ol class="job-360-stage-timeline__track">
        @foreach ($timeline as $step)
            <li @class([
                'job-360-stage-timeline__step',
                'job-360-stage-timeline__step--'.match ($step['state']) {
                    'completed' => 'completed',
                    'current' => 'current',
                    default => 'future',
                },
            ])>
                <span class="job-360-stage-timeline__dot" aria-hidden="true"></span>
                <span class="job-360-stage-timeline__label">{{ $step['label'] }}</span>
                @if (! empty($step['at']))
                    <span class="block text-[10px] text-slate-500">{{ $step['at'] }}</span>
                @endif
                @unless ($loop->last)
                    <span @class([
                        'job-360-stage-timeline__connector',
                        'job-360-stage-timeline__connector--'.($step['state'] === 'completed' ? 'completed' : 'future'),
                    ]) aria-hidden="true"></span>
                @endunless
            </li>
        @endforeach
    </ol>
</nav>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <x-admin.card>
        <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
            <span class="font-semibold text-erp-primary">{{ __('Invoice status') }}</span>
            <span class="erp-badge">{{ $invoiceStatus['label'] ?? '—' }}</span>
            @if (! empty($invoiceStatus['invoice']))
                <a href="{{ route('admin.accounting.invoices.show', $invoiceStatus['invoice']) }}" class="font-mono text-indigo-600">{{ $invoiceStatus['invoice']->invoice_number }}</a>
            @endif
        </div>
        <p class="mt-3 text-sm text-slate-600">{{ __('Billing and proof of delivery are managed on the delivery note.') }}</p>
    </x-admin.card>

    <x-admin.card>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Delivery history') }}</h3>
        <ul class="divide-y divide-slate-100 text-sm">
            @forelse ($history as $note)
                <li class="flex justify-between py-2">
                    <a href="{{ route('admin.dispatch.delivery-notes.show', $note) }}" class="font-mono text-indigo-600">{{ $note->delivery_note_number }}</a>
                    <x-admin.enum-status-badge :status="$note->status->value" />
                </li>
            @empty
                <li class="py-4 text-slate-500">{{ __('No delivery notes for this job.') }}</li>
            @endforelse
        </ul>
    </x-admin.card>
</div>
