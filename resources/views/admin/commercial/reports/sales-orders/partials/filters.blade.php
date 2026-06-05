@props(['filters', 'branches', 'customers', 'salespersons'])

@php
    use App\Enums\SalesOrderStatus;
@endphp

<x-admin.card class="mb-6">
    <form method="GET" action="{{ route('commercial.reports.sales_orders.index') }}" data-turbo-frame="erp-main" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
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
            <label class="text-[11px] text-slate-500" for="customer_id">{{ __('Customer') }}</label>
            <select id="customer_id" name="customer_id" class="erp-input mt-1 w-full">
                <option value="">{{ __('All customers') }}</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(($filters['customer_id'] ?? null) == $customer->id)>{{ $customer->company_name }}</option>
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
            <label class="text-[11px] text-slate-500" for="status">{{ __('Status') }}</label>
            <select id="status" name="status" class="erp-input mt-1 w-full">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (SalesOrderStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ ucfirst(str_replace('_', ' ', $status->value)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="quotation_source">{{ __('Quotation Source') }}</label>
            <select id="quotation_source" name="quotation_source" class="erp-input mt-1 w-full">
                <option value="">{{ __('All sources') }}</option>
                <option value="from_quotation" @selected(($filters['quotation_source'] ?? '') === 'from_quotation')>{{ __('From quotation') }}</option>
                <option value="direct" @selected(($filters['quotation_source'] ?? '') === 'direct')>{{ __('Direct order') }}</option>
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="text-[11px] text-slate-500" for="search">{{ __('Search') }}</label>
            <input
                type="search"
                id="search"
                name="search"
                value="{{ $filters['search'] ?? '' }}"
                placeholder="{{ __('Order number or customer name…') }}"
                class="erp-input mt-1 w-full"
            >
        </div>
        <div class="flex items-end">
            <button type="submit" class="erp-btn-primary w-full sm:w-auto">{{ __('Apply filters') }}</button>
        </div>
    </form>
</x-admin.card>
