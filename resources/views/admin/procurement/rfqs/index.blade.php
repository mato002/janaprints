<x-admin-layout :title="__('RFQs')" :breadcrumbs="[['label' => __('Procurement'), 'url' => route('admin.procurement.dashboard')], ['label' => __('RFQs')]]">
    <x-admin.page-header :title="__('Requests For Quotation')" />
    <x-admin.card>
        <table class="erp-table text-sm">
            <thead>
                <tr>
                    <th>{{ __('RFQ #') }}</th>
                    <th>{{ __('Request') }}</th>
                    <th>{{ __('Issue') }}</th>
                    <th>{{ __('Closing') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rfqs as $rfq)
                    <tr>
                        <td>{{ $rfq->rfq_number }}</td>
                        <td>{{ $rfq->purchaseRequest?->request_number ?? '—' }}</td>
                        <td>{{ $rfq->issue_date?->format('Y-m-d') }}</td>
                        <td>{{ $rfq->closing_date?->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ str($rfq->status->value)->headline() }}</td>
                        <td><a href="{{ route('admin.procurement.rfqs.show', $rfq) }}" class="erp-link">{{ __('View') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-slate-500">{{ __('No RFQs yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $rfqs->links() }}</div>
    </x-admin.card>
</x-admin-layout>
