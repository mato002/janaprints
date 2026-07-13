<x-admin-layout :title="__('Bank Reconciliation')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Bank Reconciliation')]]">
    <x-admin.page-header :title="__('Bank Reconciliation')" :description="__('Match statement lines to posted GL bank movements')">
        <x-slot name="actions">
            @can('accounting.bank.manage')
                <a href="{{ route('admin.accounting.bank.reconciliation.create') }}" class="erp-btn-primary">{{ __('New statement') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-500 border-b border-erp-border">
                    <th class="py-2">{{ __('Date') }}</th>
                    <th class="py-2">{{ __('Bank account') }}</th>
                    <th class="py-2 text-right">{{ __('Closing') }}</th>
                    <th class="py-2">{{ __('Status') }}</th>
                    <th class="py-2 erp-table-actions-col">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($statements as $statement)
                    <tr class="border-t border-erp-border">
                        <td class="py-2">{{ $statement->statement_date->format('Y-m-d') }}</td>
                        <td class="py-2">{{ $statement->bankAccount?->name }}</td>
                        <td class="py-2 text-right">{{ number_format((float) $statement->closing_balance, 2) }}</td>
                        <td class="py-2">
                            <x-admin.status-badge :variant="match($statement->status) {
                                App\Enums\BankStatementStatus::Reconciled => 'success',
                                App\Enums\BankStatementStatus::InProgress => 'warning',
                                default => 'neutral',
                            }">{{ $statement->status->label() }}</x-admin.status-badge>
                        </td>
                        <td class="py-2">
                            <a href="{{ route('admin.accounting.bank.reconciliation.show', $statement) }}" class="text-erp-accent hover:text-erp-accent-hover">{{ __('Open') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6">
                            <x-admin.empty-state icon="scale" :title="__('No statements yet')" :description="__('Import a bank statement to begin matching.')" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $statements->links() }}</div>
    </x-admin.card>
</x-admin-layout>
