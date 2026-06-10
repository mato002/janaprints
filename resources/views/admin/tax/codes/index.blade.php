<x-admin-layout :title="__('Tax Codes')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Tax Codes')]]">
    <x-admin.page-header :title="__('Tax Codes')" :description="__('Rates are effective-dated; documents resolve codes via tax rules.')">
        <x-slot name="actions">
            @can('create', \App\Models\Tax\TaxCode::class)
                <a href="{{ route('admin.tax.codes.create') }}" class="erp-btn-primary">{{ __('New tax code') }}</a>
            @endcan
            @include('admin.accounting.partials.listing-export-dropdown', ['listing' => 'tax-codes'])
        </x-slot>
    </x-admin.page-header>

    <x-admin.card>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-erp-border text-left text-[11px] uppercase text-slate-400">
                    <th class="py-2">{{ __('Code') }}</th>
                    <th class="py-2">{{ __('Name') }}</th>
                    <th class="py-2">{{ __('Category') }}</th>
                    <th class="py-2 text-right">{{ __('Current rate') }}</th>
                    <th class="py-2">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($codes as $code)
                    <tr class="border-b border-erp-border/50">
                        <td class="py-2"><a href="{{ route('admin.tax.codes.show', $code) }}" class="font-mono text-xs text-erp-primary hover:underline">{{ $code->code }}</a></td>
                        <td class="py-2">{{ $code->name }}</td>
                        <td class="py-2 text-slate-500">{{ $code->category?->name }} ({{ $code->category?->type?->label() }})</td>
                        <td class="py-2 text-right">{{ $code->rates->first() ? number_format($code->rates->first()->rate_percent, 2).'%' : '—' }}</td>
                        <td class="py-2">{{ $code->is_active ? __('Active') : __('Inactive') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-slate-500">{{ __('No tax codes yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
