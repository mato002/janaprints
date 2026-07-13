@php
    use App\Support\Navigation\WorkspaceEmbed;
@endphp

<x-admin.card :padding="false" class="mb-4">
    <x-admin.index-toolbar
        :action="route('admin.hr.leave.dashboard')"
        :reset-url="WorkspaceEmbed::url(route('admin.hr.leave.dashboard', WorkspaceEmbed::queryParams(['tab' => 'requests'])))"
        compact
        class="erp-index-toolbar-form--compact"
    >
        <input type="hidden" name="tab" value="requests">
        @if (WorkspaceEmbed::inWorkspaceContext())
            <input type="hidden" name="embedded" value="1">
        @endif

        @can('export', App\Models\Hr\LeaveRequest::class)
            <x-slot name="export">
                <x-admin.export-dropdown
                    :post-action="route('admin.hr.leave.export')"
                    :post-fields="array_merge(['tab' => 'requests'], $filters ?? [])"
                />
            </x-slot>
        @endcan

        <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
            <option value="">{{ __('All statuses') }}</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <select name="employee_id" class="erp-toolbar-select" aria-label="{{ __('Employee') }}">
            <option value="">{{ __('All employees') }}</option>
            @foreach ($formData['employees'] as $employee)
                <option value="{{ $employee->id }}" @selected((int) ($filters['employee_id'] ?? 0) === $employee->id)>{{ $employee->full_name }}</option>
            @endforeach
        </select>
        <select name="leave_type_id" class="erp-toolbar-select" aria-label="{{ __('Leave Type') }}">
            <option value="">{{ __('All types') }}</option>
            @foreach ($formData['leaveTypes'] as $type)
                <option value="{{ $type->id }}" @selected((int) ($filters['leave_type_id'] ?? 0) === $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
        <select name="department_id" class="erp-toolbar-select" aria-label="{{ __('Department') }}">
            <option value="">{{ __('All departments') }}</option>
            @foreach ($formData['departments'] as $department)
                <option value="{{ $department->id }}" @selected((int) ($filters['department_id'] ?? 0) === $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
    </x-admin.index-toolbar>
</x-admin.card>

<x-admin.data-table :search-placeholder="__('Search leave requests…')" :exportable="false" export-filename="leave-requests">
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
                        <x-admin.table-row-action :href="WorkspaceEmbed::url(route('admin.hr.leave.show', $leaveRequest))">{{ __('View') }}</x-admin.table-row-action>
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
