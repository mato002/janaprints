<?php
    $description = $salesOrder->order_number.' — '.__('Remaining billable').': '.number_format($salesOrder->remainingInvoiceTotal(), 2);
?>

<?php if (isset($component)) { $__componentOriginald3ad0f200dc20b794011e332a16c068d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3ad0f200dc20b794011e332a16c068d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal-form','data' => ['title' => __('Create invoice'),'breadcrumbs' => [
        ['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.dashboard')],
        ['label' => $salesOrder->order_number, 'url' => route('admin.sales-orders.show', $salesOrder)],
        ['label' => __('Create invoice')],
    ],'maxWidth' => '2xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create invoice')),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.dashboard')],
        ['label' => $salesOrder->order_number, 'url' => route('admin.sales-orders.show', $salesOrder)],
        ['label' => __('Create invoice')],
    ]),'maxWidth' => '2xl']); ?>
    <?php if (! (request()->header('Turbo-Frame') === 'erp-form-modal')): ?>
        <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Create invoice'),'description' => $description]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create invoice')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($description)]); ?>
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
        <p class="mb-4 text-sm text-slate-600"><?php echo e($description); ?></p>
    <?php endif; ?>

    <?php if($pendingInvoices->isNotEmpty()): ?>
        <div class="mb-4 rounded-lg border border-erp-border bg-slate-50 px-4 py-3">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Existing invoices')); ?></p>
            <ul class="space-y-1 text-sm">
                <?php $__currentLoopData = $pendingInvoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pendingInvoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>
                        <a href="<?php echo e(route('admin.invoices.show', $pendingInvoice)); ?>" class="font-mono text-erp-accent hover:underline" data-turbo-frame="erp-main"><?php echo e($pendingInvoice->invoice_number); ?></a>
                        <span class="text-slate-500"> — <?php echo e($pendingInvoice->invoice_type->label()); ?> <?php echo e(number_format($pendingInvoice->total_amount, 2)); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form
        method="POST"
        action="<?php echo e(route('admin.invoices.store-from-sales-order', $salesOrder)); ?>"
        class="space-y-4"
        x-data="{
            billingType: '<?php echo e(old('invoice_type', 'standard')); ?>',
            eligibility: <?php echo \Illuminate\Support\Js::from($billingEligibilityByType)->toHtml() ?>,
            selectedEligibility() { return this.eligibility[this.billingType] ?? { eligible: true, blockers: [] }; }
        }"
    >
        <?php echo csrf_field(); ?>
        <?php echo $__env->make('admin.partials.modal-validation-alert', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" x-show="! selectedEligibility().eligible" x-cloak>
            <p class="mb-2 font-medium text-amber-950"><?php echo e(__('This billing type is blocked')); ?></p>
            <ul class="list-disc ps-5">
                <template x-for="blocker in selectedEligibility().blockers" :key="blocker">
                    <li x-text="blocker"></li>
                </template>
            </ul>
            <?php if($salesOrder->jobCard): ?>
                <p class="mt-3">
                    <a href="<?php echo e(route('admin.production.job-cards.show', $salesOrder->jobCard)); ?>" class="font-medium text-erp-accent underline" data-turbo-frame="erp-main"><?php echo e(__('Open production job')); ?></a>
                    <?php echo e(__('to post finished goods, or complete dispatch/collection on the job.')); ?>

                </p>
            <?php endif; ?>
            <p class="mt-2" x-show="eligibility.deposit?.eligible || eligibility.progress?.eligible">
                <?php echo e(__('To bill before production finishes, switch billing type to Deposit or Progress billing.')); ?>

            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="erp-label"><?php echo e(__('Billing type')); ?></label>
                <select name="invoice_type" class="erp-input w-full" x-model="billingType" required>
                    <option value="standard"><?php echo e(__('Full invoice')); ?></option>
                    <option value="partial"><?php echo e(__('Partial (selected lines)')); ?></option>
                    <option value="deposit"><?php echo e(__('Deposit')); ?></option>
                    <option value="progress"><?php echo e(__('Progress billing')); ?></option>
                </select>
            </div>
            <div>
                <label class="erp-label"><?php echo e(__('Invoice date')); ?></label>
                <input type="date" name="invoice_date" value="<?php echo e(old('invoice_date', now()->toDateString())); ?>" class="erp-input w-full" required>
            </div>
            <div>
                <label class="erp-label"><?php echo e(__('Due date')); ?></label>
                <input type="date" name="due_date" value="<?php echo e(old('due_date', $salesOrder->payment_terms_days ? now()->addDays((int) $salesOrder->payment_terms_days)->toDateString() : '')); ?>" class="erp-input w-full">
            </div>
            <div x-show="billingType === 'progress'" x-cloak>
                <label class="erp-label"><?php echo e(__('Progress %')); ?></label>
                <input type="number" name="billing_percent" min="1" max="100" step="0.01" class="erp-input w-full" placeholder="30" :required="billingType === 'progress'">
            </div>
            <div x-show="billingType === 'deposit'" x-cloak>
                <label class="erp-label"><?php echo e(__('Deposit amount')); ?></label>
                <input type="number" name="deposit_amount" min="0.01" step="0.01" class="erp-input w-full" :required="billingType === 'deposit'">
            </div>
        </div>

        <div>
            <label class="erp-label"><?php echo e(__('Notes')); ?></label>
            <textarea name="notes" rows="2" class="erp-input w-full"><?php echo e(old('notes')); ?></textarea>
        </div>

        <div class="rounded-lg border border-erp-border p-3" x-show="billingType === 'partial'" x-cloak>
            <h3 class="mb-3 text-sm font-medium"><?php echo e(__('Lines')); ?></h3>
            <?php $__currentLoopData = $salesOrder->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex flex-wrap items-center gap-3 border-t border-erp-border py-2 text-sm">
                    <input type="checkbox" name="lines[<?php echo e($index); ?>][selected]" value="1" class="rounded">
                    <input type="hidden" name="lines[<?php echo e($index); ?>][sales_order_item_id]" value="<?php echo e($item->id); ?>">
                    <span class="flex-1 font-medium"><?php echo e($item->item_name); ?></span>
                    <span class="text-slate-500"><?php echo e(__('Max')); ?> <?php echo e($item->quantity); ?></span>
                    <input type="number" name="lines[<?php echo e($index); ?>][quantity]" value="<?php echo e($item->quantity); ?>" min="0.001" step="0.001" class="erp-input w-24">
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                type="submit"
                class="erp-btn-primary disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="! selectedEligibility().eligible"
                :title="selectedEligibility().eligible ? '' : selectedEligibility().blockers.join(' ')"
            ><?php echo e(__('Create invoice')); ?></button>
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

        <p class="text-sm text-slate-500" x-show="! selectedEligibility().eligible" x-cloak>
            <?php echo e(__('Create invoice is disabled until the blockers above are resolved, or you choose a billing type that is allowed at this stage.')); ?>

        </p>
    </form>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\invoices\create-from-order.blade.php ENDPATH**/ ?>