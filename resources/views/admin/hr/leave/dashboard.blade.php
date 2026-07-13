@php
    use App\Support\Navigation\WorkspaceEmbed;
    $turboFrame = WorkspaceEmbed::turboFrame();
@endphp

<x-admin-layout :title="__('Leave')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Leave')]]">
    <x-admin.page-header :title="__('Leave Management')" :description="__('Leave requests, balances, and workforce absence planning.')">
        <x-slot name="actions">
            @can('create', App\Models\Hr\LeaveRequest::class)
                <a href="{{ WorkspaceEmbed::url(route('admin.hr.leave.create')) }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Apply for leave') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <a href="{{ WorkspaceEmbed::url(route('admin.hr.leave.dashboard', WorkspaceEmbed::queryParams(['tab' => 'requests', 'status' => 'submitted']))) }}" data-turbo-frame="{{ $turboFrame }}" class="block rounded-lg transition hover:ring-2 hover:ring-erp-accent/30">
            <x-admin.kpi-widget :label="__('Pending Approval')" :value="$stats['pending']" icon="clock" />
        </a>
        <a href="{{ WorkspaceEmbed::url(route('admin.hr.leave.dashboard', WorkspaceEmbed::queryParams(['tab' => 'requests', 'status' => 'approved']))) }}" data-turbo-frame="{{ $turboFrame }}" class="block rounded-lg transition hover:ring-2 hover:ring-erp-accent/30">
            <x-admin.kpi-widget :label="__('Approved This Month')" :value="$stats['approved_this_month']" icon="check-circle" />
        </a>
        <a href="{{ WorkspaceEmbed::url(route('admin.hr.leave.dashboard', WorkspaceEmbed::queryParams(['tab' => 'calendar', 'view' => 'week']))) }}" data-turbo-frame="{{ $turboFrame }}" class="block rounded-lg transition hover:ring-2 hover:ring-erp-accent/30">
            <x-admin.kpi-widget :label="__('On Leave Today')" :value="$stats['on_leave_today']" icon="calendar" />
        </a>
    </div>

    <nav class="mt-6 mb-4 flex flex-wrap gap-2 border-b border-slate-200 pb-2" aria-label="{{ __('Leave sections') }}">
        @php
            $leaveTabs = [
                'requests' => __('All requests'),
                'balances' => __('Leave balances'),
                'calendar' => __('Calendar'),
            ];
            if ($canManageSetup ?? false) {
                $leaveTabs['setup'] = __('Setup');
            }
        @endphp
        @foreach ($leaveTabs as $id => $label)
            <a
                href="{{ WorkspaceEmbed::url(route('admin.hr.leave.dashboard', WorkspaceEmbed::queryParams(array_filter(['tab' => $id, 'view' => $id === 'calendar' ? ($calendarView ?? 'month') : null])))) }}"
                data-turbo-frame="{{ $turboFrame }}"
                @class([
                    'rounded-md px-3 py-1.5 text-sm font-medium',
                    'bg-erp-primary text-white' => $tab === $id,
                    'text-slate-600 hover:bg-slate-100' => $tab !== $id,
                ])
            >{{ $label }}</a>
        @endforeach
    </nav>

    @if ($tab === 'requests')
        @include('admin.hr.leave.partials.workspace-requests')
    @elseif ($tab === 'balances')
        @include('admin.hr.leave.partials.workspace-balances')
    @elseif ($tab === 'calendar')
        @include('admin.hr.leave.partials.workspace-calendar')
    @elseif ($tab === 'setup')
        @include('admin.hr.leave.partials.workspace-setup')
    @endif
</x-admin-layout>
