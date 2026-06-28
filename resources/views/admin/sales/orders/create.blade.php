<x-admin.modal-form
    :title="__('New sales order')"
    :breadcrumbs="[
        ['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.index')],
        ['label' => __('New sales order')],
    ]"
    maxWidth="4xl"
>
    @php
        $fields = $formFields ?? [];
        $contextBaseUrl = url('admin/sales-orders/customers');
        $specBaseUrl = $contextBaseUrl;
    @endphp

    <div
        x-data="{
            tab: @js(old('entry_mode') === 'direct' ? 'direct' : ($defaultTab ?? 'quotation')),
            customerId: @js((string) old('customer_id', $selectedCustomerId ?? '')),
            context: null,
            loadingContext: false,
            selectedOrderId: @js((string) old('repeat_source_sales_order_id', '')),
            form: {
                inventory_item_id: @js((string) old('inventory_item_id', '')),
                quantity: @js(old('quantity', '1')),
                unit_price: @js(old('unit_price', '0')),
                required_date: @js(old('required_date', '')),
                notes: @js(old('notes', '')),
                uses_existing_artwork: @js((bool) old('uses_existing_artwork', false)),
                customer_artwork_id: @js((string) old('customer_artwork_id', '')),
            },
            async loadContext() {
                if (!this.customerId) {
                    this.context = null;
                    this.selectedOrderId = '';
                    return;
                }
                this.loadingContext = true;
                try {
                    const response = await fetch(`${@js($contextBaseUrl)}/${this.customerId}/order-context`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (response.ok) {
                        this.context = await response.json();
                    }
                } finally {
                    this.loadingContext = false;
                }
            },
            async selectPreviousOrder(orderId) {
                this.selectedOrderId = String(orderId);
                const response = await fetch(`${@js($specBaseUrl)}/${this.customerId}/order-specification/${orderId}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) {
                    return;
                }
                const spec = await response.json();
                this.form.inventory_item_id = spec.inventory_item_id ? String(spec.inventory_item_id) : '';
                this.form.quantity = String(spec.quantity ?? 1);
                this.form.unit_price = String(spec.unit_price ?? 0);
                this.form.required_date = spec.required_date ?? '';
                this.form.notes = spec.notes ?? '';
                this.form.uses_existing_artwork = Boolean(spec.uses_existing_artwork);
                this.form.customer_artwork_id = spec.customer_artwork_id ? String(spec.customer_artwork_id) : '';
            },
            clearRepeatSelection() {
                this.selectedOrderId = '';
            },
            applyFrequentProduct(product) {
                this.selectedOrderId = '';
                this.form.inventory_item_id = String(product.inventory_item_id);
            },
        }"
        x-init="if (customerId) { loadContext(); }"
        class="space-y-4"
    >
        <nav class="flex flex-wrap gap-1 border-b border-erp-border" role="tablist">
            <button
                type="button"
                role="tab"
                class="px-3 py-2 text-sm font-medium"
                :class="tab === 'quotation' ? 'border-b-2 border-erp-accent text-erp-accent' : 'text-slate-600 hover:text-slate-900'"
                @click="tab = 'quotation'"
            >{{ __('From quotation') }}</button>
            <button
                type="button"
                role="tab"
                class="px-3 py-2 text-sm font-medium"
                :class="tab === 'direct' ? 'border-b-2 border-erp-accent text-erp-accent' : 'text-slate-600 hover:text-slate-900'"
                @click="tab = 'direct'"
            >{{ __('Direct customer order') }}</button>
        </nav>

        <div x-show="tab === 'quotation'" x-cloak>
            <x-admin.form-shell :action="route('admin.sales-orders.store')" class="space-y-4">
                <input type="hidden" name="entry_mode" value="quotation">
                @if(($fields['quotation_id']['visible'] ?? true))
                    <div>
                        <label class="erp-label" for="quotation_id">{{ __('Quotation') }}</label>
                        <select id="quotation_id" name="quotation_id" class="erp-select w-full" @required($fields['quotation_id']['required'] ?? true) @disabled($fields['quotation_id']['read_only'] ?? false)>
                            <option value="">{{ __('Select quotation') }}</option>
                            @foreach ($eligibleQuotations as $quotation)
                                <option value="{{ $quotation->id }}" @selected(old('quotation_id') == $quotation->id)>
                                    {{ $quotation->quotation_number }} — {{ $quotation->customer?->company_name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($eligibleQuotations->isEmpty())
                            <p class="mt-2 text-sm text-slate-500">
                                {{ __('No quotations are ready for conversion. The quotation must be accepted, not already converted, and have approved artwork linked to it.') }}
                            </p>
                        @else
                            <p class="mt-2 text-sm text-slate-500">
                                {{ __('Only accepted quotations with approved artwork are listed.') }}
                            </p>
                        @endif
                    </div>
                @endif
                @include('admin.partials.form-custom-fields', ['fields' => $fields])
                <x-admin.form-modal-actions>
                    <button type="submit" class="erp-btn-primary" @disabled($eligibleQuotations->isEmpty())>{{ __('Create sales order') }}</button>
                </x-admin.form-modal-actions>
            </x-admin.form-shell>
        </div>

        <div x-show="tab === 'direct'" x-cloak>
            <x-admin.form-shell :action="route('admin.sales-orders.store')" class="space-y-4">
                <input type="hidden" name="entry_mode" value="direct">
                <input type="hidden" name="repeat_source_sales_order_id" :value="selectedOrderId">

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="erp-label" for="customer_id">{{ __('Customer') }}</label>
                        <select
                            id="customer_id"
                            name="customer_id"
                            class="erp-select w-full"
                            required
                            x-model="customerId"
                            @change="selectedOrderId = ''; loadContext()"
                        >
                            <option value="">{{ __('Select customer') }}</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <template x-if="loadingContext">
                    <p class="text-sm text-slate-500">{{ __('Loading customer order history…') }}</p>
                </template>

                <template x-if="context && !loadingContext">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                            <div class="rounded-lg border border-erp-border p-3 text-sm" x-show="context.frequent_products?.length">
                                <p class="mb-2 font-medium text-erp-primary">{{ __('Frequently ordered') }}</p>
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="product in context.frequent_products" :key="product.inventory_item_id">
                                        <button
                                            type="button"
                                            class="rounded border border-erp-border px-2 py-1 text-xs hover:bg-slate-50"
                                            @click="applyFrequentProduct(product)"
                                            x-text="product.item_name"
                                        ></button>
                                    </template>
                                </div>
                            </div>
                            <div class="rounded-lg border border-erp-border p-3 text-sm" x-show="context.serial_profiles?.length">
                                <p class="mb-2 font-medium text-erp-primary">{{ __('Serial profiles') }}</p>
                                <ul class="space-y-1 text-xs text-slate-600">
                                    <template x-for="profile in context.serial_profiles" :key="profile.inventory_item_id">
                                        <li>
                                            <span x-text="profile.product"></span>
                                            <span class="font-mono text-slate-500" x-text="`(${profile.serial_prefix})`"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            <div class="rounded-lg border border-erp-border p-3 text-sm" x-show="context.billing_defaults">
                                <p class="mb-2 font-medium text-erp-primary">{{ __('Billing default') }}</p>
                                <p class="text-xs text-slate-600" x-text="context.billing_defaults?.billing_type ?? '—'"></p>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-erp-border">
                            <table class="erp-table w-full text-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('Order') }}</th>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('Qty') }}</th>
                                        <th>{{ __('Required') }}</th>
                                        <th>{{ __('Artwork') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="order in context.previous_orders" :key="order.id">
                                        <tr
                                            class="cursor-pointer hover:bg-slate-50"
                                            :class="selectedOrderId == order.id ? 'bg-erp-accent/5' : ''"
                                            @click="selectPreviousOrder(order.id)"
                                        >
                                            <td class="font-mono text-xs" x-text="order.order_number"></td>
                                            <td x-text="order.product ?? '—'"></td>
                                            <td class="font-mono" x-text="order.quantity"></td>
                                            <td class="text-xs" x-text="order.required_date ?? '—'"></td>
                                            <td class="text-xs" x-text="order.artwork ?? '—'"></td>
                                            <td class="text-right">
                                                <span
                                                    class="text-xs font-medium text-erp-accent"
                                                    x-show="selectedOrderId == order.id"
                                                >{{ __('Selected') }}</span>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="!context.previous_orders?.length">
                                        <td colspan="6" class="py-6 text-center text-slate-500">{{ __('No previous orders for this customer.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="erp-label" for="inventory_item_id">{{ __('Product') }}</label>
                                <select id="inventory_item_id" name="inventory_item_id" class="erp-select w-full" x-model="form.inventory_item_id" @change="clearRepeatSelection()">
                                    <option value="">{{ __('Select product') }}</option>
                                    @foreach ($catalogueItems as $item)
                                        <option value="{{ $item->id }}">{{ $item->item_name }}@if($item->sku) ({{ $item->sku }})@endif</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="erp-label" for="quantity">{{ __('Quantity') }}</label>
                                <input id="quantity" type="number" name="quantity" class="erp-input w-full" min="0.001" step="any" x-model="form.quantity" required>
                            </div>
                            <div>
                                <label class="erp-label" for="unit_price">{{ __('Unit price') }}</label>
                                <input id="unit_price" type="number" name="unit_price" class="erp-input w-full" min="0" step="0.01" x-model="form.unit_price">
                            </div>
                            <div>
                                <label class="erp-label" for="required_date">{{ __('Required date') }}</label>
                                <input id="required_date" type="date" name="required_date" class="erp-input w-full" x-model="form.required_date">
                            </div>
                            <div class="md:col-span-2">
                                <label class="erp-label" for="direct_notes">{{ __('Notes') }}</label>
                                <textarea id="direct_notes" name="notes" class="erp-input w-full" rows="2" x-model="form.notes"></textarea>
                            </div>
                        </div>

                        <div class="rounded-lg border border-erp-border p-4 space-y-3">
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="checkbox" name="uses_existing_artwork" value="1" class="rounded border-slate-300" x-model="form.uses_existing_artwork">
                                <span>{{ __('Use existing artwork') }}</span>
                            </label>
                            <div x-show="form.uses_existing_artwork">
                                <label class="erp-label" for="customer_artwork_id">{{ __('Artwork version') }}</label>
                                <select id="customer_artwork_id" name="customer_artwork_id" class="erp-select w-full" x-model="form.customer_artwork_id">
                                    <option value="">{{ __('Select artwork') }}</option>
                                    <template x-for="art in context.artwork_library" :key="art.id">
                                        <option :value="art.id" x-text="`${art.artwork_name} (v${art.version})`"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <p class="text-xs text-slate-500" x-show="selectedOrderId">
                            {{ __('Repeat job: specifications and artwork reference will be cloned from the selected order. Adjust quantity, required date, and notes before creating.') }}
                        </p>
                    </div>
                </template>

                <x-admin.form-modal-actions>
                    <button type="submit" class="erp-btn-primary" :disabled="!customerId">{{ __('Create direct order') }}</button>
                </x-admin.form-modal-actions>
            </x-admin.form-shell>
        </div>
    </div>
</x-admin.modal-form>
