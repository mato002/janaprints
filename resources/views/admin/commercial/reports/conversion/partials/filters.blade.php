@props(['filters', 'branches', 'lead_sources', 'salespersons'])

@php
    use App\Enums\CustomerType;
    use App\Enums\LeadStatus;
@endphp

<x-admin.card class="mb-6">
    <form method="GET" action="{{ route('commercial.reports.conversion.index') }}" data-turbo-frame="erp-main" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <input type="hidden" name="tab" value="{{ $filters['tab'] ?? 'full_funnel' }}">

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
            <label class="text-[11px] text-slate-500" for="salesperson_id">{{ __('Salesperson') }}</label>
            <select id="salesperson_id" name="salesperson_id" class="erp-input mt-1 w-full">
                <option value="">{{ __('All salespersons') }}</option>
                @foreach ($salespersons as $salesperson)
                    <option value="{{ $salesperson->id }}" @selected(($filters['salesperson_id'] ?? null) == $salesperson->id)>{{ $salesperson->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="lead_source_id">{{ __('Lead Source') }}</label>
            <select id="lead_source_id" name="lead_source_id" class="erp-input mt-1 w-full">
                <option value="">{{ __('All sources') }}</option>
                @foreach ($lead_sources as $source)
                    <option value="{{ $source->id }}" @selected(($filters['lead_source_id'] ?? null) == $source->id)>{{ $source->name }}</option>
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
            <label class="text-[11px] text-slate-500" for="status">{{ __('Lead Status') }}</label>
            <select id="status" name="status" class="erp-input mt-1 w-full">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (LeadStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ ucfirst($status->value) }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="text-[11px] text-slate-500" for="search">{{ __('Search') }}</label>
            <input
                type="search"
                id="search"
                name="search"
                value="{{ $filters['search'] ?? '' }}"
                placeholder="{{ __('Lead, quote, or order reference…') }}"
                class="erp-input mt-1 w-full"
            >
        </div>
        <div class="flex items-end">
            <button type="submit" class="erp-btn-primary w-full sm:w-auto">{{ __('Apply filters') }}</button>
        </div>
    </form>
</x-admin.card>
