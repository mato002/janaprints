<x-admin-layout :title="__('Salary Templates')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Compensation'), 'url' => route('admin.hr.compensation.dashboard')], ['label' => __('Templates')]]">
    <x-admin.page-header :title="__('Salary Templates')" :description="__('Reusable pay structures for common roles and grades.')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.compensation.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    @can('create', App\Models\Hr\EmployeeCompensation::class)
        <form method="POST" action="{{ route('admin.hr.compensation.templates.store') }}" class="erp-card mb-6 space-y-3">
            @csrf
            <h3 class="font-semibold text-erp-primary">{{ __('New template') }}</h3>
            <div class="grid gap-3 md:grid-cols-3">
                <input type="text" name="code" class="erp-input" placeholder="{{ __('Code') }}" required>
                <input type="text" name="name" class="erp-input" placeholder="{{ __('Name') }}" required>
                <input type="number" step="0.01" name="basic_salary" class="erp-input" placeholder="{{ __('Basic salary') }}" required>
                <input type="number" step="0.01" name="house_allowance" class="erp-input" placeholder="{{ __('House') }}" value="0">
                <input type="number" step="0.01" name="transport_allowance" class="erp-input" placeholder="{{ __('Transport') }}" value="0">
                <input type="number" step="0.01" name="medical_allowance" class="erp-input" placeholder="{{ __('Medical') }}" value="0">
            </div>
            <button type="submit" class="erp-btn-primary">{{ __('Create template') }}</button>
        </form>
    @endcan

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th>{{ __('Template') }}</th>
                <th>{{ __('Basic') }}</th>
                <th>{{ __('Gross') }}</th>
                <th>{{ __('Group') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($templates as $template)
                <tr>
                    <td>
                        <div class="font-medium">{{ $template->name }}</div>
                        <div class="text-xs text-slate-500">{{ $template->code }}</div>
                    </td>
                    <td>{{ number_format($template->basic_salary, 2) }}</td>
                    <td>{{ number_format($template->grossComponents(), 2) }}</td>
                    <td>{{ $template->payroll_group?->label() }}</td>
                </tr>
            @empty
                <tr><td colspan="4"><x-admin.empty-state :title="__('No templates yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$templates" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
