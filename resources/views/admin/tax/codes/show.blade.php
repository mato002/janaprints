<x-admin-layout :title="$taxCode->code" :breadcrumbs="[['label' => __('Tax Codes'), 'url' => route('admin.tax.codes.index')], ['label' => $taxCode->code]]">
    <x-admin.page-header :title="$taxCode->code" :description="$taxCode->name">
        @can('update', $taxCode)
            <a href="{{ route('admin.tax.codes.edit', $taxCode) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
        @endcan
    </x-admin.page-header>

    <div class="mb-4 grid grid-cols-3 gap-3">
        <x-admin.kpi-widget :label="__('Category')" :value="$taxCode->category?->name" />
        <x-admin.kpi-widget :label="__('Type')" :value="$taxCode->category?->type?->label()" />
        <x-admin.kpi-widget :label="__('Status')" :value="$taxCode->is_active ? __('Active') : __('Inactive')" />
    </div>

    <x-admin.card class="mb-4" :title="__('Rate history')">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-erp-border text-left text-[11px] uppercase text-slate-400">
                    <th class="py-2">{{ __('Rate %') }}</th>
                    <th class="py-2">{{ __('Effective from') }}</th>
                    <th class="py-2">{{ __('Effective to') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($taxCode->rates as $rate)
                    <tr class="border-b border-erp-border/50">
                        <td class="py-2">{{ number_format($rate->rate_percent, 4) }}%</td>
                        <td class="py-2">{{ $rate->effective_from->format('Y-m-d') }}</td>
                        <td class="py-2">{{ $rate->effective_to?->format('Y-m-d') ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @can('update', $taxCode)
            <form method="POST" action="{{ route('admin.tax.codes.rates.store', $taxCode) }}" class="mt-4 flex flex-wrap items-end gap-3 border-t border-erp-border pt-4">
                @csrf
                <div><label class="text-[11px] text-slate-500">{{ __('New rate %') }}</label><input type="number" step="0.01" name="rate_percent" class="erp-input mt-1" required></div>
                <div><label class="text-[11px] text-slate-500">{{ __('Effective from') }}</label><input type="date" name="effective_from" value="{{ now()->toDateString() }}" class="erp-input mt-1" required></div>
                <button class="erp-btn-secondary">{{ __('Add rate') }}</button>
            </form>
        @endcan
    </x-admin.card>
</x-admin-layout>
