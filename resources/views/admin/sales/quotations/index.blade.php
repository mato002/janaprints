<x-admin-layout :title="__('All quotations')" :breadcrumbs="[['label' => __('Quotations'), 'url' => route('admin.quotations.dashboard')], ['label' => __('List')]]">
    <x-admin.page-header :title="__('Quotations')">
        @can('create', App\Models\Sales\Quotation::class)
            <a href="{{ route('admin.quotations.create') }}" class="erp-btn-primary">{{ __('Create') }}</a>
        @endcan
    </x-admin.page-header>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>{{ __('Number') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Rev') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quotations as $quotation)
                        <tr>
                            <td>{{ $quotation->quotation_number }}</td>
                            <td>{{ $quotation->customer?->company_name }}</td>
                            <td>{{ $quotation->quotation_date->format('Y-m-d') }}</td>
                            <td>{{ $quotation->currency }} {{ number_format($quotation->total_amount, 2) }}</td>
                            <td><span class="erp-badge">{{ str_replace('_', ' ', $quotation->status->value) }}</span></td>
                            <td>{{ $quotation->revision_number }}</td>
                            <td><a href="{{ route('admin.quotations.show', $quotation) }}" class="text-erp-accent">{{ __('View') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-slate-500 py-8">{{ __('No quotations.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $quotations->links() }}</div>
    </x-admin.card>
</x-admin-layout>
