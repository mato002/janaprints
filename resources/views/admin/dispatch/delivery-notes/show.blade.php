@php
    $note = $note;
@endphp
<x-admin-layout :title="$note->delivery_note_number" :breadcrumbs="[
    ['label' => __('Dispatch'), 'url' => route('admin.dispatch.dashboard')],
    ['label' => __('Delivery notes'), 'url' => route('admin.dispatch.delivery-notes.index')],
    ['label' => $note->delivery_note_number],
]">
    <x-admin.page-header :title="$note->delivery_note_number" :description="$note->customer?->company_name">
        <x-slot name="actions">
            @if ($note->productionJobCard)
                <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $note->productionJobCard, 'tab' => 'dispatch']) }}" class="erp-btn-secondary">{{ __('Job dispatch tab') }}</a>
            @endif
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-4">{{ session('status') }}</x-admin.alert>
    @endif

    <div class="mb-6 grid gap-4 lg:grid-cols-3">
        <x-admin.card>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Status') }}</dt><dd><x-admin.enum-status-badge :status="$note->status->value" /></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Delivery date') }}</dt><dd>{{ $note->delivery_date->format('M j, Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Ready to invoice') }}</dt><dd>{{ $note->invoice_ready ? __('Yes') : __('No') }}</dd></div>
                @if ($note->activeInvoice)
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Invoice') }}</dt>
                        <dd><a href="{{ route('admin.invoices.show', $note->activeInvoice) }}" class="font-mono text-indigo-600">{{ $note->activeInvoice->invoice_number }}</a></dd>
                    </div>
                @elseif ($note->invoice_ready && ! ($invoiceEligibility['eligible'] ?? false))
                    <p class="text-xs text-amber-700">{{ __('Delivery is complete and billable, but no invoice is linked to this delivery note yet.') }}</p>
                @endif
            </dl>
        </x-admin.card>
        <x-admin.card>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Dispatched') }}</dt><dd>{{ $note->dispatched_at?->format('M j, Y H:i') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Delivered') }}</dt><dd>{{ $note->delivered_at?->format('M j, Y H:i') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Recipient') }}</dt><dd>{{ $note->recipient_name ?? '—' }}</dd></div>
            </dl>
        </x-admin.card>
        <x-admin.card>
            <h3 class="mb-2 text-sm font-semibold">{{ __('Actions') }}</h3>
            <div class="flex flex-wrap gap-2">
                @can('dispatch', $note)
                    <form method="POST" action="{{ route('admin.dispatch.delivery-notes.dispatch', $note) }}">
                        @csrf
                        <x-primary-button type="submit">{{ __('Dispatch') }}</x-primary-button>
                    </form>
                @endcan
                @can('deliver', $note)
                    <form method="POST" action="{{ route('admin.dispatch.delivery-notes.deliver', $note) }}" class="flex flex-col gap-2">
                        @csrf
                        <input type="text" name="recipient_name" class="erp-input text-sm" placeholder="{{ __('Recipient name') }}" value="{{ $note->recipient_name }}">
                        <x-primary-button type="submit">{{ __('Confirm delivery') }}</x-primary-button>
                    </form>
                @endcan
                @can('cancel', $note)
                    <form method="POST" action="{{ route('admin.dispatch.delivery-notes.cancel', $note) }}">
                        @csrf
                        <input type="hidden" name="reason" value="{{ __('Cancelled by user') }}">
                        <x-danger-button type="submit">{{ __('Cancel') }}</x-danger-button>
                    </form>
                @endcan
                @can('create', App\Models\Sales\CustomerInvoice::class)
                    @if (($invoiceEligibility['eligible'] ?? false))
                        <form method="POST" action="{{ route('admin.dispatch.delivery-notes.generate-invoice', $note) }}">
                            @csrf
                            <x-primary-button type="submit">{{ __('Generate invoice') }}</x-primary-button>
                        </form>
                    @endif
                @endcan
            </div>
            @if (! empty($invoiceEligibility['warnings']))
                <ul class="mt-2 list-disc ps-5 text-xs text-amber-800">
                    @foreach ($invoiceEligibility['warnings'] as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            @endif
            @if ($salesOrderInvoices->isNotEmpty())
                <div class="mt-3 border-t border-slate-100 pt-3">
                    <p class="mb-1 text-xs font-medium text-slate-600">{{ __('Sales order invoices') }}</p>
                    <ul class="space-y-1 text-xs">
                        @foreach ($salesOrderInvoices as $soInvoice)
                            <li>
                                <a href="{{ route('admin.invoices.show', $soInvoice) }}" class="font-mono text-indigo-600 hover:underline">{{ $soInvoice->invoice_number }}</a>
                                @if ((int) $soInvoice->delivery_note_id === (int) $note->id)
                                    <span class="text-slate-500">({{ __('this delivery') }})</span>
                                @elseif ($soInvoice->delivery_note_id)
                                    <span class="text-slate-500">({{ __('other delivery') }})</span>
                                @else
                                    <span class="text-amber-700">({{ __('from order — not linked here') }})</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (! empty($dispatchReadiness['blockers'] ?? []))
                <ul class="mt-2 list-disc ps-5 text-xs text-amber-800">
                    @foreach ($dispatchReadiness['blockers'] as $blocker)
                        <li>{{ $blocker }}</li>
                    @endforeach
                    @if ($note->productionJobCard)
                        <li>
                            <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $note->productionJobCard, 'tab' => 'outputs']) }}" class="font-medium text-erp-primary hover:underline">
                                {{ __('Open job → Finished goods') }}
                            </a>
                        </li>
                    @endif
                </ul>
            @endif
            @if (! empty($invoiceEligibility['blockers']))
                <ul class="mt-2 list-disc ps-5 text-xs text-red-700">
                    @foreach ($invoiceEligibility['blockers'] as $blocker)
                        <li>{{ $blocker }}</li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>
    </div>

    @if (! empty($partialDelivery['is_partial']))
        <x-admin.card class="mb-6">
            <h3 class="mb-2 text-sm font-semibold">{{ __('Partial delivery') }}</h3>
            <p class="mb-2 text-xs text-slate-600">{{ __('This delivery note does not cover the full sales order quantity.') }}</p>
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">{{ __('Line') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('Ordered') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('On this DN') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('Remaining') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($partialDelivery['lines'] ?? [] as $row)
                        @if ($row['remaining'] > 0)
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2">#{{ $row['sales_order_item_id'] }}</td>
                                <td class="px-3 py-2 text-right font-mono">{{ $row['ordered'] }}</td>
                                <td class="px-3 py-2 text-right font-mono">{{ $row['delivered_on_note'] }}</td>
                                <td class="px-3 py-2 text-right font-mono">{{ $row['remaining'] }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </x-admin.card>
    @endif

    <x-admin.card class="mb-6">
        <h3 class="mb-3 text-sm font-semibold">{{ __('Inventory impact') }}</h3>
        <dl class="mb-4 grid gap-2 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500">{{ __('FG source') }}</dt><dd>{{ $inventoryImpact['finished_goods_warehouse']?->name ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Transit location') }}</dt><dd>{{ $inventoryImpact['transit_warehouse']?->name ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Total inventory cost') }}</dt><dd class="tabular-nums">{{ number_format($inventoryImpact['total_cost'] ?? 0, 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Accounting posted') }}</dt><dd>{{ $inventoryImpact['posted_journal'] ? __('Yes') : __('No') }}</dd></div>
        </dl>
        @if ($inventoryImpact['posted_journal'] ?? null)
            <p class="mb-3 text-sm">{{ __('Journal') }}: <span class="font-mono">{{ $inventoryImpact['posted_journal']->reference ?? $inventoryImpact['posted_journal']->journal_number }}</span></p>
        @endif
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-2 text-left">{{ __('Item') }}</th>
                    <th class="px-3 py-2 text-right">{{ __('Qty') }}</th>
                    <th class="px-3 py-2 text-right">{{ __('Unit cost') }}</th>
                    <th class="px-3 py-2 text-right">{{ __('Total') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('Transit status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($inventoryImpact['lines'] ?? [] as $row)
                    @php $line = $row['line']; @endphp
                    <tr>
                        <td class="px-3 py-2">{{ $line->inventoryItem?->sku ?? $line->description }}</td>
                        <td class="px-3 py-2 text-right font-mono">{{ $line->quantity }}</td>
                        <td class="px-3 py-2 text-right font-mono">{{ number_format((float) ($line->unit_cost ?? 0), 4) }}</td>
                        <td class="px-3 py-2 text-right font-mono">{{ number_format((float) ($line->total_cost ?? 0), 2) }}</td>
                        <td class="px-3 py-2">
                            @if ($row['delivered'] ?? false)
                                {{ __('Delivered / COGS') }}
                            @elseif ($row['dispatched'] ?? false)
                                {{ __('In transit') }}
                            @else
                                {{ __('Pending dispatch') }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>

    <x-admin.card>
        <h3 class="mb-3 text-sm font-semibold">{{ __('Line items') }}</h3>
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-2 text-left">{{ __('Description') }}</th>
                    <th class="px-3 py-2 text-right">{{ __('Qty') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('Unit') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($note->items as $item)
                    <tr>
                        <td class="px-3 py-2">{{ $item->description }}</td>
                        <td class="px-3 py-2 text-right font-mono">{{ $item->quantity }}</td>
                        <td class="px-3 py-2">{{ $item->unit }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
