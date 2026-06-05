<x-admin-layout :title="$rfq->rfq_number">
    <x-admin.page-header
        :title="__('Vendor Comparison — :rfq', ['rfq' => $rfq->rfq_number])"
        :description="str($rfq->status->value)->headline()"
    >
        <x-slot name="actions">
            @can('compare', $rfq)
                <form method="POST" action="{{ route('admin.procurement.vendor-comparison.compare', $rfq) }}" class="inline">
                    @csrf
                    @foreach ($weights as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <button type="submit" class="erp-btn-secondary">{{ __('Save comparison') }}</button>
                </form>
            @endcan
            @if ($rfq->purchaseOrder)
                <a href="{{ route('admin.procurement.orders.show', $rfq->purchaseOrder) }}" class="erp-btn-primary">{{ __('View PO') }}</a>
            @endif
        </x-slot>
    </x-admin.page-header>

    <x-admin.card class="mb-4">
        <h3 class="mb-3 text-sm font-semibold">{{ __('RFQ requirements') }}</h3>
        <div class="mb-4 grid gap-3 text-sm sm:grid-cols-3">
            <div><span class="text-slate-500">{{ __('RFQ') }}:</span> {{ $workspace['rfq']['rfq_number'] }}</div>
            <div><span class="text-slate-500">{{ __('Required date') }}:</span> {{ $workspace['rfq']['required_date'] }}</div>
            <div><span class="text-slate-500">{{ __('Purchase request') }}:</span> {{ $workspace['rfq']['purchase_request_number'] ?? '—' }}</div>
        </div>
        <table class="erp-table text-sm">
            <thead>
                <tr>
                    <th>{{ __('Requested item') }}</th>
                    <th>{{ __('Required quantity') }}</th>
                    <th>{{ __('Required date') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($workspace['items'] as $item)
                    <tr>
                        <td>{{ $item['inventory_item'] ?? $item['description'] }}</td>
                        <td>{{ number_format($item['quantity'], 2) }}</td>
                        <td>{{ $item['required_date'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>

    @can('manageComparison', $rfq)
        <x-admin.card class="mb-4">
            <h3 class="mb-3 text-sm font-semibold">{{ __('Scoring weights') }}</h3>
            <form method="GET" action="{{ route('admin.procurement.vendor-comparison.show', $rfq) }}" class="grid gap-3 sm:grid-cols-5">
                @foreach (['price' => __('Price'), 'performance' => __('Performance'), 'lead_time' => __('Lead time'), 'quality' => __('Quality')] as $key => $label)
                    <div>
                        <label class="text-[11px] text-slate-500" for="{{ $key }}">{{ $label }} %</label>
                        <input type="number" id="{{ $key }}" name="{{ $key }}" value="{{ $weights[$key] ?? 0 }}" min="0" max="100" class="erp-input mt-1 w-full">
                    </div>
                @endforeach
                <div class="flex items-end">
                    <button type="submit" class="erp-btn-secondary w-full">{{ __('Recalculate') }}</button>
                </div>
            </form>
        </x-admin.card>
    @endcan

    <x-admin.card>
        <h3 class="mb-3 text-sm font-semibold">{{ __('Supplier comparison grid') }}</h3>
        <div class="overflow-x-auto">
            <table class="erp-table text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Supplier') }}</th>
                        <th>{{ __('Quoted price') }}</th>
                        <th>{{ __('Total cost') }}</th>
                        <th>{{ __('Lead time') }}</th>
                        <th>{{ __('Payment terms') }}</th>
                        <th>{{ __('Delivery terms') }}</th>
                        <th>{{ __('Warranty') }}</th>
                        <th>{{ __('Supplier rating') }}</th>
                        <th>{{ __('Historical performance') }}</th>
                        <th>{{ __('Score') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($workspace['matrix'] as $row)
                        @php
                            $highlights = $workspace['highlights'];
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
                            <td>
                                {{ $row['vendor_name'] }}
                                <div class="text-[11px] text-slate-500">{{ str($row['invitation_status'])->headline() }}</div>
                            </td>
                            <td>{{ number_format($row['quoted_price'], 2) }}</td>
                            <td>{{ number_format($row['total_cost'], 2) }}</td>
                            <td>{{ $row['avg_lead_time_days'] ?? '—' }} {{ __('days') }}</td>
                            <td>{{ $row['payment_terms'] }}</td>
                            <td>{{ $row['delivery_terms'] }}</td>
                            <td>{{ $row['warranty'] }}</td>
                            <td>{{ $row['supplier_rating'] ?? '—' }}</td>
                            <td>{{ isset($row['historical_performance']) ? $row['historical_performance'].'%' : '—' }}</td>
                            <td>{{ $row['score'] }}</td>
                            <td class="space-y-1">
                                @can('award', $rfq)
                                    <form method="POST" action="{{ route('admin.procurement.vendor-comparison.award', $rfq) }}">
                                        @csrf
                                        <input type="hidden" name="vendor_id" value="{{ $row['vendor_id'] }}">
                                        <input type="hidden" name="auto_po" value="1">
                                        <button class="erp-link">{{ __('Award') }}</button>
                                    </form>
                                @endcan
                                @can('manageComparison', $rfq)
                                    <form method="POST" action="{{ route('admin.procurement.vendor-comparison.reject', [$rfq, $row['rfq_vendor_id']]) }}">
                                        @csrf
                                        <button class="erp-link text-red-600">{{ __('Reject quote') }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.procurement.vendor-comparison.requote', [$rfq, $row['rfq_vendor_id']]) }}">
                                        @csrf
                                        <button class="erp-link">{{ __('Request requote') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.card>

    @can('award', $rfq)
        <x-admin.card class="mt-4">
            <h3 class="mb-3 text-sm font-semibold">{{ __('Award partial quantity') }}</h3>
            <form method="POST" action="{{ route('admin.procurement.vendor-comparison.award-partial', $rfq) }}" class="space-y-3">
                @csrf
                <input type="hidden" name="auto_po" value="1">
                <div>
                    <label class="text-[11px] text-slate-500" for="partial_vendor_id">{{ __('Supplier') }}</label>
                    <select id="partial_vendor_id" name="vendor_id" class="erp-input mt-1 w-full max-w-md" required>
                        @foreach ($workspace['matrix'] as $row)
                            <option value="{{ $row['vendor_id'] }}">{{ $row['vendor_name'] }}</option>
                        @endforeach
                    </select>
                </div>
                @foreach ($workspace['items'] as $index => $item)
                    <div class="grid gap-2 sm:grid-cols-3">
                        <div class="sm:col-span-2 text-sm">{{ $item['description'] }} ({{ __('max') }} {{ number_format($item['quantity'], 2) }})</div>
                        <input type="hidden" name="lines[{{ $index }}][rfq_item_id]" value="{{ $item['id'] }}">
                        <input type="number" step="0.001" name="lines[{{ $index }}][quantity]" class="erp-input" placeholder="{{ __('Award qty') }}">
                    </div>
                @endforeach
                <x-primary-button>{{ __('Award partial quantity') }}</x-primary-button>
            </form>
        </x-admin.card>

        @if (count($workspace['items']) > 0 && count($workspace['matrix']) > 1)
            <x-admin.card class="mt-4">
                <h3 class="mb-3 text-sm font-semibold">{{ __('Split award') }}</h3>
                <form method="POST" action="{{ route('admin.procurement.vendor-comparison.split-award', $rfq) }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="auto_po" value="1">
                    @foreach ($workspace['items'] as $itemIndex => $item)
                        <div class="rounded-lg border border-erp-border p-3">
                            <p class="mb-2 text-sm font-medium">{{ $item['description'] }} — {{ number_format($item['quantity'], 2) }}</p>
                            @foreach ($workspace['matrix'] as $vendorIndex => $row)
                                <div class="mb-2 grid gap-2 sm:grid-cols-3">
                                    <div class="text-sm">{{ $row['vendor_name'] }}</div>
                                    <input type="hidden" name="allocations[{{ $itemIndex }}_{{ $vendorIndex }}][vendor_id]" value="{{ $row['vendor_id'] }}">
                                    <input type="hidden" name="allocations[{{ $itemIndex }}_{{ $vendorIndex }}][rfq_item_id]" value="{{ $item['id'] }}">
                                    <input type="number" step="0.001" name="allocations[{{ $itemIndex }}_{{ $vendorIndex }}][quantity]" class="erp-input" placeholder="{{ __('Qty') }}">
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                    <x-primary-button>{{ __('Split award') }}</x-primary-button>
                </form>
            </x-admin.card>
        @endif
    @endcan

    @if ($rfq->awardedVendor)
        <p class="mt-4 text-sm text-slate-600">
            {{ __('Awarded supplier') }}: <strong>{{ $rfq->awardedVendor->vendor_name }}</strong>
            @if ($rfq->award_type)
                · {{ str($rfq->award_type)->headline() }}
            @endif
        </p>
    @endif
</x-admin-layout>
