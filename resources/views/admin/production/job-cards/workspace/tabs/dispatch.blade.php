@php
    $checklist = $tabData['checklist'] ?? [];
    $eligibility = $tabData['dispatch_eligibility'] ?? ['eligible' => false, 'blockers' => [], 'warnings' => []];
    $dnEligibility = $tabData['delivery_note_eligibility'] ?? ['eligible' => false, 'blockers' => []];
    $activeNote = $tabData['active_delivery_note'] ?? null;
    $history = $tabData['delivery_history'] ?? collect();
    $invoiceStatus = $tabData['invoice_status'] ?? ['label' => '—', 'state' => 'na'];
    $jobCard = $jobCard ?? null;
@endphp

@if (! empty($eligibility['blockers']))
    @include('admin.production.job-cards.workspace.partials.control-alerts', [
        'alerts' => collect($eligibility['blockers'])->map(fn ($m) => ['type' => 'error', 'message' => $m])->all(),
    ])
@endif

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
    <x-admin.card class="lg:col-span-1">
        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Readiness score') }}</h3>
        <p class="text-3xl font-bold tabular-nums text-erp-primary">{{ $tabData['readiness_score'] ?? 0 }}%</p>
        <p class="mt-2 text-sm text-slate-600">
            @if ($eligibility['eligible'] ?? false)
                {{ __('Eligible to mark ready for dispatch') }}
            @else
                {{ __('Dispatch blocked until checklist items pass') }}
            @endif
        </p>
    </x-admin.card>

    <x-admin.card class="lg:col-span-2">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Dispatch readiness checklist') }}</h3>
        <ul class="divide-y divide-erp-border">
            @foreach ($checklist as $item)
                @php
                    $stateBadge = match ($item['state']) {
                        'passed' => 'bg-emerald-100 text-emerald-800',
                        'failed' => 'bg-red-100 text-red-800',
                        'warning' => 'bg-amber-100 text-amber-800',
                        default => 'bg-slate-100 text-slate-600',
                    };
                @endphp
                <li class="flex items-center justify-between gap-4 py-2.5 text-sm">
                    <span class="font-medium text-erp-primary">{{ $item['label'] }}</span>
                    <span class="text-slate-500">{{ $item['detail'] }}</span>
                    <span class="erp-badge shrink-0 {{ $stateBadge }}">{{ ucfirst($item['state']) }}</span>
                </li>
            @endforeach
        </ul>
    </x-admin.card>
</div>

<div class="mb-4">
    <x-admin.card>
        <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
            <span class="font-semibold text-erp-primary">{{ __('Invoice status') }}</span>
            <span class="erp-badge">{{ $invoiceStatus['label'] ?? '—' }}</span>
            @if (! empty($invoiceStatus['invoice']))
                <a href="{{ route('admin.accounting.invoices.show', $invoiceStatus['invoice']) }}" class="font-mono text-indigo-600">{{ $invoiceStatus['invoice']->invoice_number }}</a>
            @endif
        </div>
    </x-admin.card>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <x-admin.card>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Delivery note') }}</h3>
        @if ($activeNote)
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Number') }}</dt>
                    <dd><a href="{{ route('admin.dispatch.delivery-notes.show', $activeNote) }}" class="font-mono text-indigo-600">{{ $activeNote->delivery_note_number }}</a></dd>
                </div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Status') }}</dt><dd><x-admin.enum-status-badge :status="$activeNote->status->value" /></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Dispatch date') }}</dt><dd>{{ $activeNote->dispatched_at?->format('M j, Y') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Delivery date') }}</dt><dd>{{ $activeNote->delivered_at?->format('M j, Y') ?? $activeNote->delivery_date->format('M j, Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Recipient') }}</dt><dd>{{ $activeNote->recipient_name ?? '—' }}</dd></div>
            </dl>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('admin.dispatch.delivery-notes.show', $activeNote) }}" class="erp-btn-secondary text-sm">{{ __('View delivery note') }}</a>
                @can('dispatch', $activeNote)
                    @if ($activeNote->status->canDispatch())
                        <form method="POST" action="{{ route('admin.dispatch.delivery-notes.dispatch', $activeNote) }}">@csrf
                            <x-primary-button type="submit" class="text-sm">{{ __('Dispatch') }}</x-primary-button>
                        </form>
                    @endif
                @endcan
                @can('deliver', $activeNote)
                    @if ($activeNote->status->canDeliver())
                        <form method="POST" action="{{ route('admin.dispatch.delivery-notes.deliver', $activeNote) }}">@csrf
                            <x-primary-button type="submit" class="text-sm">{{ __('Deliver') }}</x-primary-button>
                        </form>
                    @endif
                @endcan
                @can('create', App\Models\Accounting\Invoice::class)
                    @if ($activeNote->isInvoiceable() && ! $activeNote->activeInvoice)
                        <form method="POST" action="{{ route('admin.dispatch.delivery-notes.generate-invoice', $activeNote) }}">@csrf
                            <x-primary-button type="submit" class="text-sm">{{ __('Generate invoice') }}</x-primary-button>
                        </form>
                    @endif
                @endcan
            </div>
        @elseif ($dnEligibility['eligible'] ?? false)
            @can('create', App\Models\Dispatch\DeliveryNote::class)
                <form method="POST" action="{{ route('admin.dispatch.delivery-notes.store-from-job', $jobCard) }}" class="mt-2">
                    @csrf
                    <x-primary-button type="submit">{{ __('Create delivery note') }}</x-primary-button>
                </form>
            @else
                <p class="text-sm text-slate-500">{{ __('You do not have permission to create delivery notes.') }}</p>
            @endcan
        @else
            <ul class="list-disc ps-5 text-sm text-red-700">
                @foreach ($dnEligibility['blockers'] ?? [] as $blocker)
                    <li>{{ $blocker }}</li>
                @endforeach
            </ul>
        @endif
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
