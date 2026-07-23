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
            :description="__('Choose a sales order with a remaining billable balance. Use the filter to narrow the list — all billable orders are shown by default.')"
        />
    @else
        <p class="mb-4 text-sm text-slate-600">
            {{ __('Choose a sales order with a remaining billable balance. Use the filter to narrow the list — all billable orders are shown by default.') }}
        </p>
    @endunless

    @if ($orderOptions === [])
        <x-admin.empty-state
            icon="receipt-tax"
            :title="__('No billable sales orders found')"
            :description="__('Confirm a sales order first, or check that it still has a remaining billable balance. You can also create invoices from a sales order or delivery note.')"
        />
    @else
        <div class="space-y-4" x-data="invoiceOrderPicker(@js($orderOptions))">
            <div>
                <label for="invoice-order-filter" class="erp-label">{{ __('Sales order') }}</label>
                <input
                    id="invoice-order-filter"
                    type="search"
                    x-model="query"
                    class="erp-input w-full"
                    placeholder="{{ __('Filter by order number or customer…') }}"
                    autocomplete="off"
                >
                <p class="mt-1 text-xs text-slate-500">
                    <span x-text="filtered.length"></span> {{ __('of') }} {{ count($orderOptions) }} {{ __('billable orders') }}
                </p>
            </div>

            <div class="max-h-80 overflow-y-auto rounded-lg border border-erp-border bg-white">
                <template x-if="filtered.length === 0">
                    <p class="px-4 py-8 text-center text-sm text-slate-500">{{ __('No orders match your filter.') }}</p>
                </template>
                <template x-for="order in filtered" :key="order.value">
                    <button
                        type="button"
                        class="flex w-full items-start gap-3 border-b border-slate-100 px-4 py-3 text-left transition last:border-b-0 hover:bg-slate-50"
                        :class="selected?.value === order.value ? 'bg-erp-accent/10 ring-1 ring-inset ring-erp-accent/30' : ''"
                        @click="select(order)"
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
                    </button>
                </template>
            </div>

            <div
                x-show="selected"
                x-cloak
                class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm"
            >
                <p class="font-medium text-slate-900" x-text="selected?.order_number"></p>
                <p class="text-slate-600">
                    <span x-text="selected?.customer"></span>
                    · {{ __('Remaining') }}: <span class="font-mono font-semibold" x-text="selected?.remaining"></span>
                </p>
            </div>

            <x-admin.form-modal-actions>
                <button
                    type="button"
                    class="erp-btn-secondary"
                    @click="window.erpModalManager?.closeModal?.()"
                >{{ __('Cancel') }}</button>
                <a
                    :href="selected?.href ?? '#'"
                    class="erp-btn-primary"
                    data-erp-modal-open
                    :class="{ 'pointer-events-none opacity-50': ! selected }"
                    :aria-disabled="! selected"
                    @click="! selected && $event.preventDefault()"
                >{{ __('Continue') }}</a>
            </x-admin.form-modal-actions>
        </div>
    @endif
</x-admin.modal-form>
