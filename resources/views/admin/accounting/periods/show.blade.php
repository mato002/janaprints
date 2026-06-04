<x-admin-layout :title="$fiscalYear->name" :breadcrumbs="[['label' => __('Accounting Periods'), 'url' => route('admin.accounting.periods.index')], ['label' => $fiscalYear->name]]">
    <x-admin.page-header :title="$fiscalYear->name" :description="$fiscalYear->code.' · '.$fiscalYear->start_date->format('Y-m-d').' → '.$fiscalYear->end_date->format('Y-m-d')">
        <x-admin.status-badge :variant="match($fiscalYear->status) {
            App\Enums\FiscalYearStatus::Open => 'success',
            App\Enums\FiscalYearStatus::YearEndPreparation => 'warning',
            App\Enums\FiscalYearStatus::Closed => 'neutral',
            App\Enums\FiscalYearStatus::Locked => 'danger',
        }">{{ $fiscalYear->status->label() }}</x-admin.status-badge>
    </x-admin.page-header>

    <x-admin.card class="mb-4">
        <h3 class="mb-3 text-sm font-semibold">{{ __('Fiscal year controls') }}</h3>
        <div class="flex flex-wrap gap-2">
            @can('yearEndPrep', $fiscalYear)
                @if ($fiscalYear->status === App\Enums\FiscalYearStatus::Open)
                    <form method="POST" action="{{ route('admin.accounting.periods.fiscal-years.year-end-prep', $fiscalYear) }}">@csrf
                        <button type="submit" class="erp-btn-secondary">{{ __('Year-end preparation') }}</button>
                    </form>
                @endif
            @endcan
            @can('close', $fiscalYear)
                @if (in_array($fiscalYear->status, [App\Enums\FiscalYearStatus::Open, App\Enums\FiscalYearStatus::YearEndPreparation], true))
                    <form method="POST" action="{{ route('admin.accounting.periods.fiscal-years.close', $fiscalYear) }}">@csrf
                        <button type="submit" class="erp-btn-primary">{{ __('Close fiscal year') }}</button>
                    </form>
                @endif
            @endcan
            @can('lock', $fiscalYear)
                @if ($fiscalYear->status === App\Enums\FiscalYearStatus::Closed)
                    <form method="POST" action="{{ route('admin.accounting.periods.fiscal-years.lock', $fiscalYear) }}">@csrf
                        <button type="submit" class="erp-btn-secondary">{{ __('Lock fiscal year') }}</button>
                    </form>
                @endif
            @endcan
            @can('reopen', $fiscalYear)
                @if (in_array($fiscalYear->status, [App\Enums\FiscalYearStatus::Closed, App\Enums\FiscalYearStatus::YearEndPreparation], true))
                    <form method="POST" action="{{ route('admin.accounting.periods.fiscal-years.reopen', $fiscalYear) }}">@csrf
                        <button type="submit" class="erp-btn-secondary">{{ __('Reopen fiscal year') }}</button>
                    </form>
                @endif
            @endcan
        </div>
        @if ($fiscalYear->year_end_prep_at)
            <p class="mt-2 text-[11px] text-slate-500">{{ __('Year-end prep') }}: {{ $fiscalYear->year_end_prep_at->format('Y-m-d H:i') }} — {{ $fiscalYear->yearEndPrepByUser?->name }}</p>
        @endif
        @if ($fiscalYear->notes)
            <p class="mt-2 text-sm text-slate-600">{{ $fiscalYear->notes }}</p>
        @endif
    </x-admin.card>

    @if ($closeAudits->isNotEmpty())
        <x-admin.card class="mb-4">
            <h3 class="mb-3 text-sm font-semibold">{{ __('Close audit trail') }}</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-erp-border text-left text-[11px] uppercase text-slate-500">
                        <th class="pb-2">{{ __('Date') }}</th>
                        <th class="pb-2">{{ __('Type') }}</th>
                        <th class="pb-2">{{ __('Period') }}</th>
                        <th class="pb-2">{{ __('Net amount') }}</th>
                        <th class="pb-2">{{ __('Journal') }}</th>
                        <th class="pb-2">{{ __('By') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($closeAudits as $audit)
                        <tr class="border-t border-erp-border">
                            <td class="py-2">{{ $audit->performed_at->format('Y-m-d H:i') }}</td>
                            <td class="py-2">{{ $audit->close_type->label() }}</td>
                            <td class="py-2">{{ $audit->accountingPeriod?->code ?? '—' }}</td>
                            <td class="py-2 font-mono">{{ number_format($audit->net_amount, 2) }}</td>
                            <td class="py-2">
                                @if ($audit->journal_id)
                                    <a href="{{ route('admin.accounting.journals.show', $audit->journal_id) }}" class="text-erp-accent">{{ $audit->journal?->journal_number }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="py-2">{{ $audit->performedByUser?->name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-admin.card>
    @endif

    <x-admin.data-table :search-placeholder="__('Search periods…')">
        <x-slot name="head">
            <tr>
                <th scope="col">#</th>
                <th scope="col">{{ __('Period') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Dates') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @foreach ($fiscalYear->periods as $period)
                <tr x-show="rowVisible(@js(strtolower($period->name.' '.$period->code.' '.$period->status->value)))">
                    <td class="text-slate-500">{{ $period->period_number }}</td>
                    <td>
                        <span class="font-medium">{{ $period->name }}</span>
                        <div class="font-mono text-[11px] text-slate-500">{{ $period->code }}
                            @if ($period->is_current)<span class="text-erp-accent">· {{ __('Current') }}</span>@endif
                        </div>
                    </td>
                    <td class="hidden md:table-cell text-sm text-slate-600">{{ $period->start_date->format('Y-m-d') }} → {{ $period->end_date->format('Y-m-d') }}</td>
                    <td>
                        <x-admin.status-badge :variant="match($period->status) {
                            App\Enums\AccountingPeriodStatus::Open => 'success',
                            App\Enums\AccountingPeriodStatus::Closed => 'neutral',
                            App\Enums\AccountingPeriodStatus::Locked => 'danger',
                        }">{{ $period->status->label() }}</x-admin.status-badge>
                    </td>
                    <td class="erp-table-actions-col">
                        <div class="flex flex-wrap justify-end gap-1">
                            @can('setCurrent', $period)
                                @if ($period->status === App\Enums\AccountingPeriodStatus::Open && ! $period->is_current)
                                    <form method="POST" action="{{ route('admin.accounting.periods.set-current', $period) }}">@csrf
                                        <button type="submit" class="text-[11px] text-erp-accent">{{ __('Set current') }}</button>
                                    </form>
                                @endif
                            @endcan
                            @can('close', $period)
                                @if ($period->status === App\Enums\AccountingPeriodStatus::Open)
                                    <form method="POST" action="{{ route('admin.accounting.periods.close', $period) }}">@csrf
                                        <button type="submit" class="text-[11px] text-erp-accent">{{ __('Close') }}</button>
                                    </form>
                                @endif
                            @endcan
                            @can('lock', $period)
                                @if ($period->status === App\Enums\AccountingPeriodStatus::Closed)
                                    <form method="POST" action="{{ route('admin.accounting.periods.lock', $period) }}">@csrf
                                        <button type="submit" class="text-[11px] text-erp-accent">{{ __('Lock') }}</button>
                                    </form>
                                @endif
                            @endcan
                            @can('reopen', $period)
                                @if ($period->status === App\Enums\AccountingPeriodStatus::Closed)
                                    <form method="POST" action="{{ route('admin.accounting.periods.reopen', $period) }}">@csrf
                                        <button type="submit" class="text-[11px] text-slate-600">{{ __('Reopen') }}</button>
                                    </form>
                                @endif
                                @if ($period->status === App\Enums\AccountingPeriodStatus::Locked)
                                    <form method="POST" action="{{ route('admin.accounting.periods.unlock', $period) }}">@csrf
                                        <button type="submit" class="text-[11px] text-slate-600">{{ __('Unlock') }}</button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-slot>
    </x-admin.data-table>
</x-admin-layout>
