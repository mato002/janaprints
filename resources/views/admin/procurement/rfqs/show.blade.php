<x-admin-layout :title="$rfq->rfq_number" :breadcrumbs="[['label' => __('RFQs'), 'url' => route('admin.procurement.rfqs.index')], ['label' => $rfq->rfq_number]]">
    <x-admin.page-header :title="$rfq->rfq_number" :description="str($rfq->status->value)->headline()">
        <x-slot name="actions">
            @can('manage', $rfq)
                @if ($rfq->status->canIssue())
                    <form method="POST" action="{{ route('admin.procurement.rfqs.issue', $rfq) }}">@csrf<button class="erp-btn-primary">{{ __('Issue RFQ') }}</button></form>
                @endif
                @if ($rfq->status === App\Enums\RfqStatus::Open)
                    <form method="POST" action="{{ route('admin.procurement.rfqs.close', $rfq) }}">@csrf<button class="erp-btn-secondary">{{ __('Close for comparison') }}</button></form>
                @endif
            @endcan
            @can('compare', $rfq)
                <form method="POST" action="{{ route('admin.procurement.rfqs.compare', $rfq) }}">@csrf<button class="erp-btn-secondary">{{ __('Save comparison') }}</button></form>
            @endcan
            @can('convert', $rfq)
                <form method="POST" action="{{ route('admin.procurement.rfqs.convert', $rfq) }}">@csrf<button class="erp-btn-primary">{{ __('Convert to PO') }}</button></form>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card>
        <h3 class="mb-3 text-sm font-semibold">{{ __('Line items') }}</h3>
        <table class="erp-table text-sm">
            <thead><tr><th>{{ __('Description') }}</th><th>{{ __('Qty') }}</th></tr></thead>
            <tbody>
                @foreach ($rfq->items as $item)
                    <tr><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>

    <x-admin.card class="mt-4">
        <h3 class="mb-3 text-sm font-semibold">{{ __('Vendor comparison') }}</h3>
        <table class="erp-table text-sm">
            <thead>
                <tr>
                    <th>{{ __('Vendor') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Lead time') }}</th>
                    <th>{{ __('Score') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($comparison['matrix'] as $row)
                    @php
                        $highlights = $comparison['highlights'];
                        $classes = [];
                        if (($highlights['lowest_price_vendor_id'] ?? null) === $row['vendor_id']) {
                            $classes[] = 'bg-emerald-50';
                        }
                        if (($highlights['best_lead_time_vendor_id'] ?? null) === $row['vendor_id']) {
                            $classes[] = 'ring-1 ring-sky-200';
                        }
                        if (($highlights['best_score_vendor_id'] ?? null) === $row['vendor_id']) {
                            $classes[] = 'font-semibold';
                        }
                    @endphp
                    <tr class="{{ implode(' ', $classes) }}">
                        <td>{{ $row['vendor_name'] }}</td>
                        <td>{{ number_format($row['total_price'], 2) }}</td>
                        <td>{{ $row['avg_lead_time_days'] ?? '—' }} {{ __('days') }}</td>
                        <td>{{ $row['score'] }}</td>
                        <td>
                            @can('award', $rfq)
                                <form method="POST" action="{{ route('admin.procurement.rfqs.award', $rfq) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="vendor_id" value="{{ $row['vendor_id'] }}">
                                    <button class="erp-link">{{ __('Award') }}</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if ($rfq->awardedVendor)
            <p class="mt-3 text-sm text-slate-600">{{ __('Awarded vendor') }}: <strong>{{ $rfq->awardedVendor->vendor_name }}</strong></p>
        @endif
    </x-admin.card>

    @foreach ($rfq->vendors as $rfqVendor)
        @can('manage', $rfq)
            <x-admin.card class="mt-4">
                <h3 class="text-sm font-semibold">{{ __('Record response') }} — {{ $rfqVendor->vendor->vendor_name }}</h3>
                <form method="POST" action="{{ route('admin.procurement.rfqs.respond', [$rfq, $rfqVendor]) }}" class="mt-3 space-y-2">
                    @csrf
                    @foreach ($rfq->items as $index => $item)
                        <div class="grid grid-cols-1 gap-2 border-b border-slate-100 pb-2 sm:grid-cols-4">
                            <div class="sm:col-span-2 text-sm">{{ $item->description }} ({{ $item->quantity }})</div>
                            <input type="hidden" name="lines[{{ $index }}][rfq_item_id]" value="{{ $item->id }}">
                            <input type="number" step="0.01" name="lines[{{ $index }}][quoted_price]" class="erp-input" placeholder="{{ __('Price') }}" required>
                            <input type="number" name="lines[{{ $index }}][lead_time_days]" class="erp-input" placeholder="{{ __('Lead days') }}">
                        </div>
                    @endforeach
                    <x-primary-button>{{ __('Save response') }}</x-primary-button>
                </form>
            </x-admin.card>
        @endcan
    @endforeach
</x-admin-layout>
