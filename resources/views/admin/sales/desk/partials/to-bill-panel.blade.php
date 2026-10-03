@php
    use App\Support\Sales\SalesDeskViews;

    $customer = $customer ?? null;
    $customers = $customers ?? collect();
    $customerQuery = $customerQuery ?? '';
    $invoiceFrom = $invoiceFrom ?? 'sales-desk';
@endphp

<div class="sales-desk-register__heading mb-3 flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-sm font-semibold text-erp-primary">{{ __('Jobs to invoice') }}</h2>
        <p class="text-xs text-slate-600">{{ __('Select one or more jobs, then generate a single invoice. Same tools as Accounting, for the salesperson.') }}</p>
    </div>
    @can('create', App\Models\Sales\CustomerInvoice::class)
        <a href="{{ route('admin.invoices.create', ['from' => 'sales-desk', 'return_view' => 'to-bill']) }}" class="erp-btn-primary text-xs" data-erp-modal-open>
            {{ __('Create invoice') }}
        </a>
    @endcan
</div>

@if (session('status'))
    <div class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
@endif
@if (session('error'))
    <div class="mb-3 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
@endif

<form method="GET" action="{{ SalesDeskViews::toBillUrl() }}" class="mb-4 flex flex-wrap items-end gap-2">
    @if (request()->boolean('embedded'))
        <input type="hidden" name="embedded" value="1">
    @endif
    <input type="hidden" name="view" value="{{ SalesDeskViews::TO_BILL }}">
    <div class="min-w-[16rem] flex-1">
        <label class="erp-label" for="sales-to-bill-customer">{{ __('Customer') }}</label>
        <input
            id="sales-to-bill-customer"
            type="search"
            name="customer"
            value="{{ $customerQuery }}"
            class="erp-input w-full"
            placeholder="{{ __('Search customer to see jobs to bill…') }}"
        >
    </div>
    @if ($customers->isNotEmpty())
        <div class="min-w-[14rem]">
            <label class="erp-label" for="sales-to-bill-customer-id">{{ __('Jump to') }}</label>
            <select id="sales-to-bill-customer-id" name="customer_id" class="erp-input w-full" onchange="this.form.submit()">
                <option value="">{{ __('All customers') }}</option>
                @foreach ($customers as $option)
                    <option value="{{ $option->id }}" @selected((int) ($customer?->id) === (int) $option->id)>{{ $option->company_name }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <button type="submit" class="erp-btn-secondary">{{ __('Show jobs') }}</button>
    @if ($customer || $customerQuery !== '')
        <a href="{{ SalesDeskViews::toBillUrl(array_filter(['embedded' => request()->boolean('embedded') ? 1 : null])) }}" class="erp-btn-ghost text-sm">{{ __('Clear') }}</a>
    @endif
</form>

@if ($customer)
    <p class="mb-3 text-sm text-slate-600">{{ __('Showing jobs to bill for :customer.', ['customer' => $customer->company_name]) }}</p>
@endif

@include('admin.sales.invoices.partials.jobs-register', ['invoiceFrom' => $invoiceFrom])
