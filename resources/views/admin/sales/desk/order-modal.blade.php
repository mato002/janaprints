@php
    $printSpec = $salesOrder->customerPrintSpecification;
    $firstItem = $salesOrder->items->first();
    $artwork = $salesOrder->customerArtwork
        ?? $printSpec?->activeArtworkVersion;
    $productLabel = $printSpec?->productLabel()
        ?? $salesOrder->inventoryItem?->item_name
        ?? $firstItem?->item_name;
    $jobSpecification = $jobSpecification ?? ['has_specification' => false, 'sections' => []];
    $financial = app(\App\Support\Sales\SalesOrderFinancialStatusService::class)->snapshot($salesOrder);
@endphp

<x-admin.modal-form :title="$salesOrder->order_number" maxWidth="5xl">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center gap-2">
            <span class="erp-badge">{{ str_replace('_', ' ', $salesOrder->status->value) }}</span>
            <span class="text-sm font-medium text-slate-800">{{ $salesOrder->customer?->company_name ?? '—' }}</span>
            @if ($salesOrder->production_destination)
                <span class="erp-badge">{{ $salesOrder->production_destination->label() }}</span>
            @endif
            @if ($salesOrder->priority)
                <span class="text-xs text-slate-500">{{ $salesOrder->priority->label() }}</span>
            @endif
        </div>

        <dl class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
            <div>
                <dt class="text-slate-500">{{ __('Order date') }}</dt>
                <dd>{{ $salesOrder->order_date?->format('d M Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('Required') }}</dt>
                <dd>{{ $salesOrder->required_date?->format('d M Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('Priority') }}</dt>
                <dd>{{ $salesOrder->priority?->label() ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('Fulfilment') }}</dt>
                <dd>{{ $salesOrder->fulfilment_method?->label() ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('Billing') }}</dt>
                <dd>{{ $salesOrder->billing_type?->label() ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('Payment status') }}</dt>
                <dd class="font-medium">{{ $financial['financial_status_label'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('Subtotal') }}</dt>
                <dd class="font-mono">{{ number_format((float) $salesOrder->subtotal, 2) }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('Total') }}</dt>
                <dd class="font-mono font-medium">{{ number_format((float) $salesOrder->total_amount, 2) }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('Job') }}</dt>
                <dd>{{ $salesOrder->jobCard?->job_card_number ?? '—' }}</dd>
            </div>
        </dl>

        <div class="rounded-lg border border-erp-border bg-white p-4">
            <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Print specification') }}</h3>
            <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-slate-500">{{ __('Specification') }}</dt>
                    <dd class="font-medium">
                        {{ $printSpec?->name ?? $firstItem?->specification_name ?? '—' }}
                        @if ($printSpec?->specification_code || $firstItem?->specification_code)
                            <span class="block font-mono text-xs font-normal text-slate-500">
                                {{ $printSpec?->specification_code ?? $firstItem?->specification_code }}
                            </span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Product') }}</dt>
                    <dd>{{ $productLabel ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Goes to') }}</dt>
                    <dd>{{ $salesOrder->production_destination?->label() ?? $printSpec?->production_destination?->label() ?? __('Not set') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Artwork') }}</dt>
                    <dd>
                        @if ($artwork)
                            {{ $artwork->artwork_name ?? $artwork->original_file_name ?? __('Artwork') }}
                            <span class="text-slate-500">({{ $artwork->versionLabel() }})</span>
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>

            @if ($salesOrder->items->isNotEmpty())
                <div class="mt-4 border-t border-slate-100 pt-3">
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Order lines') }}</h4>
                    @foreach ($salesOrder->items as $item)
                        <div class="border-b border-slate-100 py-2 text-sm last:border-0">
                            <div class="flex flex-wrap items-baseline justify-between gap-2">
                                <span class="font-medium">{{ $item->item_name }}</span>
                                <span class="font-mono text-slate-600">
                                    {{ number_format((float) $item->quantity, 2) }} × {{ number_format((float) $item->unit_price, 2) }}
                                    = {{ number_format((float) $item->line_total, 2) }}
                                </span>
                            </div>
                            @if ($item->description)
                                <p class="mt-0.5 text-xs text-slate-500">{{ $item->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <h3 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Job details') }}</h3>
            <p class="mb-3 text-xs text-slate-500">{{ __('The Digital, Offset, or Outsource details captured when this order was created.') }}</p>
            @include('admin.production.specifications.partials.read-only-display', [
                'specification' => $jobSpecification,
                'hideApprovalStatus' => true,
            ])
        </div>

        @if ($salesOrder->notes || $firstItem?->production_notes_snapshot || $firstItem?->customer_instructions_snapshot || $printSpec?->production_notes)
            <div class="rounded-lg border border-erp-border bg-white p-4">
                <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Notes') }}</h3>
                <dl class="space-y-3 text-sm">
                    @if ($salesOrder->notes)
                        <div>
                            <dt class="text-slate-500">{{ __('Order notes') }}</dt>
                            <dd class="whitespace-pre-wrap">{{ $salesOrder->notes }}</dd>
                        </div>
                    @endif
                    @if ($firstItem?->customer_instructions_snapshot || $printSpec?->customer_instructions)
                        <div>
                            <dt class="text-slate-500">{{ __('Customer instructions') }}</dt>
                            <dd class="whitespace-pre-wrap">{{ $firstItem?->customer_instructions_snapshot ?? $printSpec?->customer_instructions }}</dd>
                        </div>
                    @endif
                    @if ($firstItem?->production_notes_snapshot || $printSpec?->production_notes)
                        <div>
                            <dt class="text-slate-500">{{ __('Production notes') }}</dt>
                            <dd class="whitespace-pre-wrap">{{ $firstItem?->production_notes_snapshot ?? $printSpec?->production_notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        @endif

        <div class="flex flex-wrap gap-2">
            <a
                href="{{ route('admin.sales-orders.specifications.print', $salesOrder) }}"
                class="erp-btn-secondary text-sm"
                target="_blank"
                rel="noopener"
                data-turbo="false"
            >{{ __('Print specifications') }}</a>
            @if (request('from') === 'sales-desk')
                @can('update', $salesOrder)
                    <a href="{{ route('admin.sales-orders.edit', [$salesOrder, 'from' => 'sales-desk']) }}" class="erp-btn-secondary text-sm" data-erp-modal-open>{{ __('Edit order') }}</a>
                @endcan
                @can('create', App\Models\Sales\CustomerPayment::class)
                    <a href="{{ route('admin.payments.create', ['from' => 'sales-desk', 'customer_id' => $salesOrder->customer_id, 'sales_order_id' => $salesOrder->id]) }}" class="erp-btn-secondary text-sm" data-erp-modal-open>{{ __('Record payment') }}</a>
                @endcan
                @can('create', App\Models\Sales\CustomerInvoice::class)
                    <a href="{{ route('admin.invoices.from-sales-order', [$salesOrder, 'from' => 'sales-desk']) }}" class="erp-btn-secondary text-sm" data-erp-modal-open>{{ __('Create invoice') }}</a>
                @endcan
            @elseif (request('from') === 'production-floor')
                <a href="{{ route('admin.sales-orders.show', $salesOrder) }}" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main" data-turbo-action="advance">{{ __('Open full order') }}</a>
            @endif
        </div>
    </div>
</x-admin.modal-form>
