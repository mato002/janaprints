<x-admin-layout :title="__('Job cards')" :breadcrumbs="[['label' => __('Production'), 'url' => route('admin.production.dashboard')], ['label' => __('Job cards')]]">
    <x-admin.page-header :title="__('Production job cards')">
        @can('create', App\Models\Production\ProductionJobCard::class)
            <a href="{{ route('admin.production.job-cards.create') }}" class="erp-btn-primary">{{ __('New job card') }}</a>
        @endcan
    </x-admin.page-header>

    <x-admin.card>
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th>{{ __('Job card') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Sales order') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Priority') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobCards as $card)
                    <tr>
                        <td>{{ $card->job_card_number }}</td>
                        <td>{{ $card->customer?->company_name }}</td>
                        <td>{{ $card->salesOrder?->order_number }}</td>
                        <td><span class="erp-badge">{{ str_replace('_', ' ', $card->status->value) }}</span></td>
                        <td>{{ $card->priority->value }}</td>
                        <td><a href="{{ route('admin.production.job-cards.show', $card) }}" class="text-indigo-600">{{ __('View') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-slate-500 py-4">{{ __('No job cards yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $jobCards->links() }}</div>
    </x-admin.card>
</x-admin-layout>
