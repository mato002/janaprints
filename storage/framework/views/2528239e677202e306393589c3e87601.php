<?php if (isset($component)) { $__componentOriginald3ad0f200dc20b794011e332a16c068d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3ad0f200dc20b794011e332a16c068d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal-form','data' => ['title' => __('Create invoice'),'maxWidth' => '2xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create invoice')),'maxWidth' => '2xl']); ?>
    <form method="POST" action="<?php echo e(route('admin.invoices.store-from-sales-order', $salesOrder)); ?>" class="space-y-4" data-erp-desk-form x-data="{
        billingType: '<?php echo e(old('invoice_type', 'standard')); ?>',
        eligibility: <?php echo \Illuminate\Support\Js::from($billingEligibilityByType)->toHtml() ?>,
        selectedEligibility() { return this.eligibility[this.billingType] ?? { eligible: true, blockers: [] }; }
    }">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="from" value="sales-desk">

        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
            <p class="font-medium text-slate-900"><?php echo e($salesOrder->order_number); ?></p>
            <p class="text-xs text-slate-600"><?php echo e($salesOrder->customer?->company_name); ?> · <?php echo e(__('Remaining billable')); ?>: <?php echo e(number_format($salesOrder->remainingInvoiceTotal(), 2)); ?></p>
        </div>

        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" x-show="! selectedEligibility().eligible" x-cloak>
            <template x-for="blocker in selectedEligibility().blockers" :key="blocker">
                <p x-text="blocker"></p>
            </template>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="erp-label"><?php echo e(__('Billing type')); ?></label>
                <select name="invoice_type" class="erp-input w-full" x-model="billingType" required>
                    <option value="standard"><?php echo e(__('Full invoice')); ?></option>
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
                <input type="date" name="due_date" value="<?php echo e(old('due_date')); ?>" class="erp-input w-full">
            </div>
            <div x-show="billingType === 'deposit'" x-cloak>
                <label class="erp-label"><?php echo e(__('Deposit amount')); ?></label>
                <input type="number" name="deposit_amount" step="0.01" min="0.01" class="erp-input w-full" value="<?php echo e(old('deposit_amount')); ?>">
            </div>
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
            <button type="submit" class="erp-btn-primary"><?php echo e(__('Create invoice')); ?></button>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\desk\invoice-modal.blade.php ENDPATH**/ ?>