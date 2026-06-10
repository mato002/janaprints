<x-admin-layout :title="__('Attendance')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Attendance')]]">
    <x-admin.page-header :title="__('Attendance')" :description="__('Workforce attendance dashboard and time tracking.')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.attendance.index', $filters) }}" class="erp-btn-secondary">{{ __('Attendance register') }}</a>
            @can('create', App\Models\Hr\AttendanceRecord::class)
                <a href="{{ route('admin.hr.attendance.create') }}" class="erp-btn-secondary" data-erp-modal-open>{{ __('Manual attendance') }}</a>
            @endcan
            @can('viewAny', App\Models\Hr\Shift::class)
                <a href="{{ route('admin.hr.shifts.index') }}" class="erp-btn-secondary">{{ __('Shifts') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    @include('admin.hr.attendance.partials.filters', [
        'filters' => $filters,
        'formData' => $formData,
        'action' => route('admin.hr.attendance.dashboard'),
    ])

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        @foreach ([
            ['label' => __('Present Today'), 'value' => $stats['present_today'], 'icon' => 'check-circle'],
            ['label' => __('Absent Today'), 'value' => $stats['absent_today'], 'icon' => 'x-circle'],
            ['label' => __('Late Today'), 'value' => $stats['late_today'], 'icon' => 'clock'],
            ['label' => __('On Leave'), 'value' => $stats['on_leave'], 'icon' => 'calendar'],
            ['label' => __('Total Employees'), 'value' => $stats['total_employees'], 'icon' => 'users'],
            ['label' => __('Attendance %'), 'value' => $stats['attendance_percent'].'%', 'icon' => 'chart-pie'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    @can('clock', App\Models\Hr\AttendanceRecord::class)
        <x-admin.card class="mt-6">
            <h3 class="text-sm font-semibold text-erp-primary">{{ __('My clock actions') }}</h3>
            <p class="mt-1 text-sm text-slate-600">{{ __('Record your attendance for today.') }}</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <form method="POST" action="{{ route('admin.hr.attendance.clock-in') }}">
                    @csrf
                    <button type="submit" class="erp-btn-primary">{{ __('Clock In') }}</button>
                </form>
                <form method="POST" action="{{ route('admin.hr.attendance.clock-out') }}">
                    @csrf
                    <button type="submit" class="erp-btn-secondary">{{ __('Clock Out') }}</button>
                </form>
            </div>
        </x-admin.card>
    @endcan

    <x-admin.card class="mt-6">
        <p class="text-sm text-slate-600">
            {{ __('Future integrations: biometric devices, mobile GPS check-in, QR attendance, and machine attendance import.') }}
        </p>
    </x-admin.card>
</x-admin-layout>
