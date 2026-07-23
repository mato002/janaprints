<?php if (isset($component)) { $__componentOriginald3ad0f200dc20b794011e332a16c068d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3ad0f200dc20b794011e332a16c068d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal-form','data' => ['title' => __('Create invoice'),'breadcrumbs' => [
        ['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')],
        ['label' => __('Invoices'), 'url' => route('admin.invoices.index')],
        ['label' => __('Create invoice')],
    ],'maxWidth' => '3xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create invoice')),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')],
        ['label' => __('Invoices'), 'url' => route('admin.invoices.index')],
        ['label' => __('Create invoice')],
    ]),'maxWidth' => '3xl']); ?>
    <?php if (! (request()->header('Turbo-Frame') === 'erp-form-modal')): ?>
        <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Create invoice'),'description' => __('Choose a sales order with a remaining billable balance. Use the filter to narrow the list — all billable orders are shown by default.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create invoice')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Choose a sales order with a remaining billable balance. Use the filter to narrow the list — all billable orders are shown by default.'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $attributes = $__attributesOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__attributesOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $component = $__componentOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__componentOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
    <?php else: ?>
        <p class="mb-4 text-sm text-slate-600">
            <?php echo e(__('Choose a sales order with a remaining billable balance. Use the filter to narrow the list — all billable orders are shown by default.')); ?>

        </p>
    <?php endif; ?>

    <?php if($orderOptions === []): ?>
        <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'receipt-tax','title' => __('No billable sales orders found'),'description' => __('Confirm a sales order first, or check that it still has a remaining billable balance. You can also create invoices from a sales order or delivery note.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'receipt-tax','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No billable sales orders found')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Confirm a sales order first, or check that it still has a remaining billable balance. You can also create invoices from a sales order or delivery note.'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
    <?php else: ?>
        <div class="space-y-4" x-data="invoiceOrderPicker(<?php echo \Illuminate\Support\Js::from($orderOptions)->toHtml() ?>)">
            <div>
                <label for="invoice-order-filter" class="erp-label"><?php echo e(__('Sales order')); ?></label>
                <input
                    id="invoice-order-filter"
                    type="search"
                    x-model="query"
                    class="erp-input w-full"
                    placeholder="<?php echo e(__('Filter by order number or customer…')); ?>"
                    autocomplete="off"
                >
                <p class="mt-1 text-xs text-slate-500">
                    <span x-text="filtered.length"></span> <?php echo e(__('of')); ?> <?php echo e(count($orderOptions)); ?> <?php echo e(__('billable orders')); ?>

                </p>
            </div>

            <div class="max-h-80 overflow-y-auto rounded-lg border border-erp-border bg-white">
                <template x-if="filtered.length === 0">
                    <p class="px-4 py-8 text-center text-sm text-slate-500"><?php echo e(__('No orders match your filter.')); ?></p>
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
                                <?php echo e(__('Remaining')); ?>: <span x-text="order.remaining"></span>
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
                    · <?php echo e(__('Remaining')); ?>: <span class="font-mono font-semibold" x-text="selected?.remaining"></span>
                </p>
            </div>

            <?php if (isset($component)) { $__componentOriginald865c6e99253c837baa94b9ed23bdb6d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald865c6e99253c837baa94b9ed23bdb6d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-modal-actions','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-modal-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <button
                    type="button"
                    class="erp-btn-secondary"
                    @click="window.erpModalManager?.closeModal?.()"
                ><?php echo e(__('Cancel')); ?></button>
                <a
                    :href="selected?.href ?? '#'"
                    class="erp-btn-primary"
                    data-erp-modal-open
                    :class="{ 'pointer-events-none opacity-50': ! selected }"
                    :aria-disabled="! selected"
                    @click="! selected && $event.preventDefault()"
                ><?php echo e(__('Continue')); ?></a>
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
        </div>
    <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\invoices\select-order.blade.php ENDPATH**/ ?>