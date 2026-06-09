<x-admin-layout :title="__('Attendance Register')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Attendance'), 'url' => route('admin.hr.attendance.dashboard')], ['label' => __('Register')]]">
    <x-admin.page-header :title="__('Attendance Register')" :description="__('Daily attendance records with hours, overtime, and status.')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.attendance.dashboard', $filters) }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
            @can('create', App\Models\Hr\AttendanceRecord::class)
                <a href="{{ route('admin.hr.attendance.create') }}" class="erp-btn-primary">{{ __('Manual attendance') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    @include('admin.hr.attendance.partials.filters', [
        'filters' => $filters,
        'formData' => $formData,
        'statuses' => $statuses,
        'action' => route('admin.hr.attendance.index'),
        'exportAction' => route('admin.hr.attendance.export'),
        'canExport' => auth()->user()?->can('export', App\Models\Hr\AttendanceRecord::class) ?? false,
    ])

    <x-admin.data-table :search-placeholder="__('Search attendance…')" export-filename="attendance-register" :exportable="false">
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Employee') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Employee Number') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Department') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Branch') }}</th>
                <th scope="col" class="hidden xl:table-cell">{{ __('Shift') }}</th>
                <th scope="col">{{ __('Clock In') }}</th>
                <th scope="col">{{ __('Clock Out') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Hours') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Overtime') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($records as $record)
                @php
                    $searchText = strtolower(implode(' ', array_filter([
                        $record->employee?->full_name,
                        $record->employee?->employee_number,
                        $record->department?->name,
                        $record->branch?->name,
                        $record->shift?->name,
                        $record->status?->label(),
                    ])));
                @endphp
                <tr x-show="rowVisible(@js($searchText))">
                    <td>
                        <div class="font-medium text-erp-primary">{{ $record->employee?->full_name }}</div>
                        <div class="text-[11px] text-slate-500">{{ $record->attendance_date?->format('Y-m-d') }}</div>
                    </td>
                    <td class="hidden md:table-cell font-mono text-[11px]">{{ $record->employee?->employee_number }}</td>
                    <td class="hidden lg:table-cell">{{ $record->department?->name ?? '—' }}</td>
                    <td class="hidden lg:table-cell">{{ $record->branch?->name ?? '—' }}</td>
                    <td class="hidden xl:table-cell">{{ $record->shift?->name ?? '—' }}</td>
                    <td class="tabular-nums text-sm">{{ $record->clock_in_at?->format('H:i') ?? '—' }}</td>
                    <td class="tabular-nums text-sm">{{ $record->clock_out_at?->format('H:i') ?? '—' }}</td>
                    <td class="hidden md:table-cell tabular-nums">{{ $record->actual_hours ?? '—' }}</td>
                    <td class="hidden md:table-cell tabular-nums">{{ $record->overtime_hours }}</td>
                    <td>
                        <span class="erp-badge erp-badge--{{ $record->status?->badgeClass() }}">
                            {{ $record->status?->label() }}
                        </span>
                    </td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            @can('adjust', $record)
                                <x-admin.table-row-action :href="route('admin.hr.attendance.adjust', $record)">{{ __('Adjust') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="11"><x-admin.empty-state icon="clock" :title="__('No attendance records')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer">
            <x-admin.table-pagination :paginator="$records" />
        </x-slot>
    </x-admin.data-table>
</x-admin-layout>
