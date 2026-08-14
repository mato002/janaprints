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
        $createSpecificationUrl = route('admin.crm.print-specifications.quick-create');
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
                production_destination: @js(old('production_destination', '')),
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
                if (!this.customerId || !this.selectedSpecId || !this.form.production_destination) {
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
            openCreateSpecification() {
                if (!this.customerId) {
                    window.erpModalManager?.showToast?.(@js(__('Select a customer first.')), 'error');

                    return;
                }

                if (!window.erpLookupManager) {
                    return;
                }

                const url = @js($createSpecificationUrl) + '?' + new URLSearchParams({ customer_id: this.customerId }).toString();

                window.erpLookupManager.open(url, {
                    title: @js(__('Create print specification')),
                    onSuccess: async (record) => {
                        await this.loadContext();

                        if (!record?.value) {
                            return;
                        }

                        this.selectedSpecId = String(record.value);
                        const spec = this.context?.print_specifications?.find(
                            (item) => String(item.id) === String(record.value),
                        );

                        if (spec) {
                            this.selectSpecification(spec);
                        }
                    },
                });
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
                @include('admin.sales.orders.partials.production-destination-picker', [
                    'value' => old('production_destination'),
                    'required' => true,
                ])
                @if(($fields['quotation_id']['visible'] ?? true))
                    @include('admin.sales.orders.partials.quotation-picker-field', [
                        'value' => old('quotation_id', $selectedQuotationId ?? null),
                        'required' => ($fields['quotation_id']['required'] ?? true),
                    ])
                @endif
                @include('admin.partials.form-custom-fields', ['fields' => $fields])
                <x-admin.form-modal-actions class="erp-form-modal__actions--sticky">
                    <button type="submit" class="erp-btn-primary min-h-[2.75rem] w-full sm:w-auto">{{ __('Create from quotation') }}</button>
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

                @php
                    $salesDeskLocked = request('from') === 'sales-desk'
                        && filled($selectedCustomerId ?? null)
                        && filled($selectedSpecificationId ?? null);
                    $lockedCustomer = $salesDeskLocked
                        ? $customers->firstWhere('id', (int) old('customer_id', $selectedCustomerId))
                        : null;
                @endphp

                @if ($salesDeskLocked && $lockedCustomer)
                    <input type="hidden" name="customer_id" value="{{ old('customer_id', $selectedCustomerId) }}">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Locked context') }}</p>
                        <p class="font-medium text-slate-900">{{ $lockedCustomer->company_name ?? $lockedCustomer->name }}</p>
                        <p class="mt-1 text-xs text-slate-600">{{ __('Customer is locked from the Sales Desk. Change specification below if needed.') }}</p>
                        <a
                            href="{{ route('admin.crm.customers.show', $lockedCustomer) }}"
                            class="mt-2 inline-flex text-xs font-medium text-erp-primary hover:underline"
                            data-turbo-frame="erp-main"
                        >{{ __('View Customer 360') }}</a>
                    </div>
                @else
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
                @endif

                @include('admin.sales.orders.partials.production-destination-picker', [
                    'alpineModel' => 'form.production_destination',
                    'value' => old('production_destination'),
                    'required' => true,
                ])

                <template x-if="loadingContext">
                    <p class="text-sm text-slate-400">{{ __('Loading…') }}</p>
                </template>

                <template x-if="contextError && !loadingContext">
                    <p class="text-sm text-red-600" x-text="contextError"></p>
                </template>

                <template x-if="context && !loadingContext">
                    <div class="space-y-4">
                        <div>
                            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-sm font-semibold text-slate-900">{{ __('Print specifications') }}</h3>
                                @if ($canCreateSpecification ?? false)
                                    <button
                                        type="button"
                                        class="erp-btn-secondary text-xs"
                                        x-show="customerId"
                                        @click="openCreateSpecification()"
                                    >{{ __('Create new') }}</button>
                                @endif
                            </div>
                            @if ($salesDeskLocked ?? false)
                                <p class="mb-2 text-xs text-slate-500">{{ __('Specification is pre-selected. Choose another from the list or create new if needed.') }}</p>
                            @endif
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
                                            <td colspan="6" class="py-6 text-center text-slate-500">
                                                {{ __('No active print specifications for this customer.') }}
                                            </td>
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
                                <input id="required_date" type="date" name="required_date" class="erp-input w-full min-h-[2.75rem]" min="{{ now()->toDateString() }}" x-model="form.required_date">
                                <p class="mt-1 text-xs text-slate-500">{{ __('Cannot be earlier than today.') }}</p>
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
