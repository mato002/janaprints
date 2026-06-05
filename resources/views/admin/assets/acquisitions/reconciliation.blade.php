<x-admin-layout :title="__('Capitalization Reconciliation')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Capitalization Reconciliation')]]">
    <x-admin.page-header :title="__('Capitalization Reconciliation')" :description="__('Compare procurement, accounting, and asset register.')">
        <x-slot name="actions">
            <form method="POST" action="{{ route('admin.assets.acquisitions.reconciliation.store') }}">
                @csrf
                <button type="submit" class="erp-btn-primary">{{ __('Run Reconciliation') }}</button>
            </form>
        </x-slot>
    </x-admin.page-header>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Number') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Procurement') }}</th>
                        <th>{{ __('Capitalized') }}</th>
                        <th>{{ __('Posted') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            <td>{{ $record->reconciliation_number }}</td>
                            <td>{{ $record->reconciliation_date?->format('Y-m-d') }}</td>
                            <td>{{ number_format($record->procurement_received_value, 2) }}</td>
                            <td>{{ number_format($record->capitalized_value, 2) }}</td>
                            <td>{{ number_format($record->posted_value, 2) }}</td>
                            <td><x-admin.status-badge :status="$record->status->value" :label="$record->status->label()" /></td>
                            <td><a href="{{ route('admin.assets.acquisitions.reconciliation.show', $record) }}" class="erp-link">{{ __('View') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-slate-500">{{ __('No reconciliation runs yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $records->links() }}</div>
    </x-admin.card>
</x-admin-layout>
