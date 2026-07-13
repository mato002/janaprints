<x-admin-layout :title="$salesOrder->order_number" :breadcrumbs="[['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.dashboard')], ['label' => $salesOrder->order_number]]">
    <x-admin.page-header :title="$salesOrder->order_number" :description="$salesOrder->customer?->company_name">
        <x-slot:actions>
            <span class="erp-badge">{{ str_replace('_', ' ', $salesOrder->status->value) }}</span>
            @if (! empty($financial))
                <span class="erp-badge bg-{{ $financial['financial_status_variant'] === 'success' ? 'emerald' : ($financial['financial_status_variant'] === 'warning' ? 'amber' : 'slate') }}-100">
                    {{ $financial['financial_status_label'] }}
                </span>
            @endif
            @can('update', $salesOrder)
                <a href="{{ route('admin.sales-orders.edit', $salesOrder) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

<div class="workspace-kpi-grid grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
        <x-admin.kpi-widget :label="__('Subtotal')" :value="number_format($salesOrder->subtotal, 2)" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Tax')" :value="number_format($salesOrder->tax_amount, 2)" icon="receipt-tax" />
        <x-admin.kpi-widget :label="__('Total')" :value="number_format($salesOrder->total_amount, 2)" icon="calculator" />
    </div>

    @if (! empty($profitability))
        <x-admin.card class="mb-6">
            <h3 class="font-medium mb-3">{{ __('Estimated profitability') }}</h3>
            <dl class="workspace-meta-grid text-sm grid sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <div><dt class="text-slate-500">{{ __('Revenue') }}</dt><dd class="font-mono">{{ number_format($profitability['revenue'], 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Material cost') }}</dt><dd class="font-mono">{{ number_format($profitability['material_cost'], 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Waste cost') }}</dt><dd class="font-mono">{{ number_format($profitability['wastage_cost'], 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Outsource cost') }}</dt><dd class="font-mono">{{ number_format($profitability['outsource_cost'], 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Estimated profit') }}</dt><dd class="font-mono">{{ number_format($profitability['estimated_profit'], 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Margin') }}</dt><dd>{{ number_format($profitability['estimated_margin_percent'], 1) }}%</dd></div>
            </dl>
        </x-admin.card>
    @endif

    <x-admin.card class="mb-6">
        <h3 class="font-medium mb-3">{{ __('Workflow') }}</h3>
        <x-admin.workflow-error />

        <ol class="mb-4 flex flex-wrap gap-2">
            @foreach ($workflow['pipeline'] as $step)
                <li @class([
                    'rounded-full px-3 py-1 text-xs font-medium',
                    'bg-emerald-100 text-emerald-800' => $step['state'] === 'complete',
                    'bg-erp-accent/10 text-erp-accent' => $step['state'] === 'current',
                    'bg-slate-100 text-slate-500' => in_array($step['state'], ['upcoming', 'paused'], true),
                    'bg-red-100 text-red-700' => $step['state'] === 'cancelled',
                ])>{{ $step['label'] }}</li>
            @endforeach
        </ol>

        @if ($workflow['hint'])
            <p class="mb-3 text-sm text-slate-600">{{ $workflow['hint'] }}</p>
        @endif

        @if ($salesOrder->jobCard)
            <p class="mb-3 text-sm">
                <a href="{{ route('admin.production.job-cards.show', $salesOrder->jobCard) }}" class="text-erp-accent hover:text-erp-accent-hover">
                    {{ __('Open job card :number', ['number' => $salesOrder->jobCard->job_card_number]) }}
                </a>
            </p>
        @endif

        <div class="workspace-action-bar flex flex-wrap gap-2">
            @can('confirm', $salesOrder)
                @if ($workflow['can_confirm'])
                    <form method="POST" action="{{ route('admin.sales-orders.confirm', $salesOrder) }}">@csrf
                        <button class="erp-btn-primary">{{ __('Confirm order') }}</button></form>
                @endif
            @endcan
            @can('production', $salesOrder)
                @if ($workflow['can_release'])
                    <form method="POST" action="{{ route('admin.sales-orders.release-to-production', $salesOrder) }}">@csrf
                        <button class="erp-btn-primary">{{ __('Send to production') }}</button></form>
                @endif
            @endcan
            @can('close', $salesOrder)
                @if ($workflow['can_close'])
                    <form method="POST" action="{{ route('admin.sales-orders.close', $salesOrder) }}">@csrf
                        <button class="erp-btn-primary">{{ __('Close order') }}</button></form>
                @endif
            @endcan
            @can('transition', $salesOrder)
                @if ($salesOrder->status->canTransitionTo(App\Enums\SalesOrderStatus::OnHold))
                    <form method="POST" action="{{ route('admin.sales-orders.hold', $salesOrder) }}">@csrf
                        <button class="erp-btn-secondary">{{ __('On hold') }}</button></form>
                @endif
                @if ($salesOrder->status === App\Enums\SalesOrderStatus::OnHold)
                    <form method="POST" action="{{ route('admin.sales-orders.resume', $salesOrder) }}">@csrf
                        <button class="erp-btn-primary">{{ __('Resume') }}</button></form>
                @endif
                @if ($salesOrder->status->canTransitionTo(App\Enums\SalesOrderStatus::Cancelled))
                    <form method="POST" action="{{ route('admin.sales-orders.cancel', $salesOrder) }}">@csrf
                        <button class="erp-btn-secondary text-red-600">{{ __('Cancel') }}</button></form>
                @endif
            @endcan
        </div>
    </x-admin.card>

    <x-admin.card class="mb-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
            <h3 class="font-medium">{{ __('Invoicing') }}</h3>
            @can('create', App\Models\Sales\CustomerInvoice::class)
                @if ($salesOrder->remainingInvoiceTotal() > 0 && !in_array($salesOrder->status, [App\Enums\SalesOrderStatus::Draft, App\Enums\SalesOrderStatus::Cancelled]))
                    <a href="{{ route('admin.invoices.from-sales-order', $salesOrder) }}" class="erp-btn-primary" data-turbo-frame="_top">{{ __('Create invoice') }}</a>
                @endif
            @endcan
        </div>
        @if (! empty($financial['billing_eligibility']['blockers']))
            <p class="mb-3 text-sm text-amber-700">{{ implode(' ', $financial['billing_eligibility']['blockers']) }}</p>
            <p class="mb-3 text-xs text-amber-700">{{ __('Use deposit or progress billing on the next screen if you need to invoice before fulfilment is complete.') }}</p>
        @endif
        <dl class="workspace-meta-grid text-sm grid sm:grid-cols-3 gap-3 mb-4">
            <div><dt class="text-slate-500">{{ __('Order total') }}</dt><dd class="font-mono">{{ number_format($salesOrder->total_amount, 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Invoiced') }}</dt><dd class="font-mono">{{ number_format($salesOrder->invoiced_total, 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Remaining') }}</dt><dd class="font-mono">{{ number_format($salesOrder->remainingInvoiceTotal(), 2) }}</dd></div>
        </dl>
        @if (! empty($financial['payment']))
            <dl class="workspace-meta-grid text-sm grid sm:grid-cols-4 gap-3 mb-4 border-t border-erp-border pt-3">
                <div><dt class="text-slate-500">{{ __('Payment status') }}</dt><dd>{{ $financial['payment']['label'] }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Paid') }}</dt><dd class="font-mono">{{ number_format($financial['payment']['amount_paid'], 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Outstanding') }}</dt><dd class="font-mono">{{ number_format($financial['payment']['amount_outstanding'], 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Fulfilment ready') }}</dt><dd>{{ ($financial['billing_eligibility']['fulfilment_ready'] ?? false) ? __('Yes') : __('No') }}</dd></div>
            </dl>
        @endif
        @if (! empty($financial['deposit']))
            <dl class="workspace-meta-grid text-sm grid sm:grid-cols-4 gap-3 border-t border-erp-border pt-3">
                <div><dt class="text-slate-500">{{ __('Billing type') }}</dt><dd>{{ $financial['deposit']['billing_type'] }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Required deposit') }}</dt><dd class="font-mono">{{ number_format($financial['deposit']['required'], 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Deposit invoiced') }}</dt><dd class="font-mono">{{ number_format($financial['deposit']['invoiced'], 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Deposit paid') }}</dt><dd class="font-mono">{{ number_format($financial['deposit']['paid'], 2) }}</dd></div>
            </dl>
        @endif
        @if ($salesOrder->invoices->isNotEmpty())
            <ul class="mt-3 text-sm space-y-1">
                @foreach ($salesOrder->invoices as $inv)
                    <li><a href="{{ route('admin.invoices.show', $inv) }}" class="text-erp-accent font-mono">{{ $inv->invoice_number }}</a> — {{ $inv->status->label() }} ({{ number_format($inv->total_amount, 2) }})</li>
                @endforeach
            </ul>
        @endif
    </x-admin.card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Traceability') }}</h3>
            <dl class="workspace-meta-grid text-sm space-y-2">
                <div><dt class="text-slate-500">{{ __('Quotation') }}</dt><dd>{{ $salesOrder->quotation?->quotation_number }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Artwork') }}</dt><dd>{{ $salesOrder->artworkRequest?->request_number }}</dd></div>
                @if ($salesOrder->jobCard)
                    <div><dt class="text-slate-500">{{ __('Job card') }}</dt>
                        <dd><a href="{{ route('admin.production.job-cards.show', $salesOrder->jobCard) }}" class="text-erp-accent hover:text-erp-accent-hover">{{ $salesOrder->jobCard->job_card_number }}</a></dd></div>
                @endif
                @if ($salesOrder->conversion)
                    <div><dt class="text-slate-500">{{ __('Converted') }}</dt>
                        <dd>{{ $salesOrder->conversion->created_at?->format('Y-m-d H:i') }} — {{ $salesOrder->conversion->converter?->name }}
                            ({{ __('Quotation rev') }} {{ $salesOrder->conversion->quotation_revision_number }},
                            {{ __('Artwork v') }}{{ $salesOrder->conversion->artwork_version_number }})</dd></div>
                @endif
            </dl>
        </x-admin.card>

        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Production product') }}</h3>
            @if ($salesOrder->inventoryItem)
                <p class="mb-3 text-sm">
                    <span class="font-medium">{{ $salesOrder->inventoryItem->item_name }}</span>
                    <span class="text-slate-500">({{ $salesOrder->inventoryItem->sku }})</span>
                </p>
            @else
                <p class="mb-3 text-sm text-amber-700">{{ __('No catalogue product linked yet. Link a finished-good inventory item so production and material requirements can run.') }}</p>
            @endif
            @can('updateProductionSetup', $salesOrder)
                <form method="POST" action="{{ route('admin.sales-orders.production-setup.update', $salesOrder) }}" class="flex flex-wrap items-end gap-2">
                    @csrf
                    @method('PATCH')
                    <div class="min-w-[16rem] flex-1">
                        <label class="erp-label">{{ __('Catalogue item') }}</label>
                        <select name="inventory_item_id" class="erp-input w-full" required>
                            <option value="">{{ __('Select product') }}</option>
                            @foreach ($catalogueItems as $item)
                                <option value="{{ $item->id }}" @selected($salesOrder->inventory_item_id == $item->id)>
                                    {{ $item->item_name }} ({{ $item->sku }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="erp-btn-secondary">{{ $salesOrder->inventoryItem ? __('Update product') : __('Link product') }}</button>
                </form>
            @endcan
        </x-admin.card>

        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Line items & production specifications') }}</h3>
            @forelse ($salesOrder->items as $item)
                @include('admin.sales.orders.partials.item-specification', [
                    'salesOrder' => $salesOrder,
                    'item' => $item,
                    'itemSpecifications' => $itemSpecifications ?? [],
                ])
            @empty
                <p class="text-sm text-slate-500">{{ __('No line items.') }}</p>
            @endforelse
        </x-admin.card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Notes') }}</h3>
            @foreach ($salesOrder->orderNotes as $note)
                <div class="text-sm py-1">{{ $note->user?->name }}: {{ $note->note }}</div>
            @endforeach
            @can('view', $salesOrder)
                <form method="POST" action="{{ route('admin.sales-orders.notes.store', $salesOrder) }}" class="mt-4 space-y-2">
                    @csrf
                    <textarea name="note" class="erp-input w-full" rows="2" required></textarea>
                    <button class="erp-btn-secondary">{{ __('Add note') }}</button>
                </form>
            @endcan
        </x-admin.card>

        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Attachments') }}</h3>
            @foreach ($salesOrder->attachments as $attachment)
                <div class="text-sm py-1">{{ $attachment->original_name }}</div>
            @endforeach
            @can('view', $salesOrder)
                <form method="POST" action="{{ route('admin.sales-orders.attachments.store', $salesOrder) }}" enctype="multipart/form-data" data-turbo-frame="_top" class="mt-4">
                    @csrf
                    <input type="file" name="file" class="erp-input w-full" required>
                    <button class="erp-btn-secondary mt-2">{{ __('Upload') }}</button>
                </form>
            @endcan
        </x-admin.card>
    </div>
</x-admin-layout>
