<x-admin-layout :title="__('Deduction Library')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Compensation'), 'url' => route('admin.hr.compensation.dashboard')], ['label' => __('Deductions')]]">
    <x-admin.page-header :title="__('Deduction Library')" :description="__('PAYE, NSSF, SHIF, housing levy, advances, loans, and custom deduction definitions.')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.compensation.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    @can('create', App\Models\Hr\EmployeeCompensation::class)
        <form method="POST" action="{{ route('admin.hr.compensation.deductions.store') }}" class="erp-card mb-6 space-y-3">
            @csrf
            <h3 class="font-semibold text-erp-primary">{{ __('New deduction type') }}</h3>
            <div class="grid gap-3 md:grid-cols-4">
                <input type="text" name="code" class="erp-input" placeholder="{{ __('Code') }}" required>
                <input type="text" name="name" class="erp-input" placeholder="{{ __('Name') }}" required>
                <input type="text" name="category" class="erp-input" placeholder="{{ __('Category') }}" value="custom">
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
            </div>
            <button type="submit" class="erp-btn-primary">{{ __('Add deduction') }}</button>
        </form>
    @endcan

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
