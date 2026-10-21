@php
    use App\Support\Sales\SalesDeskViews;

    $customer = $customer ?? null;
    $customers = $customers ?? collect();
    $customerQuery = $customerQuery ?? '';
    $invoiceFrom = $invoiceFrom ?? 'sales-desk';
@endphp

<div class="sales-desk-register__heading mb-3 flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-sm font-semibold text-erp-primary">{{ __('Invoices') }}</h2>
        <p class="text-xs text-slate-600">{{ __('Invoices created from jobs. Open one to print, approve, or post.') }}</p>
    </div>
    <a href="{{ SalesDeskViews::toBillUrl(array_filter(['embedded' => request()->boolean('embedded') ? 1 : null])) }}" class="erp-btn-secondary text-xs">
        {{ __('Jobs still to invoice') }}
    </a>
</div>

<form method="GET" action="{{ SalesDeskViews::invoicesUrl() }}" class="mb-4 flex flex-wrap items-end gap-2">
    @if (request()->boolean('embedded'))
        <input type="hidden" name="embedded" value="1">
    @endif
    <input type="hidden" name="view" value="{{ SalesDeskViews::INVOICES }}">
    <div class="min-w-[16rem] flex-1">
        <label class="erp-label" for="sales-invoices-customer">{{ __('Customer') }}</label>
        <input
            id="sales-invoices-customer"
            type="search"
            name="customer"
            value="{{ $customerQuery }}"
            class="erp-input w-full"
            placeholder="{{ __('Search customer invoices…') }}"
        >
    </div>
    @if ($customers->isNotEmpty())
        <div class="min-w-[14rem]">
            <label class="erp-label" for="sales-invoices-customer-id">{{ __('Jump to') }}</label>
            <select id="sales-invoices-customer-id" name="customer_id" class="erp-input w-full" onchange="this.form.submit()">
                <option value="">{{ __('All customers') }}</option>
                @foreach ($customers as $option)
                    <option value="{{ $option->id }}" @selected((int) ($customer?->id) === (int) $option->id)>{{ $option->company_name }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <button type="submit" class="erp-btn-secondary">{{ __('Show invoices') }}</button>
    @if ($customer || $customerQuery !== '')
        <a href="{{ SalesDeskViews::invoicesUrl(array_filter(['embedded' => request()->boolean('embedded') ? 1 : null])) }}" class="erp-btn-ghost text-sm">{{ __('Clear') }}</a>
    @endif
</form>

@include('admin.sales.invoices.partials.invoices-register', ['invoiceFrom' => $invoiceFrom])
