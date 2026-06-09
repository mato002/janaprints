<x-admin-layout :title="__('Leave Calendar')" :breadcrumbs="[['label' => __('Leave'), 'url' => route('admin.hr.leave.dashboard')], ['label' => __('Calendar')]]">
    <x-admin.page-header :title="__('Leave Calendar')" :description="$periodLabel">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.leave.calendar', array_merge($filters, ['view' => 'month', 'year' => $year, 'month' => $month])) }}" class="erp-btn-secondary @if($view === 'month') ring-2 ring-erp-accent @endif">{{ __('Monthly') }}</a>
            <a href="{{ route('admin.hr.leave.calendar', array_merge($filters, ['view' => 'week', 'week' => $weekStart->toDateString()])) }}" class="erp-btn-secondary @if($view === 'week') ring-2 ring-erp-accent @endif">{{ __('Weekly') }}</a>
        </x-slot>
    </x-admin.page-header>

    <x-admin.card :padding="false" class="mb-4">
        <form method="GET" x-data="erpIndexFilterForm()" @change="onFieldChange($event)" class="erp-index-toolbar-form">
            <div class="erp-index-toolbar border-b border-erp-border bg-white px-4 py-3">
                <div class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="view" value="{{ $view }}">
                    @if ($view === 'month')
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
                    <a href="{{ route('admin.hr.leave.calendar') }}" class="erp-btn-ghost py-1 text-xs text-slate-500" data-turbo-frame="erp-main">{{ __('Reset') }}</a>
                </div>
            </div>
        </form>
    </x-admin.card>

    <div class="grid gap-2 @if($view === 'month') sm:grid-cols-7 @else grid-cols-1 @endif">
        @foreach ($events as $day)
            @if ($day['requests']->isNotEmpty())
                <div class="erp-card p-3 @if($view === 'month') min-h-[6rem] @endif">
                    <p class="text-xs font-semibold text-slate-500 mb-2">{{ \Illuminate\Support\Carbon::parse($day['date'])->format('D, M j') }}</p>
                    <div class="space-y-1">
                        @foreach ($day['requests'] as $leaveRequest)
                            <a href="{{ route('admin.hr.leave.show', $leaveRequest) }}" class="block rounded bg-erp-page px-2 py-1 text-xs hover:bg-erp-accent/10">
                                <span class="font-medium">{{ $leaveRequest->employee?->full_name }}</span>
                                <span class="text-slate-500"> · {{ $leaveRequest->leaveType?->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @elseif ($view === 'week')
                <div class="erp-card p-3 text-sm text-slate-400">{{ \Illuminate\Support\Carbon::parse($day['date'])->format('l, M j') }} — {{ __('No leave') }}</div>
            @endif
        @endforeach
    </div>
</x-admin-layout>
