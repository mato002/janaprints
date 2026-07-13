@php
    use App\Support\Navigation\WorkspaceEmbed;
    $turboFrame = WorkspaceEmbed::turboFrame();
@endphp

<div class="mb-4 flex flex-wrap items-center gap-2">
    <a
        href="{{ WorkspaceEmbed::url(route('admin.hr.leave.dashboard', WorkspaceEmbed::queryParams(array_merge($filters ?? [], ['tab' => 'calendar', 'view' => 'month', 'year' => $year, 'month' => $month])))) }}"
        data-turbo-frame="{{ $turboFrame }}"
        @class(['erp-btn-secondary', 'ring-2 ring-erp-accent' => ($calendarView ?? 'month') === 'month'])
    >{{ __('Monthly') }}</a>
    <a
        href="{{ WorkspaceEmbed::url(route('admin.hr.leave.dashboard', WorkspaceEmbed::queryParams(array_merge($filters ?? [], ['tab' => 'calendar', 'view' => 'week', 'week' => $weekStart->toDateString()])))) }}"
        data-turbo-frame="{{ $turboFrame }}"
        @class(['erp-btn-secondary', 'ring-2 ring-erp-accent' => ($calendarView ?? 'month') === 'week'])
    >{{ __('Weekly') }}</a>
    <span class="ml-auto text-sm text-slate-500">{{ $periodLabel }}</span>
</div>

<x-admin.card :padding="false" class="mb-4">
    <form method="GET" action="{{ route('admin.hr.leave.dashboard') }}" x-data="erpIndexFilterForm()" @change="onFieldChange($event)" class="erp-index-toolbar-form">
        <div class="erp-index-toolbar border-b border-erp-border bg-white px-4 py-3">
            <div class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="tab" value="calendar">
                <input type="hidden" name="view" value="{{ $calendarView }}">
                @if (WorkspaceEmbed::inWorkspaceContext())
                    <input type="hidden" name="embedded" value="1">
                @endif
                @if (($calendarView ?? 'month') === 'month')
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                @else
                    <input type="hidden" name="week" value="{{ $weekStart->toDateString() }}">
                @endif
                <select name="branch_id" class="erp-toolbar-select" aria-label="{{ __('Branch') }}">
                    <option value="">{{ __('All branches') }}</option>
                    @foreach ($formData['branches'] as $branch)
                        <option value="{{ $branch->id }}" @selected((int) ($filters['branch_id'] ?? 0) === $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
                <select name="department_id" class="erp-toolbar-select" aria-label="{{ __('Department') }}">
                    <option value="">{{ __('All departments') }}</option>
                    @foreach ($formData['departments'] as $department)
                        <option value="{{ $department->id }}" @selected((int) ($filters['department_id'] ?? 0) === $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
                <a href="{{ WorkspaceEmbed::url(route('admin.hr.leave.dashboard', WorkspaceEmbed::queryParams(['tab' => 'calendar']))) }}" class="erp-btn-ghost py-1 text-xs text-slate-500" data-turbo-frame="{{ $turboFrame }}">{{ __('Reset') }}</a>
            </div>
        </div>
    </form>
</x-admin.card>

<div class="grid gap-2 @if(($calendarView ?? 'month') === 'month') sm:grid-cols-7 @else grid-cols-1 @endif">
    @foreach ($events as $day)
        @if ($day['requests']->isNotEmpty())
            <div class="erp-card p-3 @if(($calendarView ?? 'month') === 'month') min-h-[6rem] @endif">
                <p class="mb-2 text-xs font-semibold text-slate-500">{{ \Illuminate\Support\Carbon::parse($day['date'])->format('D, M j') }}</p>
                <div class="space-y-1">
                    @foreach ($day['requests'] as $leaveRequest)
                        <a href="{{ WorkspaceEmbed::url(route('admin.hr.leave.show', $leaveRequest)) }}" class="block rounded bg-erp-page px-2 py-1 text-xs hover:bg-erp-accent/10">
                            <span class="font-medium">{{ $leaveRequest->employee?->full_name }}</span>
                            <span class="text-slate-500"> · {{ $leaveRequest->leaveType?->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @elseif (($calendarView ?? 'month') === 'week')
            <div class="erp-card p-3 text-sm text-slate-400">{{ \Illuminate\Support\Carbon::parse($day['date'])->format('l, M j') }} — {{ __('No leave') }}</div>
        @endif
    @endforeach
</div>
