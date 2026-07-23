<?php if (isset($component)) { $__componentOriginald3ad0f200dc20b794011e332a16c068d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3ad0f200dc20b794011e332a16c068d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal-form','data' => ['title' => __('Record payment'),'maxWidth' => '2xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Record payment')),'maxWidth' => '2xl']); ?>
    <form method="POST" action="<?php echo e(route('admin.payments.store')); ?>" class="space-y-4" data-erp-desk-form>
        <?php echo csrf_field(); ?>
        <input type="hidden" name="from" value="sales-desk">
        <input type="hidden" name="customer_id" value="<?php echo e($customer?->id ?? old('customer_id')); ?>">
        <?php if($salesOrder): ?>
            <input type="hidden" name="sales_order_id" value="<?php echo e($salesOrder->id); ?>">
        <?php endif; ?>

        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
            <p class="font-medium text-slate-900"><?php echo e($customer?->company_name ?? __('Customer')); ?></p>
            <?php if($salesOrder): ?>
                <p class="text-xs text-slate-600"><?php echo e(__('Order')); ?> <?php echo e($salesOrder->order_number); ?> · <?php echo e(number_format((float) $salesOrder->total_amount, 2)); ?></p>
            <?php endif; ?>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="erp-label"><?php echo e(__('Payment date')); ?></label>
                <input type="date" name="payment_date" value="<?php echo e(old('payment_date', now()->toDateString())); ?>" class="erp-input w-full" required>
            </div>
            <div>
                <label class="erp-label"><?php echo e(__('Method')); ?></label>
                <select name="payment_method" class="erp-input w-full" required>
                    <?php $__currentLoopData = App\Enums\CustomerPaymentMethod::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($method->value); ?>" <?php if(old('payment_method', 'cash') === $method->value): echo 'selected'; endif; ?>><?php echo e($method->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="erp-label"><?php echo e(__('Amount')); ?></label>
                <input type="number" name="amount" step="0.01" min="0.01" class="erp-input w-full" value="<?php echo e(old('amount', $defaultAmount ?? null)); ?>" required>
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_deposit" value="1" <?php if(old('is_deposit')): echo 'checked'; endif; ?>>
                    <?php echo e(__('Customer deposit')); ?>

                </label>
            </div>
            <div>
                <label class="erp-label"><?php echo e(__('M-Pesa reference')); ?></label>
                <input type="text" name="mpesa_reference" class="erp-input w-full" value="<?php echo e(old('mpesa_reference')); ?>">
            </div>
            <div>
                <label class="erp-label"><?php echo e(__('Bank reference')); ?></label>
                <input type="text" name="bank_reference" class="erp-input w-full" value="<?php echo e(old('bank_reference')); ?>">
            </div>
        </div>

        <?php if($customer && count($openInvoices ?? []) > 0): ?>
            <div class="rounded-lg border border-erp-border p-3">
                <h3 class="mb-2 text-sm font-medium"><?php echo e(__('Allocate to invoices')); ?></h3>
                <?php $__currentLoopData = $openInvoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex flex-wrap items-center gap-3 py-2 border-t border-erp-border text-sm first:border-t-0">
                        <input type="hidden" name="allocations[<?php echo e($index); ?>][customer_invoice_id]" value="<?php echo e($inv->id); ?>">
                        <span class="flex-1 font-mono"><?php echo e($inv->invoice_number); ?></span>
                        <span class="text-slate-500"><?php echo e(number_format($inv->balance_due, 2)); ?></span>
                        <input type="number" name="allocations[<?php echo e($index); ?>][amount]" step="0.01" min="0" max="<?php echo e($inv->balance_due); ?>" class="erp-input w-28" placeholder="0.00">
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

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
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payments.post')): ?>
                <button type="submit" name="post_now" value="1" class="erp-btn-primary"><?php echo e(__('Record payment')); ?></button>
            <?php endif; ?>
            <button type="submit" class="erp-btn-secondary"><?php echo e(__('Save draft')); ?></button>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\desk\payment-modal.blade.php ENDPATH**/ ?>