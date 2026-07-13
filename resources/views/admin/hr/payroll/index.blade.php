<x-admin-layout :title="__('Payroll Runs')" :breadcrumbs="[['label' => __('Payroll'), 'url' => route('admin.hr.payroll.dashboard')], ['label' => __('Runs')]]">
    <x-admin.page-header :title="__('Payroll Runs')">
        <x-slot name="actions">
            @can('create', App\Models\Hr\PayrollRun::class)
                <a href="{{ route('admin.hr.payroll.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('New run') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

<x-admin.data-table export-filename="payroll-runs">
        <x-slot name="head">
            <tr>
                <th>{{ __('Reference') }}</th>
                <th>{{ __('Period') }}</th>
                <th>{{ __('Pay Date') }}</th>
                <th>{{ __('Employees') }}</th>
                <th>{{ __('Gross') }}</th>
                <th>{{ __('Net') }}</th>
                <th>{{ __('Status') }}</th>
                <th class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($runs as $run)
                <tr>
                    <td class="font-mono text-[11px]">{{ $run->reference }}</td>
                    <td class="text-sm">{{ $run->period_start?->format('M j') }} – {{ $run->period_end?->format('M j, Y') }}</td>
                    <td>{{ $run->pay_date?->format('Y-m-d') }}</td>
                    <td class="tabular-nums">{{ $run->employee_count }}</td>
                    <td class="tabular-nums">{{ number_format($run->gross_total, 2) }}</td>
                    <td class="tabular-nums font-medium">{{ number_format($run->net_total, 2) }}</td>
                    <td><span class="erp-badge erp-badge--{{ $run->status?->badgeClass() }}">{{ $run->status?->label() }}</span></td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.hr.payroll.show', $run)">{{ __('View') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8"><x-admin.empty-state icon="cash" :title="__('No payroll runs yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer">
            <x-admin.table-pagination :paginator="$runs" />
        </x-slot>
    </x-admin.data-table>
</x-admin-layout>
