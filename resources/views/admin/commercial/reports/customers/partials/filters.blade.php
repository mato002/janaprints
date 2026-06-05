@props(['filters', 'branches', 'salespersons'])

@php
    use App\Enums\CustomerStatus;
    use App\Enums\CustomerType;
@endphp

<x-admin.card class="mb-6">
    <form method="GET" action="{{ route('commercial.reports.customers.index') }}" data-turbo-frame="erp-main" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <input type="hidden" name="tab" value="{{ $filters['tab'] ?? 'summary' }}">

        <div>
            <label class="text-[11px] text-slate-500" for="from_date">{{ __('From') }}</label>
            <input type="date" id="from_date" name="from_date" value="{{ $filters['from_date'] }}" class="erp-input mt-1 w-full">
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="to_date">{{ __('To') }}</label>
            <input type="date" id="to_date" name="to_date" value="{{ $filters['to_date'] }}" class="erp-input mt-1 w-full">
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="branch_id">{{ __('Branch') }}</label>
            <select id="branch_id" name="branch_id" class="erp-input mt-1 w-full">
                <option value="">{{ __('All branches') }}</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="customer_type">{{ __('Customer Type') }}</label>
            <select id="customer_type" name="customer_type" class="erp-input mt-1 w-full">
                <option value="">{{ __('All types') }}</option>
                @foreach (CustomerType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(($filters['customer_type'] ?? '') === $type->value)>{{ ucfirst($type->value) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="status">{{ __('Status') }}</label>
            <select id="status" name="status" class="erp-input mt-1 w-full">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (CustomerStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ ucfirst($status->value) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="salesperson_id">{{ __('Salesperson') }}</label>
            <select id="salesperson_id" name="salesperson_id" class="erp-input mt-1 w-full">
                <option value="">{{ __('All salespersons') }}</option>
                @foreach ($salespersons as $salesperson)
                    <option value="{{ $salesperson->id }}" @selected(($filters['salesperson_id'] ?? null) == $salesperson->id)>{{ $salesperson->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="activity_status">{{ __('Activity Status') }}</label>
            <select id="activity_status" name="activity_status" class="erp-input mt-1 w-full">
                <option value="">{{ __('All activity') }}</option>
                <option value="active" @selected(($filters['activity_status'] ?? '') === 'active')>{{ __('Active (ordered in period)') }}</option>
                <option value="inactive" @selected(($filters['activity_status'] ?? '') === 'inactive')>{{ __('Inactive (no orders in period)') }}</option>
                <option value="new" @selected(($filters['activity_status'] ?? '') === 'new')>{{ __('New (created in period)') }}</option>
                <option value="dormant" @selected(($filters['activity_status'] ?? '') === 'dormant')>{{ __('Dormant (no recent orders)') }}</option>
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="text-[11px] text-slate-500" for="search">{{ __('Search') }}</label>
            <input
                type="search"
                id="search"
                name="search"
                value="{{ $filters['search'] ?? '' }}"
                placeholder="{{ __('Customer name, code, contact, or email…') }}"
                class="erp-input mt-1 w-full"
            >
        </div>
        <div class="flex items-end">
            <button type="submit" class="erp-btn-primary w-full sm:w-auto">{{ __('Apply filters') }}</button>
        </div>
    </form>
</x-admin.card>
