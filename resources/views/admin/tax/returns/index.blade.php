<x-admin-layout :title="__('Tax Returns')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Tax Returns')]]">
    <x-admin.page-header :title="__('Tax Returns')">
        <x-slot name="actions">
            @include('admin.accounting.partials.listing-export-dropdown', ['listing' => 'tax-returns'])
        </x-slot>
    </x-admin.page-header>

    <x-admin.card>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-erp-border text-left text-[11px] uppercase text-slate-400">
                    <th class="py-2">{{ __('Return #') }}</th>
                    <th class="py-2">{{ __('Period') }}</th>
                    <th class="py-2">{{ __('Status') }}</th>
                    <th class="py-2 text-right">{{ __('Net liability') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($returns as $return)
                    <tr class="border-b border-erp-border/50">
                        <td class="py-2"><a href="{{ route('admin.tax.returns.show', $return) }}" class="text-erp-primary hover:underline">{{ $return->return_number }}</a></td>
                        <td class="py-2">{{ $return->taxPeriod?->code }}</td>
                        <td class="py-2">{{ $return->status->value }}</td>
                        <td class="py-2 text-right">{{ number_format($return->net_liability, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
