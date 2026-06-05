@props(['filters', 'branches', 'cashiers'])

@php
    use App\Enums\PosPaymentMethod;
    use App\Enums\PosSaleStatus;
@endphp

<x-admin.card class="mb-6">
    <form method="GET" action="{{ route('commercial.pos.reports.index') }}" data-turbo-frame="erp-main" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <input type="hidden" name="tab" value="{{ $filters['tab'] ?? 'sales_by_cashier' }}">

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
            <label class="text-[11px] text-slate-500" for="cashier_id">{{ __('Cashier') }}</label>
            <select id="cashier_id" name="cashier_id" class="erp-input mt-1 w-full">
                <option value="">{{ __('All cashiers') }}</option>
                @foreach ($cashiers as $cashier)
                    <option value="{{ $cashier->id }}" @selected(($filters['cashier_id'] ?? null) == $cashier->id)>{{ $cashier->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="payment_method">{{ __('Payment Method') }}</label>
            <select id="payment_method" name="payment_method" class="erp-input mt-1 w-full">
                <option value="">{{ __('All payment methods') }}</option>
                @foreach (PosPaymentMethod::cases() as $method)
                    <option value="{{ $method->value }}" @selected(($filters['payment_method'] ?? '') === $method->value)>{{ ucfirst($method->value) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="status">{{ __('Status') }}</label>
            <select id="status" name="status" class="erp-input mt-1 w-full">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (PosSaleStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ ucfirst(str_replace('_', ' ', $status->value)) }}</option>
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
                placeholder="{{ __('Sale number…') }}"
                class="erp-input mt-1 w-full"
            >
        </div>
        <div class="flex items-end">
            <button type="submit" class="erp-btn-primary w-full sm:w-auto">{{ __('Apply filters') }}</button>
        </div>
    </form>
</x-admin.card>
