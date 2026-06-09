<x-admin-layout :title="__('Allowance Library')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Compensation'), 'url' => route('admin.hr.compensation.dashboard')], ['label' => __('Allowances')]]">
    <x-admin.page-header :title="__('Allowance Library')" :description="__('House, transport, medical, risk, responsibility, and custom allowance definitions.')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.compensation.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    @can('create', App\Models\Hr\EmployeeCompensation::class)
        <form method="POST" action="{{ route('admin.hr.compensation.allowances.store') }}" class="erp-card mb-6 space-y-3">
            @csrf
            <h3 class="font-semibold text-erp-primary">{{ __('New allowance type') }}</h3>
            <div class="grid gap-3 md:grid-cols-4">
                <input type="text" name="code" class="erp-input" placeholder="{{ __('Code') }}" required>
                <input type="text" name="name" class="erp-input" placeholder="{{ __('Name') }}" required>
                <select name="calculation_type" class="erp-input">
                    @foreach ($calculationTypes as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
                <select name="frequency" class="erp-input">
                    @foreach ($frequencies as $freq)
                        <option value="{{ $freq->value }}">{{ $freq->label() }}</option>
                    @endforeach
                </select>
                <input type="number" step="0.01" name="default_amount" class="erp-input" placeholder="{{ __('Default amount') }}" value="0">
                <input type="number" step="0.01" name="percentage_rate" class="erp-input" placeholder="{{ __('Percentage %') }}">
            </div>
            <button type="submit" class="erp-btn-primary">{{ __('Add allowance') }}</button>
        </form>
    @endcan

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
