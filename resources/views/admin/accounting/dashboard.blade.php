<x-admin-layout :title="__('Accounting Dashboard')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Dashboard')]]">
    <x-admin.page-header
        :title="__('Accounting Dashboard')"
        :description="__('Finance command center — KPIs, collections, payables, and period close alerts.')"
    />

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.accounting.dashboard')" :reset-url="route('admin.accounting.dashboard')">
            @if ($dashboard['filter_options']['companies']->count() > 1)
                <select name="company_id" class="erp-toolbar-select" aria-label="{{ __('Company') }}">
                    @foreach ($dashboard['filter_options']['companies'] as $company)
                        <option value="{{ $company->id }}" @selected($dashboard['filters']['company_id'] == $company->id)>{{ $company->name }}</option>
                    @endforeach
                </select>
            @endif
            <select name="branch_id" class="erp-toolbar-select" aria-label="{{ __('Branch') }}">
                <option value="">{{ __('All branches') }}</option>
                @foreach ($dashboard['filter_options']['branches'] as $branch)
                    <option value="{{ $branch->id }}" @selected($dashboard['filters']['branch_id'] == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
            <select name="period_id" class="erp-toolbar-select" aria-label="{{ __('Fiscal Period') }}">
                @foreach ($dashboard['filter_options']['periods'] as $period)
                    <option value="{{ $period->id }}" @selected($dashboard['filters']['period_id'] == $period->id)>
                        {{ $period->code }} — {{ $period->name }}
                        @if ($period->is_current) ({{ __('current') }}) @endif
                    </option>
                @endforeach
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>
    <p class="mb-4 text-[11px] text-slate-500">
        {{ $dashboard['context']['company'] }} · {{ $dashboard['context']['branch'] }} · {{ $dashboard['context']['period'] }}
        · {{ __('As of') }} {{ $dashboard['context']['as_of_date'] }}
    </p>

    <div class="erp-kpi-grid">
        @foreach ($dashboard['cards'] as $card)
            <x-admin.kpi-widget
                :label="$card['label']"
                :value="$card['value']"
                :icon="$card['icon']"
            />
        @endforeach
    </div>

    <x-admin.card class="mt-4">
        <x-admin.quick-actions :items="[
            ['label' => __('New Journal'), 'route' => 'admin.accounting.journals.create', 'permission' => 'accounting.journals.create'],
            ['label' => __('New Invoice'), 'route' => 'admin.invoices.index', 'permission' => 'invoices.view'],
            ['label' => __('Record Payment'), 'route' => 'admin.payments.create', 'permission' => 'payments.create'],
            ['label' => __('New Supplier Bill'), 'route' => 'admin.payables.bills.create', 'permission' => 'payables.bills.create'],
        ]" />
    </x-admin.card>

    <div class="mt-4 grid grid-cols-1 gap-3 xl:grid-cols-2">
        <x-admin.card>
            <h3 class="text-sm font-medium text-erp-primary mb-2">{{ __('Recent Journals') }}</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase text-slate-400 border-b border-erp-border">
                        <th class="py-1">{{ __('Journal') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-right">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dashboard['widgets']['recent_journals'] as $journal)
                        <tr class="border-t border-erp-border">
                            <td class="py-2">
                                @can('viewAny', App\Models\Accounting\Journal::class)
                                    <a href="{{ route('admin.accounting.journals.show', $journal['id']) }}" class="text-erp-accent font-mono text-xs">{{ $journal['journal_number'] }}</a>
                                @else
                                    <span class="font-mono text-xs">{{ $journal['journal_number'] }}</span>
                                @endcan
                            </td>
                            <td>{{ $journal['journal_date'] }}</td>
                            <td>{{ $journal['status_label'] }}</td>
                            <td class="text-right">{{ number_format($journal['amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-4 text-slate-500">{{ __('No journals yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-sm font-medium text-erp-primary mb-2">{{ __('Recent Invoices') }}</h3>
            <ul class="divide-y divide-erp-border text-sm">
                @forelse ($dashboard['widgets']['recent_invoices'] as $row)
                    <li class="py-2 flex justify-between gap-2">
                        <div>
                            <a href="{{ $row['route'] }}" class="text-erp-accent font-medium">{{ $row['label'] }}</a>
                            <p class="text-[11px] text-slate-500">{{ $row['customer'] }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <div>{{ number_format($row['amount'], 2) }}</div>
                            <div class="text-[11px] text-slate-400">{{ $row['date'] }}</div>
                        </div>
                    </li>
                @empty
                    <li class="py-4 text-slate-500">{{ __('No invoices yet.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-sm font-medium text-erp-primary mb-2">{{ __('Recent Payments') }}</h3>
            <ul class="divide-y divide-erp-border text-sm">
                @forelse ($dashboard['widgets']['recent_payments'] as $row)
                    <li class="py-2 flex justify-between gap-2">
                        <div>
                            <a href="{{ $row['route'] }}" class="text-erp-accent font-medium">{{ $row['label'] }}</a>
                            <p class="text-[11px] text-slate-500">{{ $row['customer'] }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <div>{{ number_format($row['amount'], 2) }}</div>
                            <div class="text-[11px] text-slate-400">{{ $row['date'] }}</div>
                        </div>
                    </li>
                @empty
                    <li class="py-4 text-slate-500">{{ __('No payments yet.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-sm font-medium text-erp-primary mb-2">{{ __('Overdue Receivables') }}</h3>
            <ul class="divide-y divide-erp-border text-sm">
                @forelse ($dashboard['widgets']['overdue_receivables'] as $row)
                    <li class="py-2 flex justify-between">
                        <span>{{ $row['name'] }}</span>
                        <span class="font-mono">{{ number_format($row['amount'], 2) }}</span>
                    </li>
                @empty
                    <li class="py-4 text-slate-500">{{ __('No overdue customer balances.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-sm font-medium text-erp-primary mb-2">{{ __('Overdue Payables') }}</h3>
            <ul class="divide-y divide-erp-border text-sm">
                @forelse ($dashboard['widgets']['overdue_payables'] as $row)
                    <li class="py-2 flex justify-between">
                        <span>{{ $row['name'] }}</span>
                        <span class="font-mono">{{ number_format($row['amount'], 2) }}</span>
                    </li>
                @empty
                    <li class="py-4 text-slate-500">{{ __('No overdue supplier balances.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-sm font-medium text-erp-primary mb-2">{{ __('Period Closing Alerts') }}</h3>
            <ul class="divide-y divide-erp-border text-sm">
                @forelse ($dashboard['widgets']['period_closing_alerts'] as $alert)
                    <li class="py-2">
                        <a href="{{ $alert['route'] }}" class="text-erp-accent font-medium">{{ $alert['label'] }}</a>
                        <p class="text-[11px] text-slate-500">{{ $alert['description'] }}</p>
                    </li>
                @empty
                    <li class="py-4 text-slate-500">{{ __('No closing actions required.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    </div>
</x-admin-layout>
