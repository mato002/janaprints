<x-admin-layout :title="__('Exit Processes')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Exit Management'), 'url' => route('admin.hr.exit.dashboard')], ['label' => __('Processes')]]">
    <x-admin.page-header :title="__('Exit Processes')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.exit.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
            @can('create', App\Models\Hr\EmployeeExit::class)
                <a href="{{ route('admin.hr.exit.create') }}" class="erp-btn-primary">{{ __('Initiate exit') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="url()->current()" :reset-url="url()->current()">
            <select name="employee_id" class="erp-toolbar-select" aria-label="{{ __('Employee') }}">
                <option value="">{{ __('All') }}</option>
                @foreach ($formData['employees'] as $employee)
                    <option value="{{ $employee->id }}" @selected((int) ($filters['employee_id'] ?? 0) === $employee->id)>{{ $employee->full_name }}</option>
                @endforeach
            </select>
            <select name="exit_type" class="erp-toolbar-select" aria-label="{{ __('Exit Type') }}">
                <option value="">{{ __('All') }}</option>
                @foreach ($formData['exitTypes'] as $type)
                    <option value="{{ $type->value }}" @selected(($filters['exit_type'] ?? '') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All') }}</option>
                @foreach ($formData['statuses'] as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.data-table export-filename="employee-exits">
        <x-slot name="head">
            <tr>
                <th>{{ __('Reference') }}</th>
                <th>{{ __('Employee') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Last Day') }}</th>
                <th>{{ __('Net Dues') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($exits as $exit)
                <tr>
                    <td><a href="{{ route('admin.hr.exit.show', $exit) }}" class="font-medium text-indigo-600 hover:underline">{{ $exit->reference }}</a></td>
                    <td>{{ $exit->employee->full_name }}</td>
                    <td>{{ $exit->exit_type->label() }}</td>
                    <td>{{ $exit->last_working_date->format('Y-m-d') }}</td>
                    <td>{{ number_format($exit->net_final_dues, 2) }}</td>
                    <td>{{ $exit->status->label() }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-6 text-center text-slate-500">{{ __('No exit processes found.') }}</td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>

    <div class="mt-4">{{ $exits->links() }}</div>
</x-admin-layout>
