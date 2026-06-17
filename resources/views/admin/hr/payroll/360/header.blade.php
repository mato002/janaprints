<x-admin.page-header :title="$run->reference" :description="$overview['branch'].' · '.$overview['period_start']?->format('M j, Y').' – '.$overview['period_end']?->format('M j, Y')">
    <x-slot name="actions">
        <a href="{{ route('admin.hr.payroll.index') }}" class="erp-btn-secondary">{{ __('All runs') }}</a>
        <span class="erp-badge erp-badge--{{ $run->status?->badgeClass() }}">{{ $run->status?->label() }}</span>
    </x-slot>
</x-admin.page-header>

@if (count($quick_actions) > 0)
    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($quick_actions as $action)
            @if (in_array($action['route'], ['admin.hr.payroll.approve', 'admin.hr.payroll.post', 'admin.hr.payroll.mark-paid', 'admin.hr.payroll.release-payslips'], true))
                @can('approve', $run)
                    @include('admin.hr.payroll.360.partials.quick-action', ['action' => $action, 'run' => $run])
                @endcan
            @else
                @can('process', $run)
                    @include('admin.hr.payroll.360.partials.quick-action', ['action' => $action, 'run' => $run])
                @endcan
            @endif
        @endforeach

        @can('export', App\Models\Hr\PayrollRun::class)
            @if ($run->payslips->isNotEmpty())
                <x-admin.export-dropdown
                    export-route="admin.hr.payroll.export"
                    :export-route-params="['payrollRun' => $run]"
                />
            @endif
        @endcan
    </div>
@endif
