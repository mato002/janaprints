<?php if (isset($component)) { $__componentOriginald3ad0f200dc20b794011e332a16c068d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3ad0f200dc20b794011e332a16c068d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal-form','data' => ['title' => __('New sales order'),'breadcrumbs' => [
        ['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.index')],
        ['label' => __('New sales order')],
    ],'maxWidth' => '4xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('New sales order')),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.index')],
        ['label' => __('New sales order')],
    ]),'maxWidth' => '4xl']); ?>
    <?php
        $fields = $formFields ?? [];
        $contextUrl = route('admin.sales-orders.customer-order-context', ['customer' => '__CUSTOMER__']);
        $createSpecificationUrl = route('admin.crm.print-specifications.quick-create');
        $customerRouteKeys = $customers->mapWithKeys(
            fn ($customer) => [(string) $customer->id => $customer->public_id],
        );
    ?>

    <div
        x-data="{
            tab: <?php echo \Illuminate\Support\Js::from(old('entry_mode') === 'direct' ? 'direct' : ($defaultTab ?? 'quotation'))->toHtml() ?>,
            customerId: <?php echo \Illuminate\Support\Js::from((string) old('customer_id', $selectedCustomerId ?? ''))->toHtml() ?>,
            customerRouteKeys: <?php echo \Illuminate\Support\Js::from($customerRouteKeys)->toHtml() ?>,
            selectedSpecId: <?php echo \Illuminate\Support\Js::from((string) old('customer_print_specification_id', $selectedSpecificationId ?? ''))->toHtml() ?>,
            context: null,
            contextError: null,
            loadingContext: false,
            form: {
                quantity: <?php echo \Illuminate\Support\Js::from(old('quantity', '1'))->toHtml() ?>,
                unit_price: <?php echo \Illuminate\Support\Js::from(old('unit_price', '0'))->toHtml() ?>,
                required_date: <?php echo \Illuminate\Support\Js::from(old('required_date', ''))->toHtml() ?>,
                notes: <?php echo \Illuminate\Support\Js::from(old('notes', ''))->toHtml() ?>,
                priority: <?php echo \Illuminate\Support\Js::from(old('priority', 'normal'))->toHtml() ?>,
                fulfilment_method: <?php echo \Illuminate\Support\Js::from(old('fulfilment_method', 'collection'))->toHtml() ?>,
                billing_type: <?php echo \Illuminate\Support\Js::from(old('billing_type', ''))->toHtml() ?>,
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
                    const url = <?php echo \Illuminate\Support\Js::from($contextUrl)->toHtml() ?>.replace('__CUSTOMER__', routeKey) + '?scope=direct-order';
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
                        this.contextError = <?php echo \Illuminate\Support\Js::from(__('Unable to load customer order details. Please try again.'))->toHtml() ?>;
                    }
                } catch (error) {
                    this.context = null;
                    this.contextError = <?php echo \Illuminate\Support\Js::from(__('Unable to load customer order details. Please try again.'))->toHtml() ?>;
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
                    window.erpModalManager?.showToast?.(<?php echo \Illuminate\Support\Js::from(__('Select a customer first.'))->toHtml() ?>, 'error');

                    return;
                }

                if (!window.erpLookupManager) {
                    return;
                }

                const url = <?php echo \Illuminate\Support\Js::from($createSpecificationUrl)->toHtml() ?> + '?' + new URLSearchParams({ customer_id: this.customerId }).toString();

                window.erpLookupManager.open(url, {
                    title: <?php echo \Illuminate\Support\Js::from(__('Create print specification'))->toHtml() ?>,
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
                @click="tab = 'quotation'"><?php echo e(__('From Quotation')); ?></button>
            <button type="button" role="tab" class="min-h-[2.75rem] px-3 py-2 text-sm font-medium"
                :class="tab === 'direct' ? 'border-b-2 border-erp-accent text-erp-accent' : 'text-slate-600 hover:text-slate-900'"
                @click="tab = 'direct'; $nextTick(() => syncCustomerFromSelect())"><?php echo e(__('Direct Order')); ?></button>
        </nav>

        <div x-show="tab === 'quotation'" x-cloak>
            <?php if (isset($component)) { $__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-shell','data' => ['action' => route('admin.sales-orders.store'),'class' => 'space-y-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.sales-orders.store')),'class' => 'space-y-4']); ?>
                <?php if(request('from') === 'sales-desk'): ?>
                    <input type="hidden" name="from" value="sales-desk">
                <?php endif; ?>
                <input type="hidden" name="entry_mode" value="quotation">
                <?php if(($fields['quotation_id']['visible'] ?? true)): ?>
                    <?php echo $__env->make('admin.sales.orders.partials.quotation-picker-field', [
                        'value' => old('quotation_id', $selectedQuotationId ?? null),
                        'required' => ($fields['quotation_id']['required'] ?? true),
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endif; ?>
                <?php echo $__env->make('admin.partials.form-custom-fields', ['fields' => $fields], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php if (isset($component)) { $__componentOriginald865c6e99253c837baa94b9ed23bdb6d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald865c6e99253c837baa94b9ed23bdb6d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-modal-actions','data' => ['class' => 'erp-form-modal__actions--sticky']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-modal-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'erp-form-modal__actions--sticky']); ?>
                    <button type="submit" class="erp-btn-primary min-h-[2.75rem] w-full sm:w-auto"><?php echo e(__('Create from quotation')); ?></button>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald865c6e99253c837baa94b9ed23bdb6d)): ?>
<?php $attributes = $__attributesOriginald865c6e99253c837baa94b9ed23bdb6d; ?>
<?php unset($__attributesOriginald865c6e99253c837baa94b9ed23bdb6d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald865c6e99253c837baa94b9ed23bdb6d)): ?>
<?php $component = $__componentOriginald865c6e99253c837baa94b9ed23bdb6d; ?>
<?php unset($__componentOriginald865c6e99253c837baa94b9ed23bdb6d); ?>
<?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d)): ?>
<?php $attributes = $__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d; ?>
<?php unset($__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d)): ?>
<?php $component = $__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d; ?>
<?php unset($__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d); ?>
<?php endif; ?>
        </div>

        <div x-show="tab === 'direct'" x-cloak>
            <?php if (isset($component)) { $__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-shell','data' => ['action' => route('admin.sales-orders.store'),'class' => 'space-y-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.sales-orders.store')),'class' => 'space-y-4']); ?>
                <?php if(request('from') === 'sales-desk'): ?>
                    <input type="hidden" name="from" value="sales-desk">
                <?php endif; ?>
                <input type="hidden" name="entry_mode" value="direct">
                <input type="hidden" name="customer_print_specification_id" :value="selectedSpecId">

                <?php
                    $salesDeskLocked = request('from') === 'sales-desk'
                        && filled($selectedCustomerId ?? null)
                        && filled($selectedSpecificationId ?? null);
                    $lockedCustomer = $salesDeskLocked
                        ? $customers->firstWhere('id', (int) old('customer_id', $selectedCustomerId))
                        : null;
                ?>

                <?php if($salesDeskLocked && $lockedCustomer): ?>
                    <input type="hidden" name="customer_id" value="<?php echo e(old('customer_id', $selectedCustomerId)); ?>">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e(__('Locked context')); ?></p>
                        <p class="font-medium text-slate-900"><?php echo e($lockedCustomer->company_name ?? $lockedCustomer->name); ?></p>
                        <p class="mt-1 text-xs text-slate-600"><?php echo e(__('Customer is locked from the Sales Desk. Change specification below if needed.')); ?></p>
                        <a
                            href="<?php echo e(route('admin.crm.customers.show', $lockedCustomer)); ?>"
                            class="mt-2 inline-flex text-xs font-medium text-erp-primary hover:underline"
                            data-turbo-frame="erp-main"
                        ><?php echo e(__('View Customer 360')); ?></a>
                    </div>
                <?php else: ?>
                    <div>
                        <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'customer_id','label' => __('Customer'),'options' => $customers,'value' => old('customer_id', $selectedCustomerId),'required' => true,'createRoute' => 'admin.crm.customers.quick-create','refreshRoute' => 'admin.lookups.customers','permission' => 'crm.customers.create','modalTitle' => __('Create customer'),'optionLabelKey' => 'company_name','optionValueKey' => 'id','selectClass' => 'erp-input w-full min-h-[2.75rem]','emptyOption' => true,'placeholder' => __('Select customer')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'customer_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Customer')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($customers),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('customer_id', $selectedCustomerId)),'required' => true,'create-route' => 'admin.crm.customers.quick-create','refresh-route' => 'admin.lookups.customers','permission' => 'crm.customers.create','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create customer')),'option-label-key' => 'company_name','option-value-key' => 'id','select-class' => 'erp-input w-full min-h-[2.75rem]','empty-option' => true,'placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Select customer'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $attributes = $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $component = $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
                    </div>
                <?php endif; ?>

                <template x-if="loadingContext">
                    <p class="text-sm text-slate-400"><?php echo e(__('Loading…')); ?></p>
                </template>

                <template x-if="contextError && !loadingContext">
                    <p class="text-sm text-red-600" x-text="contextError"></p>
                </template>

                <template x-if="context && !loadingContext">
                    <div class="space-y-4">
                        <div>
                            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-sm font-semibold text-slate-900"><?php echo e(__('Print specifications')); ?></h3>
                                <?php if($canCreateSpecification ?? false): ?>
                                    <button
                                        type="button"
                                        class="erp-btn-secondary text-xs"
                                        x-show="customerId"
                                        @click="openCreateSpecification()"
                                    ><?php echo e(__('Create new')); ?></button>
                                <?php endif; ?>
                            </div>
                            <?php if($salesDeskLocked ?? false): ?>
                                <p class="mb-2 text-xs text-slate-500"><?php echo e(__('Specification is pre-selected. Choose another from the list or create new if needed.')); ?></p>
                            <?php endif; ?>
                            <div class="overflow-x-auto rounded-lg border border-erp-border">
                                <table class="erp-table w-full text-sm">
                                    <thead>
                                        <tr>
                                            <th><?php echo e(__('Code')); ?></th>
                                            <th><?php echo e(__('Name')); ?></th>
                                            <th><?php echo e(__('Product')); ?></th>
                                            <th><?php echo e(__('Artwork version')); ?></th>
                                            <th><?php echo e(__('Price')); ?></th>
                                            <th><?php echo e(__('Last used')); ?></th>
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
                                                <?php echo e(__('No active print specifications for this customer.')); ?>

                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2" x-show="selectedSpec">
                            <div>
                                <label class="erp-label" for="quantity"><?php echo e(__('Quantity')); ?></label>
                                <input id="quantity" type="number" name="quantity" class="erp-input w-full min-h-[2.75rem]" min="0.001" step="any" x-model="form.quantity" required>
                            </div>
                            <div>
                                <label class="erp-label" for="unit_price"><?php echo e(__('Unit price')); ?></label>
                                <input id="unit_price" type="number" name="unit_price" class="erp-input w-full min-h-[2.75rem]" min="0" step="0.01" x-model="form.unit_price">
                            </div>
                            <div>
                                <label class="erp-label" for="required_date"><?php echo e(__('Required date')); ?></label>
                                <input id="required_date" type="date" name="required_date" class="erp-input w-full min-h-[2.75rem]" x-model="form.required_date">
                            </div>
                            <div>
                                <label class="erp-label" for="priority"><?php echo e(__('Priority')); ?></label>
                                <select id="priority" name="priority" class="erp-input w-full min-h-[2.75rem]" x-model="form.priority">
                                    <?php $__currentLoopData = $priorities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($priority->value); ?>"><?php echo e(ucfirst($priority->value)); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div>
                                <label class="erp-label" for="fulfilment_method"><?php echo e(__('Fulfilment')); ?></label>
                                <select id="fulfilment_method" name="fulfilment_method" class="erp-input w-full min-h-[2.75rem]" x-model="form.fulfilment_method">
                                    <?php $__currentLoopData = \App\Enums\FulfilmentMethod::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($method->value); ?>"><?php echo e($method->label()); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div>
                                <label class="erp-label" for="billing_type"><?php echo e(__('Billing type')); ?></label>
                                <select id="billing_type" name="billing_type" class="erp-input w-full min-h-[2.75rem]" x-model="form.billing_type">
                                    <option value=""><?php echo e(__('Use customer default')); ?></option>
                                    <?php $__currentLoopData = \App\Enums\SalesOrderBillingType::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($type->value); ?>"><?php echo e($type->label()); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="erp-label" for="direct_notes"><?php echo e(__('Notes')); ?></label>
                                <textarea id="direct_notes" name="notes" class="erp-input w-full" rows="2" x-model="form.notes"></textarea>
                            </div>
                            <?php if($canSendToProduction ?? false): ?>
                                <div class="sm:col-span-2">
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                        <input type="checkbox" name="send_to_production" value="1" class="rounded border-erp-border" <?php if(old('send_to_production')): echo 'checked'; endif; ?>>
                                        <?php echo e(__('Send to production')); ?>

                                    </label>
                                    <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Creates a production job card immediately. Leave unchecked to release production manually from the sales order later.')); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </template>

                <?php if (isset($component)) { $__componentOriginald865c6e99253c837baa94b9ed23bdb6d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald865c6e99253c837baa94b9ed23bdb6d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-modal-actions','data' => ['class' => 'erp-form-modal__actions--sticky']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-modal-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'erp-form-modal__actions--sticky']); ?>
                    <button
                        type="submit"
                        class="erp-btn-primary min-h-[2.75rem] w-full sm:w-auto disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="!canSubmit"
                    ><?php echo e(__('Create direct order')); ?></button>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald865c6e99253c837baa94b9ed23bdb6d)): ?>
<?php $attributes = $__attributesOriginald865c6e99253c837baa94b9ed23bdb6d; ?>
<?php unset($__attributesOriginald865c6e99253c837baa94b9ed23bdb6d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald865c6e99253c837baa94b9ed23bdb6d)): ?>
<?php $component = $__componentOriginald865c6e99253c837baa94b9ed23bdb6d; ?>
<?php unset($__componentOriginald865c6e99253c837baa94b9ed23bdb6d); ?>
<?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d)): ?>
<?php $attributes = $__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d; ?>
<?php unset($__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d)): ?>
<?php $component = $__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d; ?>
<?php unset($__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d); ?>
<?php endif; ?>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald3ad0f200dc20b794011e332a16c068d)): ?>
<?php $attributes = $__attributesOriginald3ad0f200dc20b794011e332a16c068d; ?>
<?php unset($__attributesOriginald3ad0f200dc20b794011e332a16c068d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald3ad0f200dc20b794011e332a16c068d)): ?>
<?php $component = $__componentOriginald3ad0f200dc20b794011e332a16c068d; ?>
<?php unset($__componentOriginald3ad0f200dc20b794011e332a16c068d); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\orders\create.blade.php ENDPATH**/ ?>