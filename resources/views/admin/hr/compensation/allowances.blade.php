<x-admin-layout :title="__('Allowance Library')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Compensation'), 'url' => route('admin.hr.compensation.dashboard')], ['label' => __('Allowances')]]">
    <x-admin.page-header :title="__('Allowance Library')" :description="__('House, transport, medical, risk, responsibility, and custom allowance definitions.')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.compensation.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
            @can('create', App\Models\Hr\EmployeeCompensation::class)
                <a href="{{ route('admin.hr.compensation.allowances.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Add allowance') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

<x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th>{{ __('Allowance') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Frequency') }}</th>
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
                    <td>{{ $definition->calculation_type?->label() }}</td>
                    <td>{{ $definition->frequency?->label() }}</td>
                    <td>
                        @if ($definition->calculation_type?->value === 'percentage')
                            {{ $definition->percentage_rate }}%
                        @else
                            {{ number_format($definition->default_amount, 2) }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4"><x-admin.empty-state :title="__('No allowance definitions yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$definitions" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
