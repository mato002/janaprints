<x-admin-layout :title="__('Deduction Library')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Compensation'), 'url' => route('admin.hr.compensation.dashboard')], ['label' => __('Deductions')]]">
    <x-admin.page-header :title="__('Deduction Library')" :description="__('PAYE, NSSF, SHIF, housing levy, advances, loans, and custom deduction definitions.')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.compensation.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
            @can('create', App\Models\Hr\EmployeeCompensation::class)
                <a href="{{ route('admin.hr.compensation.deductions.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Add deduction') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

<x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th>{{ __('Deduction') }}</th>
                <th>{{ __('Category') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Default') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($definitions as $definition)
                <tr>
                    <td>
                        <div class="font-medium">{{ $definition->name }}</div>
                        <div class="text-xs text-slate-500">{{ $definition->code }}</div>
                    </td>
                    <td>{{ strtoupper($definition->category) }}</td>
                    <td>{{ $definition->calculation_type?->label() }}</td>
                    <td>{{ number_format($definition->default_amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4"><x-admin.empty-state :title="__('No deduction definitions yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$definitions" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
