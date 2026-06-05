<x-admin-layout :title="__('Shifts')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Attendance'), 'url' => route('admin.hr.attendance.dashboard')], ['label' => __('Shifts')]]">
    <x-admin.page-header :title="__('Shift Management')" :description="__('Configure work shifts with start times, grace periods, and breaks.')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.attendance.dashboard') }}" class="erp-btn-secondary">{{ __('Attendance') }}</a>
            @can('create', App\Models\Hr\Shift::class)
                <a href="{{ route('admin.hr.shifts.create') }}" class="erp-btn-primary">{{ __('Create shift') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <x-admin.data-table :search-placeholder="__('Search shifts…')" export-filename="shifts">
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Code') }}</th>
                <th scope="col">{{ __('Shift Name') }}</th>
                <th scope="col">{{ __('Type') }}</th>
                <th scope="col">{{ __('Start') }}</th>
                <th scope="col">{{ __('End') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Grace') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Break') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($shifts as $shift)
                <tr x-show="rowVisible(@js(strtolower($shift->code.' '.$shift->name.' '.$shift->shift_type->label())))">
                    <td class="font-mono text-[11px] text-slate-500">{{ $shift->code }}</td>
                    <td class="font-medium text-erp-primary">{{ $shift->name }}</td>
                    <td>{{ $shift->shift_type->label() }}</td>
                    <td class="tabular-nums">{{ substr($shift->start_time, 0, 5) }}</td>
                    <td class="tabular-nums">{{ substr($shift->end_time, 0, 5) }}</td>
                    <td class="hidden md:table-cell tabular-nums">{{ $shift->grace_minutes }}m</td>
                    <td class="hidden md:table-cell tabular-nums">{{ $shift->break_minutes }}m</td>
                    <td>
                        <span class="erp-badge erp-badge--{{ $shift->is_active ? 'success' : 'neutral' }}">
                            {{ $shift->is_active ? __('Active') : __('Inactive') }}
                        </span>
                    </td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            @can('update', $shift)
                                <x-admin.table-row-action :href="route('admin.hr.shifts.edit', $shift)">{{ __('Edit') }}</x-admin.table-row-action>
                                @if ($shift->is_active)
                                    <form method="POST" action="{{ route('admin.hr.shifts.deactivate', $shift) }}" class="contents" onsubmit="return confirm(@js(__('Deactivate this shift?')))">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="erp-table-row-action w-full text-left text-rose-700">{{ __('Deactivate') }}</button>
                                    </form>
                                @endif
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9"><x-admin.empty-state icon="clock" :title="__('No shifts configured')" /></td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
</x-admin-layout>
