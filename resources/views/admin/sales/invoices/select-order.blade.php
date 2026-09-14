<x-admin.modal-form
    :title="__('Create invoice')"
    :breadcrumbs="[
        ['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')],
        ['label' => __('Invoices'), 'url' => route('admin.invoices.index')],
        ['label' => __('Create invoice')],
    ]"
    maxWidth="3xl"
>
    @unless (request()->header('Turbo-Frame') === 'erp-form-modal')
        <x-admin.page-header
            :title="__('Create invoice')"
            :description="__('Search a customer, tick their jobs, and create one invoice. Jobs for the same customer can be billed together.')"
        />
    @else
        <p class="mb-4 text-sm text-slate-600">
            {{ __('Search a customer, tick their jobs, and create one invoice. Jobs for the same customer can be billed together.') }}
        </p>
    @endunless

    @include('admin.partials.modal-validation-alert')

    @if ($orderOptions === [])
        <x-admin.empty-state
            icon="receipt-tax"
            :title="__('No billable sales orders found')"
            :description="__('Confirm a sales order first, or check that it still has a remaining billable balance. You can also create invoices from a sales order or delivery note.')"
        />
    @else
        <div
            class="space-y-4"
            x-data="{
                orders: @js($orderOptions),
                query: '',
                selectedIds: [],
                submitting: false,
                otherCustomerHint: @js(__('Only :customer jobs can be added to this invoice. Clear the selection to bill a different customer.')),
                get filtered() {
                    const needle = this.query.trim().toLowerCase();
                    if (! needle) return this.orders;
                    return this.orders.filter((order) => (order.search ?? '').includes(needle));
                },
                get selectedOrders() {
                    const selected = new Set(this.selectedIds);
                    return this.orders.filter((order) => selected.has(order.value));
                },
                get selectedCount() { return this.selectedOrders.length; },
                get selectedCustomer() { return this.selectedOrders[0]?.customer ?? ''; },
                get selectedCustomerId() {
                    const id = this.selectedOrders[0]?.customer_id;
                    return id === undefined || id === null || id === '' ? null : Number(id);
                },
                get remainingTotal() {
                    return this.selectedOrders.reduce((sum, order) => sum + Number(order.remaining_amount || 0), 0);
                },
                get remainingTotalLabel() {
                    return this.remainingTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
                get singleHref() {
                    return this.selectedCount === 1 ? (this.selectedOrders[0]?.href || '#') : '#';
                },
                get mixedCustomerHint() {
                    if (this.selectedCustomerId === null || this.query.trim() === '') return '';
                    const blocked = this.filtered.some((order) => Number(order.customer_id) !== this.selectedCustomerId);
                    if (! blocked) return '';
                    return this.otherCustomerHint.replace(':customer', this.selectedCustomer);
                },
                isSelected(order) { return this.selectedIds.includes(order.value); },
                canSelect(order) {
                    return this.selectedCustomerId === null || Number(order.customer_id) === this.selectedCustomerId;
                },
                toggle(order) {
                    if (this.isSelected(order)) {
                        this.selectedIds = this.selectedIds.filter((id) => id !== order.value);
                        return;
                    }
                    if (! this.canSelect(order)) return;
                    this.selectedIds = [...this.selectedIds, order.value];
                },
                selectFilteredForCustomer() {
                    const customerId = this.selectedCustomerId ?? (
                        this.query.trim() !== '' && this.filtered[0] ? Number(this.filtered[0].customer_id) : null
                    );
                    if (customerId === null) return;
                    const pool = this.query.trim() !== '' ? this.filtered : this.orders;
                    this.selectedIds = pool.filter((order) => Number(order.customer_id) === customerId).map((order) => order.value);
                },
                clearSelection() { this.selectedIds = []; },
                showError(message) {
                    const items = [message].filter(Boolean);
                    if (items.length === 0) return;
                    if (typeof window.showErpFormErrorAlert === 'function') {
                        window.showErpFormErrorAlert(items);
                        return;
                    }
                    if (typeof window.showErpSweetAlert === 'function') {
                        window.showErpSweetAlert(items.join('\n'), 'error');
                        return;
                    }
                    window.alert(items.join('\n'));
                },
                async submitCombined() {
                    if (this.selectedCount < 2 || this.submitting) return;
                    this.submitting = true;
                    try {
                        const form = this.$refs.mergeForm;
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: new FormData(form),
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        });
                        if (response.status === 422) {
                            const payload = await response.json().catch(() => ({}));
                            const fromErrors = payload.errors
                                ? Object.values(payload.errors).flat().filter(Boolean)
                                : [];
                            this.showError(fromErrors[0] || payload.message || @js(__('Unable to create the invoice.')));
                            return;
                        }
                        if (response.redirected && response.url) {
                            window.location.href = response.url;
                            return;
                        }
                        if (! response.ok) {
                            this.showError(@js(__('Unable to create the invoice.')));
                            return;
                        }
                        const payload = await response.json().catch(() => null);
                        window.location.href = payload?.redirect || @js(\App\Support\Sales\ReceivablesInvoiceViews::invoicesUrl());
                    } catch (error) {
                        this.showError(@js(__('Unable to create the invoice.')));
                    } finally {
                        this.submitting = false;
                    }
                },
            }"
        >
            <form
                x-ref="mergeForm"
                method="POST"
                action="{{ route('admin.invoices.store-from-sales-orders') }}"
                class="hidden"
                @submit.prevent="submitCombined()"
            >
                @csrf
                @if (filled($fromDesk ?? request('from')))
                    <input type="hidden" name="from" value="{{ $fromDesk ?? request('from') }}">
                @endif
                <input type="hidden" name="invoice_date" value="{{ now()->toDateString() }}">
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="sales_order_ids[]" :value="id">
                </template>
            </form>

            <div>
                <label for="invoice-order-filter" class="erp-label">{{ __('Customer or job') }}</label>
                <input
                    id="invoice-order-filter"
                    type="search"
                    x-model="query"
                    class="erp-input w-full"
                    placeholder="{{ __('Search by customer name or job number…') }}"
                    autocomplete="off"
                >
                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                    <span>
                        <span x-text="filtered.length"></span> {{ __('of') }} {{ count($orderOptions) }} {{ __('billable jobs') }}
                    </span>
                    <button type="button" class="erp-btn-ghost px-2 py-1 text-xs" @click="selectFilteredForCustomer()">
                        {{ __('Select all for this customer') }}
                    </button>
                    <button type="button" class="erp-btn-ghost px-2 py-1 text-xs" x-show="selectedCount > 0" x-cloak @click="clearSelection()">
                        {{ __('Clear selection') }}
                    </button>
                </div>
            </div>

            <div class="max-h-80 overflow-y-auto rounded-lg border border-erp-border bg-white">
                <template x-if="filtered.length === 0">
                    <p class="px-4 py-8 text-center text-sm text-slate-500">{{ __('No jobs match your filter.') }}</p>
                </template>
                <template x-for="order in filtered" :key="order.value">
                    <label
                        class="flex w-full cursor-pointer items-start gap-3 border-b border-slate-100 px-4 py-3 text-left transition last:border-b-0"
                        :class="{
                            'bg-erp-accent/10 ring-1 ring-inset ring-erp-accent/30': isSelected(order),
                            'hover:bg-slate-50': canSelect(order),
                            'cursor-not-allowed opacity-40': ! canSelect(order),
                        }"
                    >
                        <input
                            type="checkbox"
                            class="mt-1 rounded border-slate-300"
                            :checked="isSelected(order)"
                            :disabled="! canSelect(order)"
                            @click.prevent="toggle(order)"
                        >
                        <div class="min-w-0 flex-1">
                            <p class="font-mono text-sm font-semibold text-erp-primary" x-text="order.order_number"></p>
                            <p class="text-sm text-slate-700" x-text="order.customer"></p>
                            <p class="mt-1 text-xs text-slate-500">
                                <span x-text="order.order_date"></span>
                                ·
                                <span x-text="order.status"></span>
                            </p>
                        </div>
                        <div class="shrink-0 text-right text-sm">
                            <p class="font-mono text-slate-600" x-text="order.total"></p>
                            <p class="font-mono font-semibold text-erp-primary">
                                {{ __('Remaining') }}: <span x-text="order.remaining"></span>
                            </p>
                        </div>
                    </label>
                </template>
            </div>

            <p class="text-xs text-amber-800" x-show="mixedCustomerHint" x-cloak x-text="mixedCustomerHint"></p>

            <div
                x-show="selectedCount > 0"
                x-cloak
                class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm"
            >
                <p class="font-medium text-slate-900">
                    <span x-text="selectedCount"></span>
                    <span x-text="selectedCount === 1 ? @js(__('job selected')) : @js(__('jobs selected'))"></span>
                    <span x-show="selectedCustomer"> — <span x-text="selectedCustomer"></span></span>
                </p>
                <p class="text-slate-600">
                    {{ __('Combined remaining') }}:
                    <span class="font-mono font-semibold" x-text="remainingTotalLabel"></span>
                </p>
                <p class="mt-1 text-xs text-slate-500" x-show="selectedCount > 1">
                    {{ __('These jobs will be billed as one invoice.') }}
                </p>
            </div>

            <x-admin.form-modal-actions>
                <a
                    x-show="selectedCount <= 1"
                    :href="singleHref"
                    class="erp-btn-primary"
                    data-erp-modal-open
                    :class="{ 'pointer-events-none opacity-50': selectedCount !== 1 }"
                    :aria-disabled="selectedCount !== 1"
                    @click="selectedCount !== 1 && $event.preventDefault()"
                >{{ __('Continue') }}</a>
                <button
                    type="button"
                    x-show="selectedCount > 1"
                    x-cloak
                    class="erp-btn-primary disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="submitting"
                    @click="submitCombined()"
                >{{ __('Create combined invoice') }}</button>
            </x-admin.form-modal-actions>
        </div>
    @endif
</x-admin.modal-form>
