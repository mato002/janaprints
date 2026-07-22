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
        $contextUrl = route('admin.sales-orders.customer-order-context', ['customer' => '__CUSTOMER__']);
        $customerRouteKeys = $customers->mapWithKeys(
            fn ($customer) => [(string) $customer->id => $customer->public_id],
        );
    @endphp

    <div
        x-data="{
            tab: @js(old('entry_mode') === 'direct' ? 'direct' : ($defaultTab ?? 'quotation')),
            customerId: @js((string) old('customer_id', $selectedCustomerId ?? '')),
            customerRouteKeys: @js($customerRouteKeys),
            selectedSpecId: @js((string) old('customer_print_specification_id', $selectedSpecificationId ?? '')),
            context: null,
            contextError: null,
            loadingContext: false,
            form: {
                quantity: @js(old('quantity', '1')),
                unit_price: @js(old('unit_price', '0')),
                required_date: @js(old('required_date', '')),
                notes: @js(old('notes', '')),
                priority: @js(old('priority', 'normal')),
                fulfilment_method: @js(old('fulfilment_method', 'collection')),
                billing_type: @js(old('billing_type', '')),
            },
            get selectedSpec() {
                if (!this.context?.print_specifications || !this.selectedSpecId) {
                    return null;
                }
                return this.context.print_specifications.find(
                    (spec) => String(spec.id) === String(this.selectedSpecId),
                ) ?? null;
            },
            get canSubmit() {
                if (!this.customerId || !this.selectedSpecId) {
                    return false;
                }
                const spec = this.selectedSpec;
                if (spec?.artwork_required && !spec?.has_active_artwork) {
                    return false;
                }
                return true;
            },
            onCustomerChanged(id) {
                this.customerId = String(id ?? '');
                this.selectedSpecId = '';
                this.loadContext();
            },
            syncCustomerFromSelect() {
                const sel = this.$el.querySelector('[name=customer_id]');
                if (sel?.value) {
                    this.onCustomerChanged(sel.value);
                }
            },
            handleFieldChange(event) {
                if (event.target?.name === 'customer_id') {
                    this.onCustomerChanged(event.target.value);
                }
            },
            async loadContext() {
                if (!this.customerId) {
                    this.context = null;
                    this.contextError = null;
                    this.selectedSpecId = '';
                    return;
                }
                this.loadingContext = true;
                this.contextError = null;
                try {
                    const routeKey = this.customerRouteKeys[this.customerId] ?? this.customerId;
                    const url = @js($contextUrl).replace('__CUSTOMER__', routeKey) + '?scope=direct-order';
                    const response = await fetch(url, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });
                    if (response.ok) {
                        this.context = await response.json();
                        if (this.selectedSpecId && !this.context.print_specifications?.some(
                            (spec) => String(spec.id) === String(this.selectedSpecId),
                        )) {
                            this.selectedSpecId = '';
                        }
                    } else {
                        this.context = null;
                        this.contextError = @js(__('Unable to load customer order details. Please try again.'));
                    }
                } catch (error) {
                    this.context = null;
                    this.contextError = @js(__('Unable to load customer order details. Please try again.'));
                } finally {
                    this.loadingContext = false;
                }
            },
            selectSpecification(spec) {
                this.selectedSpecId = String(spec.id);
                this.form.quantity = String(spec.default_quantity ?? 1);
                this.form.unit_price = String(spec.default_unit_price ?? 0);
                this.form.billing_type = spec.default_billing_type ?? this.context?.billing_defaults?.billing_type ?? '';
                this.form.fulfilment_method = spec.default_fulfilment_method ?? 'collection';
            },
        }"
        x-init="
            if (customerId) {
                loadContext().then(() => {
                    if (selectedSpecId && context?.print_specifications) {
                        const spec = context.print_specifications.find((s) => String(s.id) === String(selectedSpecId));
                        if (spec) { selectSpecification(spec); }
                    }
                });
            }
        "
        @erp-lookup-changed="if ($event.detail.name === 'customer_id') { onCustomerChanged($event.detail.value) }"
        @change="handleFieldChange($event)"
        class="space-y-4"
    >
        <nav class="flex flex-wrap gap-1 border-b border-erp-border" role="tablist">
            <button type="button" role="tab" class="min-h-[2.75rem] px-3 py-2 text-sm font-medium"
                :class="tab === 'quotation' ? 'border-b-2 border-erp-accent text-erp-accent' : 'text-slate-600 hover:text-slate-900'"
                @click="tab = 'quotation'">{{ __('From Quotation') }}</button>
            <button type="button" role="tab" class="min-h-[2.75rem] px-3 py-2 text-sm font-medium"
                :class="tab === 'direct' ? 'border-b-2 border-erp-accent text-erp-accent' : 'text-slate-600 hover:text-slate-900'"
                @click="tab = 'direct'; $nextTick(() => syncCustomerFromSelect())">{{ __('Direct Order') }}</button>
        </nav>

        <div x-show="tab === 'quotation'" x-cloak>
            <x-admin.form-shell :action="route('admin.sales-orders.store')" class="space-y-4">
                @if (request('from') === 'sales-desk')
                    <input type="hidden" name="from" value="sales-desk">
                @endif
                <input type="hidden" name="entry_mode" value="quotation">
                @if(($fields['quotation_id']['visible'] ?? true))
                    <div>
                        <label class="erp-label" for="quotation_id">{{ __('Quotation') }}</label>
                        <select id="quotation_id" name="quotation_id" class="erp-select w-full min-h-[2.75rem]" @required($fields['quotation_id']['required'] ?? true)>
                            <option value="">{{ __('Select quotation') }}</option>
                            @foreach ($eligibleQuotations as $quotation)
                                <option value="{{ $quotation->id }}" @selected(old('quotation_id', $selectedQuotationId ?? null) == $quotation->id)>
                                    {{ $quotation->salesOrderConversionLabel() }}
                                </option>
                            @endforeach
                        </select>
                        @if ($eligibleQuotations->isEmpty())
                            <p class="mt-2 text-sm text-slate-500">{{ __('No accepted quotations are available. Accept a quotation first, or use Direct Order.') }}</p>
                        @elseif ($eligibleQuotations->every(fn ($quotation) => ! $quotation->isReadyForSalesOrderConversion()))
                            <p class="mt-2 text-sm text-amber-700">{{ __('Selected quotations still need approved artwork before conversion.') }}</p>
                        @endif
                    </div>
                @endif
                @include('admin.partials.form-custom-fields', ['fields' => $fields])
                <x-admin.form-modal-actions class="erp-form-modal__actions--sticky">
                    <button type="submit" class="erp-btn-primary min-h-[2.75rem] w-full sm:w-auto" @disabled($eligibleQuotations->isEmpty())>{{ __('Create from quotation') }}</button>
                </x-admin.form-modal-actions>
            </x-admin.form-shell>
        </div>

        <div x-show="tab === 'direct'" x-cloak>
            <x-admin.form-shell :action="route('admin.sales-orders.store')" class="space-y-4">
                @if (request('from') === 'sales-desk')
                    <input type="hidden" name="from" value="sales-desk">
                @endif
                <input type="hidden" name="entry_mode" value="direct">
                <input type="hidden" name="customer_print_specification_id" :value="selectedSpecId">

                <div>
                    <x-admin.lookup-select
                        name="customer_id"
                        :label="__('Customer')"
                        :options="$customers"
                        :value="old('customer_id', $selectedCustomerId)"
                        :required="true"
                        create-route="admin.crm.customers.quick-create"
                        refresh-route="admin.lookups.customers"
                        permission="crm.customers.create"
                        :modal-title="__('Create customer')"
                        option-label-key="company_name"
                        option-value-key="id"
                        select-class="erp-input w-full min-h-[2.75rem]"
                        :empty-option="true"
                        :placeholder="__('Select customer')"
                    />
                </div>

                <template x-if="loadingContext">
                    <p class="text-sm text-slate-400">{{ __('Loading…') }}</p>
                </template>

                <template x-if="contextError && !loadingContext">
                    <p class="text-sm text-red-600" x-text="contextError"></p>
                </template>

                <template x-if="customerId && !loadingContext && !contextError && context && !context.print_specifications?.length">
                    <p class="text-sm text-slate-500">{{ __('No active print specifications for this customer.') }}</p>
                </template>

                <template x-if="context && !loadingContext">
                    <div class="space-y-4">
                        <div>
                            <h3 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Print specifications') }}</h3>
                            <div class="overflow-x-auto rounded-lg border border-erp-border">
                                <table class="erp-table w-full text-sm">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Code') }}</th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Product') }}</th>
                                            <th>{{ __('Artwork version') }}</th>
                                            <th>{{ __('Price') }}</th>
                                            <th>{{ __('Last used') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="spec in context.print_specifications" :key="spec.id">
                                            <tr
                                                class="cursor-pointer hover:bg-slate-50"
                                                :class="selectedSpecId == spec.id ? 'bg-erp-accent/5' : ''"
                                                @click="selectSpecification(spec)"
                                            >
                                                <td class="font-mono text-xs whitespace-nowrap" x-text="spec.specification_code"></td>
                                                <td class="font-medium" x-text="spec.name"></td>
                                                <td x-text="spec.product_name ?? '—'"></td>
                                                <td class="text-xs whitespace-nowrap" x-text="spec.current_artwork_label ?? '—'"></td>
                                                <td class="font-mono text-xs" x-text="spec.default_unit_price ?? '—'"></td>
                                                <td class="text-xs whitespace-nowrap" x-text="spec.last_used_at ?? '—'"></td>
                                            </tr>
                                        </template>
                                        <tr x-show="!context.print_specifications?.length">
                                            <td colspan="6" class="py-6 text-center text-slate-500">{{ __('No active print specifications for this customer.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2" x-show="selectedSpec">
                            <div>
                                <label class="erp-label" for="quantity">{{ __('Quantity') }}</label>
                                <input id="quantity" type="number" name="quantity" class="erp-input w-full min-h-[2.75rem]" min="0.001" step="any" x-model="form.quantity" required>
                            </div>
                            <div>
                                <label class="erp-label" for="unit_price">{{ __('Unit price') }}</label>
                                <input id="unit_price" type="number" name="unit_price" class="erp-input w-full min-h-[2.75rem]" min="0" step="0.01" x-model="form.unit_price">
                            </div>
                            <div>
                                <label class="erp-label" for="required_date">{{ __('Required date') }}</label>
                                <input id="required_date" type="date" name="required_date" class="erp-input w-full min-h-[2.75rem]" x-model="form.required_date">
                            </div>
                            <div>
                                <label class="erp-label" for="priority">{{ __('Priority') }}</label>
                                <select id="priority" name="priority" class="erp-input w-full min-h-[2.75rem]" x-model="form.priority">
                                    @foreach ($priorities as $priority)
                                        <option value="{{ $priority->value }}">{{ ucfirst($priority->value) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="erp-label" for="fulfilment_method">{{ __('Fulfilment') }}</label>
                                <select id="fulfilment_method" name="fulfilment_method" class="erp-input w-full min-h-[2.75rem]" x-model="form.fulfilment_method">
                                    @foreach (\App\Enums\FulfilmentMethod::cases() as $method)
                                        <option value="{{ $method->value }}">{{ $method->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="erp-label" for="billing_type">{{ __('Billing type') }}</label>
                                <select id="billing_type" name="billing_type" class="erp-input w-full min-h-[2.75rem]" x-model="form.billing_type">
                                    <option value="">{{ __('Use customer default') }}</option>
                                    @foreach (\App\Enums\SalesOrderBillingType::cases() as $type)
                                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="erp-label" for="direct_notes">{{ __('Notes') }}</label>
                                <textarea id="direct_notes" name="notes" class="erp-input w-full" rows="2" x-model="form.notes"></textarea>
                            </div>
                            @if ($canSendToProduction ?? false)
                                <div class="sm:col-span-2">
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                        <input type="checkbox" name="send_to_production" value="1" class="rounded border-erp-border" @checked(old('send_to_production'))>
                                        {{ __('Send to production') }}
                                    </label>
                                    <p class="mt-1 text-xs text-slate-500">{{ __('Creates a production job card immediately. Leave unchecked to release production manually from the sales order later.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </template>

                <x-admin.form-modal-actions class="erp-form-modal__actions--sticky">
                    <button
                        type="submit"
                        class="erp-btn-primary min-h-[2.75rem] w-full sm:w-auto disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="!canSubmit"
                    >{{ __('Create direct order') }}</button>
                </x-admin.form-modal-actions>
            </x-admin.form-shell>
        </div>
    </div>
</x-admin.modal-form>
