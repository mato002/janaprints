<x-admin-layout :title="__('Leave Requests')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Leave'), 'url' => route('admin.hr.leave.dashboard')], ['label' => __('Requests')]]">
    <x-admin.page-header :title="__('Leave Requests')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.leave.dashboard') }}" data-turbo-frame="erp-main" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
            @can('create', App\Models\Hr\LeaveRequest::class)
                <a href="{{ route('admin.hr.leave.create') }}" data-turbo-frame="erp-main" class="erp-btn-primary">{{ __('Apply for leave') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <form method="GET" class="erp-card mb-4">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label class="erp-label">{{ __('Status') }}</label>
                <select name="status" class="erp-input w-full text-sm">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Employee') }}</label>
                <select name="employee_id" class="erp-input w-full text-sm">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($formData['employees'] as $employee)
                        <option value="{{ $employee->id }}" @selected((int) ($filters['employee_id'] ?? 0) === $employee->id)>{{ $employee->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Leave Type') }}</label>
                <select name="leave_type_id" class="erp-input w-full text-sm">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($formData['leaveTypes'] as $type)
                        <option value="{{ $type->id }}" @selected((int) ($filters['leave_type_id'] ?? 0) === $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Department') }}</label>
                <select name="department_id" class="erp-input w-full text-sm">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($formData['departments'] as $department)
                        <option value="{{ $department->id }}" @selected((int) ($filters['department_id'] ?? 0) === $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-3 flex gap-2">
            <button type="submit" class="erp-btn-primary">{{ __('Filter') }}</button>
            <a href="{{ route('admin.hr.leave.index') }}" class="erp-btn-secondary">{{ __('Reset') }}</a>
        </div>
    </form>

    @can('export', App\Models\Hr\LeaveRequest::class)
        <div class="mb-4 flex flex-wrap gap-2">
            @foreach (['csv' => 'CSV', 'excel' => 'Excel', 'pdf' => 'PDF'] as $format => $label)
                <form method="POST" action="{{ route('admin.hr.leave.export') }}">
                    @csrf
                    <input type="hidden" name="format" value="{{ $format }}">
                    @foreach ($filters as $key => $value)
                        @if ($value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
                    @endforeach
                    <button type="submit" class="erp-btn-secondary">{{ __('Export :format', ['format' => $label]) }}</button>
                </form>
            @endforeach
        </div>
    @endcan

    <x-admin.data-table :search-placeholder="__('Search leave requests…')" export-filename="leave-requests">
        <x-slot name="head">
            <tr>
                <th>{{ __('Reference') }}</th>
                <th>{{ __('Employee') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Period') }}</th>
                <th>{{ __('Days') }}</th>
                <th>{{ __('Status') }}</th>
                <th class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($requests as $leaveRequest)
                <tr x-show="rowVisible(@js(strtolower(implode(' ', [$leaveRequest->reference, $leaveRequest->employee?->full_name, $leaveRequest->leaveType?->name]))))">
                    <td class="font-mono text-[11px]">{{ $leaveRequest->reference ?? '—' }}</td>
                    <td class="font-medium">{{ $leaveRequest->employee?->full_name }}</td>
                    <td>{{ $leaveRequest->leaveType?->name }}</td>
                    <td class="text-sm">{{ $leaveRequest->start_date?->format('M j') }} – {{ $leaveRequest->end_date?->format('M j, Y') }}</td>
                    <td class="tabular-nums">{{ $leaveRequest->days_requested }}</td>
                    <td>
                        <span class="erp-badge erp-badge--{{ $leaveRequest->status?->badgeClass() }}">{{ $leaveRequest->status?->label() }}</span>
                    </td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.hr.leave.show', $leaveRequest)">{{ __('View') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><x-admin.empty-state icon="calendar" :title="__('No leave requests')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer">
            <x-admin.table-pagination :paginator="$requests" />
        </x-slot>
    </x-admin.data-table>
</x-admin-layout>
