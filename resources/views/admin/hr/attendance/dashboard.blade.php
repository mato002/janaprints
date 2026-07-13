@php
    use App\Support\Navigation\WorkspaceEmbed;
    $turboFrame = WorkspaceEmbed::turboFrame();
@endphp

<x-admin-layout :title="__('Attendance')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Attendance')]]">
    <x-admin.page-header :title="__('Attendance')" :description="__('Time tracking, daily register, and shift setup.')">
        <x-slot name="actions">
            @can('clock', App\Models\Hr\AttendanceRecord::class)
                <form method="POST" action="{{ route('admin.hr.attendance.clock-in') }}" class="inline">
                    @csrf
                    <button type="submit" class="erp-btn-secondary">{{ __('Clock In') }}</button>
                </form>
                <form method="POST" action="{{ route('admin.hr.attendance.clock-out') }}" class="inline">
                    @csrf
                    <button type="submit" class="erp-btn-secondary">{{ __('Clock Out') }}</button>
                </form>
            @endcan
            @can('create', App\Models\Hr\AttendanceRecord::class)
                <a href="{{ WorkspaceEmbed::url(route('admin.hr.attendance.create')) }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Manual attendance') }}</a>
            @endcan
            @if (($tab ?? 'register') === 'shifts')
                @can('create', App\Models\Hr\Shift::class)
                    <a href="{{ WorkspaceEmbed::url(route('admin.hr.shifts.create')) }}" class="erp-btn-secondary" data-erp-modal-open>{{ __('Create shift') }}</a>
                @endcan
            @endif
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        @foreach ([
            ['label' => __('Present Today'), 'value' => $stats['present_today'], 'icon' => 'check-circle', 'status' => 'present'],
            ['label' => __('Absent Today'), 'value' => $stats['absent_today'], 'icon' => 'x-circle', 'status' => 'absent'],
            ['label' => __('Late Today'), 'value' => $stats['late_today'], 'icon' => 'clock', 'status' => 'late'],
            ['label' => __('On Leave'), 'value' => $stats['on_leave'], 'icon' => 'calendar', 'status' => 'leave'],
            ['label' => __('Total Employees'), 'value' => $stats['total_employees'], 'icon' => 'users', 'status' => null],
            ['label' => __('Attendance %'), 'value' => $stats['attendance_percent'].'%', 'icon' => 'chart-pie', 'status' => null],
        ] as $card)
            @if ($card['status'])
                <a href="{{ WorkspaceEmbed::url(route('admin.hr.attendance.dashboard', WorkspaceEmbed::queryParams(['tab' => 'register', 'date' => $filters['date'] ?? now()->toDateString(), 'status' => $card['status']]))) }}" data-turbo-frame="{{ $turboFrame }}" class="block rounded-lg transition hover:ring-2 hover:ring-erp-accent/30">
                    <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
                </a>
            @else
                <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
            @endif
        @endforeach
    </div>

    <nav class="mt-6 mb-4 flex flex-wrap gap-2 border-b border-slate-200 pb-2" aria-label="{{ __('Attendance sections') }}">
        @php
            $attendanceTabs = [
                'register' => __('Register'),
            ];
            if ($canViewShifts ?? false) {
                $attendanceTabs['shifts'] = __('Shifts');
            }
        @endphp
        @foreach ($attendanceTabs as $id => $label)
            <a
                href="{{ WorkspaceEmbed::url(route('admin.hr.attendance.dashboard', WorkspaceEmbed::queryParams(['tab' => $id, 'date' => $filters['date'] ?? null]))) }}"
                data-turbo-frame="{{ $turboFrame }}"
                @class([
                    'rounded-md px-3 py-1.5 text-sm font-medium',
                    'bg-erp-primary text-white' => $tab === $id,
                    'text-slate-600 hover:bg-slate-100' => $tab !== $id,
                ])
            >{{ $label }}</a>
        @endforeach
    </nav>

    @if ($tab === 'shifts')
        @include('admin.hr.attendance.partials.workspace-shifts')
    @else
        @include('admin.hr.attendance.partials.workspace-register')
    @endif
</x-admin-layout>
