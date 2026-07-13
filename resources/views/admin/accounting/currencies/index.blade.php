<x-admin-layout :title="__('Currencies')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Currencies')]]">
    <x-admin.page-header :title="__('Currencies')" :description="__('Base currency: :code', ['code' => $baseCurrency])">
        <x-slot name="actions">
            @can('accounting.currencies.view')
                <a href="{{ route('admin.accounting.currencies.rates.index') }}" class="erp-btn-secondary">{{ __('Exchange rates') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-500 border-b border-erp-border">
                    <th class="py-2">{{ __('Code') }}</th>
                    <th class="py-2">{{ __('Name') }}</th>
                    <th class="py-2">{{ __('Symbol') }}</th>
                    <th class="py-2">{{ __('Decimals') }}</th>
                    <th class="py-2">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($currencies as $currency)
                    <tr class="border-t border-erp-border">
                        <td class="py-2 font-mono font-medium">
                            {{ $currency->code }}
                            @if ($currency->code === $baseCurrency)
                                <span class="text-xs text-erp-accent">· {{ __('Base') }}</span>
                            @endif
                        </td>
                        <td class="py-2">{{ $currency->name }}</td>
                        <td class="py-2">{{ $currency->symbol }}</td>
                        <td class="py-2">{{ $currency->decimal_places }}</td>
                        <td class="py-2">
                            <x-admin.status-badge :variant="$currency->is_active ? 'success' : 'neutral'">
                                {{ $currency->is_active ? __('Active') : __('Inactive') }}
                            </x-admin.status-badge>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
