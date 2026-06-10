<x-admin-layout :title="__('Transit inventory')" :breadcrumbs="[
    ['label' => __('Dispatch'), 'url' => route('admin.dispatch.dashboard')],
    ['label' => __('Transit inventory')],
]">
    <x-admin.page-header :title="__('Transit inventory report')" :description="__('Stock currently in the in-transit virtual warehouse awaiting delivery confirmation.')" />

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th>{{ __('Item') }}</th>
                <th>{{ __('Quantity') }}</th>
                <th>{{ __('Days in transit') }}</th>
                <th>{{ __('Aging') }}</th>
                <th>{{ __('Delivery note') }}</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Dispatched') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($rows as $row)
                <tr @class([
                    'bg-red-50' => ($row['aging_bucket'] ?? '') === '30+',
                    'bg-amber-50' => ($row['aging_bucket'] ?? '') === '14-29',
                    'bg-yellow-50' => ($row['aging_bucket'] ?? '') === '7-13',
                ])>
                    <td>{{ $row['item']?->sku }} — {{ $row['item']?->item_name }}</td>
                    <td class="tabular-nums">{{ number_format($row['quantity'], 3) }}</td>
                    <td class="tabular-nums">{{ $row['days_in_transit'] }}</td>
                    <td>{{ $row['aging_bucket'] }}</td>
                    <td>
                        @if ($row['delivery_note'])
                            <a href="{{ route('admin.dispatch.delivery-notes.show', $row['delivery_note']) }}" class="erp-link">{{ $row['delivery_note']->delivery_note_number }}</a>
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $row['customer']?->company_name ?? '—' }}</td>
                    <td>{{ $row['dispatched_at']?->format('Y-m-d') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7"><x-admin.empty-state icon="truck" :title="__('No stock in transit')" /></td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
</x-admin-layout>
