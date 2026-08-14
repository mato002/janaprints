@php
    use App\Support\Navigation\WorkspaceEmbed;

    $exportQuery = array_merge(
        collect($filters)->filter(fn ($value) => filled($value))->all(),
        ($activeDepartment ?? null) ? ['department' => $activeDepartment] : []
    );

    $resolvedAction = WorkspaceEmbed::url($indexRoute) ?? $indexRoute;
    $frame = WorkspaceEmbed::turboFrame();
    $embedded = WorkspaceEmbed::inWorkspaceContext();
@endphp

<form
    method="GET"
    action="{{ $resolvedAction }}"
    class="erp-index-toolbar-form erp-index-toolbar-form--compact erp-index-toolbar-form--dense"
    @if ($frame) data-turbo-frame="{{ $frame }}" @endif
>
    @if ($embedded)
        <input type="hidden" name="embedded" value="1">
    @endif

    <div class="erp-index-toolbar border-b border-erp-border bg-white px-2 py-1.5 sm:px-3">
        <div class="erp-index-toolbar-row flex items-center gap-1.5">
            <div class="flex min-w-0 flex-1 flex-nowrap items-center gap-1.5 overflow-x-auto">
                <input
                    id="search"
                    type="search"
                    name="search"
                    value="{{ $filters['search'] }}"
                    class="erp-toolbar-input min-w-[12rem] flex-[1.6]"
                    placeholder="{{ __('Search jobs…') }}"
                    aria-label="{{ __('Search') }}"
                    data-erp-auto-search
                >

                <x-admin.filter-sheet>
                    <select id="status" name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (App\Enums\ProductionQueueStatus::cases() as $queueStatus)
                            <option value="{{ $queueStatus->value }}" @selected($filters['status'] === $queueStatus->value)>
                                {{ $workspace->statusLabel($queueStatus) }}
                            </option>
                        @endforeach
                        <option value="blocked" @selected($filters['status'] === 'blocked')>{{ __('Blocked (unassigned)') }}</option>
                    </select>

                    <select id="priority" name="priority" class="erp-toolbar-select" aria-label="{{ __('Priority') }}">
                        <option value="">{{ __('All priorities') }}</option>
                        @foreach (App\Enums\ProductionPriority::cases() as $priority)
                            <option value="{{ $priority->value }}" @selected($filters['priority'] === $priority->value)>{{ ucfirst(str_replace('_', ' ', $priority->value)) }}</option>
                        @endforeach
                    </select>

                    <input id="from_date" type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('Logged from') }}">
                    <input id="to_date" type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('Logged to') }}">

                    @if (filled($filters['from_date'] ?? null) || filled($filters['to_date'] ?? null))
                        <input type="hidden" name="all_dates" value="0">
                    @endif

                    <select id="due" name="due" class="erp-toolbar-select" aria-label="{{ __('Due date') }}">
                        <option value="">{{ __('All dates') }}</option>
                        <option value="today" @selected($filters['due'] === 'today')>{{ __('Today') }}</option>
                        <option value="tomorrow" @selected($filters['due'] === 'tomorrow')>{{ __('Tomorrow') }}</option>
                        <option value="week" @selected($filters['due'] === 'week')>{{ __('This week') }}</option>
                        <option value="month" @selected($filters['due'] === 'month')>{{ __('This month') }}</option>
                        <option value="overdue" @selected($filters['due'] === 'overdue')>{{ __('Overdue') }}</option>
                    </select>

                    <select id="work_center_id" name="work_center_id" class="erp-toolbar-select" aria-label="{{ __('Work center') }}">
                        <option value="">{{ __('All work centres') }}</option>
                        @foreach ($workCenters as $center)
                            <option value="{{ $center->id }}" @selected((string) $filters['work_center_id'] === (string) $center->id)>{{ $center->name }}</option>
                        @endforeach
                    </select>

                    <select id="machine_id" name="machine_id" class="erp-toolbar-select" aria-label="{{ __('Machine') }}">
                        <option value="">{{ __('All machines') }}</option>
                        @foreach ($machines as $machine)
                            <option value="{{ $machine->id }}" @selected((string) $filters['machine_id'] === (string) $machine->id)>{{ $machine->asset_name }}</option>
                        @endforeach
                    </select>

                    <select id="operator_id" name="operator_id" class="erp-toolbar-select" aria-label="{{ __('Operator') }}">
                        <option value="">{{ __('All operators') }}</option>
                        <option value="unassigned" @selected($filters['operator_id'] === 'unassigned')>{{ __('Unassigned') }}</option>
                        @foreach ($operators as $operator)
                            <option value="{{ $operator->id }}" @selected((string) $filters['operator_id'] === (string) $operator->id)>{{ $operator->name }}</option>
                        @endforeach
                    </select>

                    @if ($customers->isNotEmpty())
                        <select id="customer_id" name="customer_id" class="erp-toolbar-select" aria-label="{{ __('Customer') }}">
                            <option value="">{{ __('All customers') }}</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected((string) $filters['customer_id'] === (string) $customer->id)>{{ $customer->company_name }}</option>
                            @endforeach
                        </select>
                    @endif
                </x-admin.filter-sheet>

                <button
                    type="button"
                    data-erp-filter-reset
                    class="erp-btn-ghost shrink-0 py-1 text-xs text-slate-500"
                >{{ __('Reset') }}</button>
            </div>

            <div class="ml-auto flex shrink-0 items-center gap-1.5">
                <x-admin.export-dropdown
                    export-route="admin.production.queue.export"
                    :export-query="$exportQuery"
                />
            </div>
        </div>
    </div>
</form>
