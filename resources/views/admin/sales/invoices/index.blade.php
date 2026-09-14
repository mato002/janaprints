@php
    use App\Support\Sales\ReceivablesInvoiceViews;

    $customer = $customer ?? null;
    $customers = $customers ?? collect();
    $unbilledOrders = $unbilledOrders ?? collect();
    $customerQuery = $customerQuery ?? '';
    $activeView = ReceivablesInvoiceViews::normalize($activeView ?? request('view'));
    $isJobs = ReceivablesInvoiceViews::isJobs($activeView);
@endphp

<x-admin-layout :title="$isJobs ? __('Jobs to bill') : __('Customer invoices')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Invoices')]]">
    @include('admin.sales.invoices.partials.desk-view-nav')

    <x-admin.page-header
        :title="$isJobs ? __('Jobs') : __('Invoices')"
        :description="$isJobs
            ? __('Unbilled jobs ready to invoice. Generated invoices move to the Invoices page.')
            : __('Issued invoices and their payment status.')"
    >
        @can('create', App\Models\Sales\CustomerInvoice::class)
            <x-slot name="actions">
                <a href="{{ route('admin.invoices.create', ['from' => 'receivables']) }}" class="erp-btn-primary" data-erp-modal-open>
                    {{ __('Create invoice') }}
                </a>
            </x-slot>
        @endcan
    </x-admin.page-header>

    <form method="GET" action="{{ ReceivablesInvoiceViews::url($activeView) }}" class="mb-4 flex flex-wrap items-end gap-2">
        @if (request()->boolean('embedded'))
            <input type="hidden" name="embedded" value="1">
        @endif
        @if ($isJobs)
            <input type="hidden" name="view" value="{{ ReceivablesInvoiceViews::JOBS }}">
        @endif
        <div class="min-w-[16rem] flex-1">
            <label class="erp-label" for="invoice-customer-search">{{ __('Customer') }}</label>
            <input
                id="invoice-customer-search"
                type="search"
                name="customer"
                value="{{ $customerQuery }}"
                class="erp-input w-full"
                placeholder="{{ $isJobs ? __('Search customer to see jobs to bill…') : __('Search customer to see invoices…') }}"
            >
        </div>
        @if ($customers->isNotEmpty())
            <div class="min-w-[14rem]">
                <label class="erp-label" for="invoice-customer-id">{{ __('Jump to') }}</label>
                <select id="invoice-customer-id" name="customer_id" class="erp-input w-full" onchange="this.form.submit()">
                    <option value="">{{ __('All customers') }}</option>
                    @foreach ($customers as $option)
                        <option value="{{ $option->id }}" @selected((int) ($customer?->id) === (int) $option->id)>{{ $option->company_name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <button type="submit" class="erp-btn-secondary">{{ $isJobs ? __('Show jobs') : __('Show invoices') }}</button>
        @if ($customer || $customerQuery !== '')
            <a href="{{ ReceivablesInvoiceViews::url($activeView, array_filter(['embedded' => request()->boolean('embedded') ? 1 : null])) }}" class="erp-btn-ghost text-sm">{{ __('Clear') }}</a>
        @endif
    </form>

    @if ($customer)
        <p class="mb-3 text-sm text-slate-600">
            {{ $isJobs
                ? __('Showing jobs to bill for :customer.', ['customer' => $customer->company_name])
                : __('Showing invoices for :customer.', ['customer' => $customer->company_name]) }}
        </p>
    @endif

    @if ($isJobs)
        @include('admin.sales.invoices.partials.jobs-register')
    @else
        @include('admin.sales.invoices.partials.invoices-register')
    @endif
</x-admin-layout>
