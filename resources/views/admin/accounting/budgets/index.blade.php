<x-admin-layout :title="__('Budgets')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Budgets')]]">
    <x-admin.page-header :title="__('Budgets')" :description="__('GL budgets and budget vs actual')">
        <x-slot name="actions">
            @can('accounting.budgets.manage')
                <a href="{{ route('admin.accounting.budgets.create') }}" class="erp-btn-primary">{{ __('New budget') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-500 border-b border-erp-border">
                    <th class="py-2">{{ __('Name') }}</th>
                    <th class="py-2">{{ __('Period') }}</th>
                    <th class="py-2">{{ __('Status') }}</th>
                    <th class="py-2 erp-table-actions-col">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($budgets as $budget)
                    <tr class="border-t border-erp-border">
                        <td class="py-2 font-medium">
                            <a href="{{ route('admin.accounting.budgets.show', $budget) }}" class="text-erp-accent hover:text-erp-accent-hover">{{ $budget->name }}</a>
                        </td>
                        <td class="py-2 text-xs">{{ $budget->from_date->format('Y-m-d') }} → {{ $budget->to_date->format('Y-m-d') }}</td>
                        <td class="py-2">
                            <x-admin.status-badge :variant="match($budget->status) {
                                App\Enums\BudgetStatus::Active => 'success',
                                App\Enums\BudgetStatus::Closed => 'neutral',
                                default => 'warning',
                            }">{{ $budget->status->label() }}</x-admin.status-badge>
                        </td>
                        <td class="py-2">
                            <a href="{{ route('admin.accounting.budgets.vs-actual', $budget) }}" class="text-sm text-erp-accent">{{ __('Vs actual') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-6">
                            <x-admin.empty-state icon="chart-bar" :title="__('No budgets')" :description="__('Create a draft budget with GL account lines.')" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $budgets->links() }}</div>
    </x-admin.card>
</x-admin-layout>
