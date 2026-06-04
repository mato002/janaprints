<x-admin-layout :title="__('Tax Periods')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Tax Periods')]]">
    <x-admin.page-header :title="__('Tax Periods')" />

    <x-admin.card>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-erp-border text-left text-[11px] uppercase text-slate-400">
                    <th class="py-2">{{ __('Code') }}</th>
                    <th class="py-2">{{ __('Name') }}</th>
                    <th class="py-2">{{ __('Range') }}</th>
                    <th class="py-2">{{ __('Status') }}</th>
                    @can('manageReturns', \App\Models\Tax\TaxCode::class)
                        <th class="py-2"></th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @foreach ($periods as $period)
                    <tr class="border-b border-erp-border/50">
                        <td class="py-2 font-mono text-xs">{{ $period->code }}</td>
                        <td class="py-2">{{ $period->name }}</td>
                        <td class="py-2">{{ $period->start_date->format('Y-m-d') }} – {{ $period->end_date->format('Y-m-d') }}</td>
                        <td class="py-2">{{ $period->status?->value ?? $period->status }}</td>
                        @can('manageReturns', \App\Models\Tax\TaxCode::class)
                            <td class="py-2 text-right">
                                <form method="POST" action="{{ route('admin.tax.returns.draft', $period) }}">
                                    @csrf
                                    <button class="erp-btn-secondary text-xs">{{ __('Prepare VAT return') }}</button>
                                </form>
                            </td>
                        @endcan
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
