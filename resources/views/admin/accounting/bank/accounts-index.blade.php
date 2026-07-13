<x-admin-layout :title="__('Bank Accounts')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Bank Accounts')]]">
    <x-admin.page-header :title="__('Bank Accounts')" :description="__('GL-linked bank and cash accounts for reconciliation')">
        <x-slot name="actions">
            @can('accounting.bank.manage')
                <a href="{{ route('admin.accounting.bank.accounts.create') }}" class="erp-btn-primary">{{ __('Add bank account') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-500 border-b border-erp-border">
                    <th class="py-2">{{ __('Name') }}</th>
                    <th class="py-2">{{ __('GL account') }}</th>
                    <th class="py-2 hidden sm:table-cell">{{ __('Account number') }}</th>
                    <th class="py-2">{{ __('Currency') }}</th>
                    <th class="py-2">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($accounts as $account)
                    <tr class="border-t border-erp-border">
                        <td class="py-2 font-medium">{{ $account->name }}</td>
                        <td class="py-2 font-mono text-xs">{{ $account->glAccount?->code }} — {{ $account->glAccount?->name }}</td>
                        <td class="py-2 hidden sm:table-cell">{{ $account->account_number ?: '—' }}</td>
                        <td class="py-2">{{ $account->currency_code }}</td>
                        <td class="py-2">
                            <x-admin.status-badge :variant="$account->is_active ? 'success' : 'neutral'">
                                {{ $account->is_active ? __('Active') : __('Inactive') }}
                            </x-admin.status-badge>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6">
                            <x-admin.empty-state icon="cash" :title="__('No bank accounts')" :description="__('Link a GL cash or bank account to start reconciliation.')" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
