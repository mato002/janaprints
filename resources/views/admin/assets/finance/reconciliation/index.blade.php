<x-admin-layout :title="__('Reconciliation')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Reconciliation')]]">
    <x-admin.page-header :title="__('Asset Register Reconciliation')">
        <x-slot name="actions">
            @can('run', \App\Models\Assets\AssetRegisterReconciliation::class)
                <form method="POST" action="{{ route('admin.assets.finance.reconciliation.store') }}">@csrf<button class="erp-btn-primary">{{ __('Run Reconciliation') }}</button></form>
            @endcan
        </x-slot>
    </x-admin.page-header>
    <x-admin.card>
        <table class="erp-table w-full text-sm">
            <thead><tr><th>{{ __('No') }}</th><th>{{ __('Date') }}</th><th>{{ __('Register NBV') }}</th><th>{{ __('GL NBV') }}</th><th>{{ __('Variance') }}</th><th>{{ __('Status') }}</th></tr></thead>
            <tbody>
                @forelse ($reconciliations as $record)
                    <tr>
                        <td><a href="{{ route('admin.assets.finance.reconciliation.show', $record) }}" class="erp-link">{{ $record->reconciliation_no }}</a></td>
                        <td>{{ $record->reconciliation_date?->format('Y-m-d') }}</td>
                        <td>{{ number_format($record->register_nbv, 2) }}</td>
                        <td>{{ number_format($record->gl_nbv, 2) }}</td>
                        <td>{{ number_format($record->variance_nbv, 2) }}</td>
                        <td><x-admin.status-badge :variant="$record->status->badgeVariant()">{{ $record->status->label() }}</x-admin.status-badge></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-slate-500">{{ __('No reconciliations yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($reconciliations->hasPages())<div class="mt-4">{{ $reconciliations->links() }}</div>@endif
    </x-admin.card>
</x-admin-layout>
