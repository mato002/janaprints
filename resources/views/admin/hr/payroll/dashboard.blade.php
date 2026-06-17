<x-admin-layout :title="__('Pay runs')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Pay runs')]]">
    <x-admin.page-header
        :title="__('Pay runs')"
        :description="__('Run payroll for a period, generate payslips, and post to accounts. Set up employee salaries under the Salaries tab first.')"
    >
        <x-slot name="actions">
            @can('create', App\Models\Hr\PayrollRun::class)
                <a href="{{ route('admin.hr.payroll.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('New payroll run') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach ([
            ['label' => __('Awaiting approval'), 'value' => $stats['pending_approval'], 'icon' => 'clock'],
            ['label' => __('Posted this year'), 'value' => $stats['posted_this_year'], 'icon' => 'check-circle'],
            ['label' => __('Last net payroll'), 'value' => number_format($stats['last_net_total'], 2), 'icon' => 'cash'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <x-admin.card class="mt-6" :padding="false">
        <div class="flex items-center justify-between border-b border-erp-border px-4 py-3">
            <h2 class="text-sm font-semibold text-erp-primary">{{ __('Recent payroll runs') }}</h2>
            <a href="{{ route('admin.hr.payroll.index') }}" class="text-sm font-medium text-erp-accent hover:underline">{{ __('View all runs') }}</a>
        </div>

        <x-admin.data-table :searchable="false" :exportable="false">
            <x-slot name="head">
                <tr>
                    <th>{{ __('Reference') }}</th>
                    <th>{{ __('Period') }}</th>
                    <th>{{ __('Pay date') }}</th>
                    <th class="hidden sm:table-cell">{{ __('Employees') }}</th>
                    <th>{{ __('Net pay') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="erp-table-actions-col">{{ __('Actions') }}</th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @forelse ($recentRuns as $run)
                    <tr>
                        <td class="font-mono text-[11px]">{{ $run->reference }}</td>
                        <td class="text-sm">{{ $run->period_start?->format('M j') }} – {{ $run->period_end?->format('M j, Y') }}</td>
                        <td>{{ $run->pay_date?->format('M j, Y') }}</td>
                        <td class="hidden sm:table-cell tabular-nums">{{ $run->employee_count }}</td>
                        <td class="tabular-nums font-medium">{{ number_format($run->net_total, 2) }}</td>
                        <td><span class="erp-badge erp-badge--{{ $run->status?->badgeClass() }}">{{ $run->status?->label() }}</span></td>
                        <td class="erp-table-actions-col">
                            <x-admin.table-row-actions>
                                <x-admin.table-row-action :href="route('admin.hr.payroll.show', $run)">{{ __('Open') }}</x-admin.table-row-action>
                            </x-admin.table-row-actions>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-admin.empty-state
                                icon="cash"
                                :title="__('No payroll runs yet')"
                                :description="__('Start a new payroll run when salaries are set up and the pay period is ready.')"
                            />
                        </td>
                    </tr>
                @endforelse
            </x-slot>
        </x-admin.data-table>
    </x-admin.card>
</x-admin-layout>
