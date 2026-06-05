<x-admin-layout :title="__('Financial Profile')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => $asset->asset_number, 'url' => route('admin.assets.show', $asset)], ['label' => __('Financial Profile')]]">
    <x-admin.page-header :title="$asset->asset_name" :description="__('Financial profile and depreciation history.')" />

    <div class="mb-4 flex gap-2 border-b border-erp-border text-sm">
        <span class="border-b-2 border-erp-primary px-3 py-2 font-medium">{{ __('Acquisition') }}</span>
        <span class="px-3 py-2 text-slate-500">{{ __('Valuation') }}</span>
        <span class="px-3 py-2 text-slate-500">{{ __('Depreciation') }}</span>
        <span class="px-3 py-2 text-slate-500">{{ __('Accounting') }}</span>
        <span class="px-3 py-2 text-slate-500">{{ __('Reconciliation') }}</span>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <x-admin.card class="lg:col-span-2">
            <h3 class="mb-3 font-semibold">{{ __('Financial Summary') }}</h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-slate-500">{{ __('Acquisition Cost') }}</dt><dd>{{ number_format($profile['acquisition_cost'], 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Capitalization Date') }}</dt><dd>{{ $profile['capitalization_date']?->format('Y-m-d') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Residual Value') }}</dt><dd>{{ number_format($profile['residual_value'], 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Useful Life') }}</dt><dd>{{ $profile['useful_life_years'] }} {{ __('years') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Depreciation Method') }}</dt><dd>{{ $profile['depreciation_method']->label() }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Depreciation Start') }}</dt><dd>{{ $profile['depreciation_start_date']?->format('Y-m-d') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Accumulated Depreciation') }}</dt><dd>{{ number_format($profile['accumulated_depreciation'], 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Net Book Value') }}</dt><dd class="font-semibold">{{ number_format($profile['net_book_value'], 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Monthly Depreciation') }}</dt><dd>{{ number_format($profile['monthly_depreciation'], 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Annual Depreciation') }}</dt><dd>{{ number_format($profile['annual_depreciation'], 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Remaining Life') }}</dt><dd>{{ $profile['remaining_months'] }} {{ __('months') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Fully Depreciated') }}</dt><dd>{{ $profile['is_fully_depreciated'] ? __('Yes') : __('No') }}</dd></div>
            </dl>
        </x-admin.card>
        <x-admin.card>
            <h3 class="mb-3 font-semibold">{{ __('Category GL Mapping') }}</h3>
            <dl class="space-y-2 text-sm">
                <div><dt class="text-slate-500">{{ __('Asset Account') }}</dt><dd>{{ $asset->category?->default_gl_code ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Accumulated Depreciation') }}</dt><dd>{{ $asset->category?->accumulated_depreciation_gl_code ?? __('System default') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Depreciation Expense') }}</dt><dd>{{ $asset->category?->depreciation_expense_gl_code ?? __('System default') }}</dd></div>
            </dl>
        </x-admin.card>
    </div>

    @if ($asset->depreciationEntries->isNotEmpty())
        <x-admin.card class="mt-4">
            <h3 class="mb-3 font-semibold">{{ __('Depreciation History') }}</h3>
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Period') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Accumulated') }}</th><th>{{ __('NBV') }}</th><th>{{ __('Journal') }}</th></tr></thead>
                <tbody>
                    @foreach ($asset->depreciationEntries as $entry)
                        <tr>
                            <td>{{ $entry->period_date?->format('Y-m') }}</td>
                            <td>{{ number_format($entry->depreciation_amount, 2) }}</td>
                            <td>{{ number_format($entry->accumulated_after, 2) }}</td>
                            <td>{{ number_format($entry->net_book_value_after, 2) }}</td>
                            <td>{{ $entry->journal?->reference ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-admin.card>
    @endif

    @if ($asset->financeTimelineEntries->isNotEmpty())
        <x-admin.card class="mt-4">
            <h3 class="mb-3 font-semibold">{{ __('Finance Timeline') }}</h3>
            <ul class="space-y-2 text-sm">
                @foreach ($asset->financeTimelineEntries as $entry)
                    <li class="flex justify-between border-b border-erp-border pb-2">
                        <span>{{ $entry->title }} — {{ $entry->user?->name ?? __('System') }}</span>
                        <span class="text-slate-500">{{ $entry->occurred_at?->format('Y-m-d H:i') }}</span>
                    </li>
                @endforeach
            </ul>
        </x-admin.card>
    @endif
</x-admin-layout>
