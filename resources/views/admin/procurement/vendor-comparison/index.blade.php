<x-admin-layout :title="__('Vendor Comparison')">
    <x-admin.page-header
        :title="__('Vendor Comparison')"
        :description="__('Compare supplier quotations side by side, score responses, and award RFQs.')"
    />

    <x-admin.card>
        <table class="erp-table text-sm">
            <thead>
                <tr>
                    <th>{{ __('RFQ') }}</th>
                    <th>{{ __('Purchase Request') }}</th>
                    <th>{{ __('Closing Date') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Recommended Supplier') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rfqs as $rfq)
                    <tr>
                        <td>{{ $rfq->rfq_number }}</td>
                        <td>{{ $rfq->purchaseRequest?->request_number ?? '—' }}</td>
                        <td>{{ $rfq->closing_date?->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ str($rfq->status->value)->headline() }}</td>
                        <td>{{ $rfq->comparison?->recommendedVendor?->vendor_name ?? $rfq->awardedVendor?->vendor_name ?? '—' }}</td>
                        <td>
                            <a href="{{ route('admin.procurement.vendor-comparison.show', $rfq) }}" class="erp-link">{{ __('Open workspace') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-slate-500">{{ __('No RFQs are ready for vendor comparison yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $rfqs->links() }}</div>
    </x-admin.card>
</x-admin-layout>
