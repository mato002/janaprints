<x-admin.index-toolbar :action="$indexRoute" :reset-url="$indexRoute" compact>
    <input id="search" type="search" name="search" value="{{ $filters['search'] }}" class="erp-toolbar-input min-w-[10rem] flex-1" placeholder="{{ __('Search jobs…') }}" aria-label="{{ __('Search') }}" data-erp-auto-search>

    <input id="from_date" type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="erp-toolbar-input hidden lg:inline-block" aria-label="{{ __('Logged from') }}" data-erp-auto-submit>
    <input id="to_date" type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="erp-toolbar-input hidden lg:inline-block" aria-label="{{ __('Logged to') }}" data-erp-auto-submit>

    @if (filled($filters['from_date'] ?? null) || filled($filters['to_date'] ?? null))
        <input type="hidden" name="all_dates" value="0">
        <a href="{{ $indexRoute }}?all_dates=1" class="erp-toolbar-link text-xs whitespace-nowrap">{{ __('All logged dates') }}</a>
    @endif

    <select id="due" name="due" class="erp-toolbar-select" aria-label="{{ __('Due date') }}" data-erp-auto-submit>
        <option value="">{{ __('All dates') }}</option>
        <option value="today" @selected($filters['due'] === 'today')>{{ __('Today') }}</option>
        <option value="tomorrow" @selected($filters['due'] === 'tomorrow')>{{ __('Tomorrow') }}</option>
        <option value="week" @selected($filters['due'] === 'week')>{{ __('This week') }}</option>
        <option value="month" @selected($filters['due'] === 'month')>{{ __('This month') }}</option>
        <option value="overdue" @selected($filters['due'] === 'overdue')>{{ __('Overdue') }}</option>
    </select>

    <select id="priority" name="priority" class="erp-toolbar-select" aria-label="{{ __('Priority') }}" data-erp-auto-submit>
        <option value="">{{ __('All priorities') }}</option>
        @foreach (App\Enums\ProductionPriority::cases() as $priority)
            <option value="{{ $priority->value }}" @selected($filters['priority'] === $priority->value)>{{ ucfirst(str_replace('_', ' ', $priority->value)) }}</option>
        @endforeach
    </select>

    <select id="status" name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}" data-erp-auto-submit>
        <option value="">{{ __('All statuses') }}</option>
        @foreach (App\Enums\ProductionQueueStatus::cases() as $queueStatus)
            <option value="{{ $queueStatus->value }}" @selected($filters['status'] === $queueStatus->value)>
                {{ $workspace->statusLabel($queueStatus) }}
            </option>
        @endforeach
        <option value="blocked" @selected($filters['status'] === 'blocked')>{{ __('Blocked (unassigned)') }}</option>
    </select>

    <select id="work_center_id" name="work_center_id" class="erp-toolbar-select hidden md:inline-block" aria-label="{{ __('Work center') }}" data-erp-auto-submit>
        <option value="">{{ __('All work centres') }}</option>
        @foreach ($workCenters as $center)
            <option value="{{ $center->id }}" @selected((string) $filters['work_center_id'] === (string) $center->id)>{{ $center->name }}</option>
        @endforeach
    </select>

    <select id="machine_id" name="machine_id" class="erp-toolbar-select hidden lg:inline-block" aria-label="{{ __('Machine') }}" data-erp-auto-submit>
        <option value="">{{ __('All machines') }}</option>
        @foreach ($machines as $machine)
            <option value="{{ $machine->id }}" @selected((string) $filters['machine_id'] === (string) $machine->id)>{{ $machine->asset_name }}</option>
        @endforeach
    </select>

    <select id="operator_id" name="operator_id" class="erp-toolbar-select hidden lg:inline-block" aria-label="{{ __('Operator') }}" data-erp-auto-submit>
        <option value="">{{ __('All operators') }}</option>
        <option value="unassigned" @selected($filters['operator_id'] === 'unassigned')>{{ __('Unassigned') }}</option>
        @foreach ($operators as $operator)
            <option value="{{ $operator->id }}" @selected((string) $filters['operator_id'] === (string) $operator->id)>{{ $operator->name }}</option>
        @endforeach
    </select>

    @if ($customers->isNotEmpty())
        <select id="customer_id" name="customer_id" class="erp-toolbar-select hidden xl:inline-block" aria-label="{{ __('Customer') }}" data-erp-auto-submit>
            <option value="">{{ __('All customers') }}</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" @selected((string) $filters['customer_id'] === (string) $customer->id)>{{ $customer->company_name }}</option>
            @endforeach
        </select>
    @endif
</x-admin.index-toolbar>
