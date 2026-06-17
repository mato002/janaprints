<x-admin-layout :title="$salesOrder->order_number" :breadcrumbs="[['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.dashboard')], ['label' => $salesOrder->order_number]]">
    <x-admin.page-header :title="$salesOrder->order_number" :description="$salesOrder->customer?->company_name">
        <span class="erp-badge">{{ str_replace('_', ' ', $salesOrder->status->value) }}</span>
        @can('update', $salesOrder)
            <a href="{{ route('admin.sales-orders.edit', $salesOrder) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
        @endcan
    </x-admin.page-header>

    <div class="workspace-kpi-grid grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
        <x-admin.kpi-widget :label="__('Subtotal')" :value="number_format($salesOrder->subtotal, 2)" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Tax')" :value="number_format($salesOrder->tax_amount, 2)" icon="receipt-tax" />
        <x-admin.kpi-widget :label="__('Total')" :value="number_format($salesOrder->total_amount, 2)" icon="calculator" />
    </div>

    <x-admin.card class="mb-6">
        <h3 class="font-medium mb-3">{{ __('Workflow') }}</h3>
        <div class="workspace-action-bar flex flex-wrap gap-2">
            @can('confirm', $salesOrder)
                <form method="POST" action="{{ route('admin.sales-orders.confirm', $salesOrder) }}">@csrf
                    <button class="erp-btn-primary">{{ __('Confirm') }}</button></form>
            @endcan
            @can('production', $salesOrder)
                @if ($salesOrder->status->canTransitionTo(App\Enums\SalesOrderStatus::ReadyForProduction))
                    <form method="POST" action="{{ route('admin.sales-orders.ready-for-production', $salesOrder) }}">@csrf
                        <button class="erp-btn-secondary">{{ __('Ready for production') }}</button></form>
                @endif
                @if ($salesOrder->status->canTransitionTo(App\Enums\SalesOrderStatus::InProduction))
                    <form method="POST" action="{{ route('admin.sales-orders.start-production', $salesOrder) }}">@csrf
                        <button class="erp-btn-secondary">{{ __('Start production') }}</button></form>
                @endif
                @if ($salesOrder->status->canTransitionTo(App\Enums\SalesOrderStatus::Completed))
                    <form method="POST" action="{{ route('admin.sales-orders.complete', $salesOrder) }}">@csrf
                        <button class="erp-btn-secondary">{{ __('Complete') }}</button></form>
                @endif
                @if ($salesOrder->status->canTransitionTo(App\Enums\SalesOrderStatus::Delivered))
                    <form method="POST" action="{{ route('admin.sales-orders.deliver', $salesOrder) }}">@csrf
                        <button class="erp-btn-secondary">{{ __('Delivered') }}</button></form>
                @endif
            @endcan
            @can('close', $salesOrder)
                <form method="POST" action="{{ route('admin.sales-orders.close', $salesOrder) }}">@csrf
                    <button class="erp-btn-primary">{{ __('Close order') }}</button></form>
            @endcan
            @can('transition', $salesOrder)
                @if ($salesOrder->status->canTransitionTo(App\Enums\SalesOrderStatus::OnHold))
                    <form method="POST" action="{{ route('admin.sales-orders.hold', $salesOrder) }}">@csrf
                        <button class="erp-btn-secondary">{{ __('On hold') }}</button></form>
                @endif
                @if ($salesOrder->status === App\Enums\SalesOrderStatus::OnHold)
                    <form method="POST" action="{{ route('admin.sales-orders.resume', $salesOrder) }}">@csrf
                        <button class="erp-btn-secondary">{{ __('Resume') }}</button></form>
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
                    <a href="{{ route('admin.invoices.from-sales-order', $salesOrder) }}" class="erp-btn-primary">{{ __('Create invoice') }}</a>
                @endif
            @endcan
        </div>
        <dl class="workspace-meta-grid text-sm grid sm:grid-cols-3 gap-3">
            <div><dt class="text-slate-500">{{ __('Order total') }}</dt><dd class="font-mono">{{ number_format($salesOrder->total_amount, 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Invoiced') }}</dt><dd class="font-mono">{{ number_format($salesOrder->invoiced_total, 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Remaining') }}</dt><dd class="font-mono">{{ number_format($salesOrder->remainingInvoiceTotal(), 2) }}</dd></div>
        </dl>
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
            <h3 class="font-medium mb-3">{{ __('Line items') }}</h3>
            @foreach ($salesOrder->items as $item)
                <div class="text-sm border-b py-2 flex justify-between">
                    <span>{{ $item->item_name }} × {{ $item->quantity }}</span>
                    <span>{{ number_format($item->line_total, 2) }}</span>
                </div>
            @endforeach
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
