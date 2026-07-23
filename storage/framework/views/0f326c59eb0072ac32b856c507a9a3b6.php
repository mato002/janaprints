<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Record payment')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Record customer payment')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Record customer payment'))]); ?>
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

    <form method="POST" action="<?php echo e(route('admin.payments.store')); ?>" class="max-w-3xl space-y-6">
        <?php echo csrf_field(); ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="erp-label"><?php echo e(__('Customer')); ?></label>
                    <?php
                        $paymentCreateUrl = route('admin.payments.create');
                        $invoiceIdQuery = $sourceInvoice ? '&invoice_id='.$sourceInvoice->id : '';
                    ?>
                    <select name="customer_id" class="erp-input w-full" required onchange="if(this.value) window.location='<?php echo e($paymentCreateUrl); ?>?customer_id='+this.value+'<?php echo e($invoiceIdQuery); ?>'">
                        <option value=""><?php echo e(__('Select customer')); ?></option>
                        <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c->id); ?>" <?php if(old('customer_id', $customer?->id) == $c->id): echo 'selected'; endif; ?>><?php echo e($c->company_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
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
                    <input type="number" name="amount" step="0.01" min="0.01" class="erp-input w-full" value="<?php echo e(old('amount', $sourceInvoice?->balance_due)); ?>" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_deposit" value="1" <?php if(old('is_deposit')): echo 'checked'; endif; ?>>
                        <?php echo e(__('Customer deposit')); ?>

                    </label>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Bank reference')); ?></label>
                    <input type="text" name="bank_reference" class="erp-input w-full" value="<?php echo e(old('bank_reference')); ?>">
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('M-Pesa reference')); ?></label>
                    <input type="text" name="mpesa_reference" class="erp-input w-full" value="<?php echo e(old('mpesa_reference')); ?>">
                </div>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>

        <?php if($customer && count($openInvoices) > 0): ?>
            <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <h3 class="font-medium mb-3"><?php echo e(__('Allocate to invoices')); ?></h3>
                <?php $__currentLoopData = $openInvoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $allocationDefault = old("allocations.{$index}.amount");
                        if ($allocationDefault === null && $sourceInvoice?->id === $inv->id) {
                            $allocationDefault = $sourceInvoice->balance_due;
                        }
                    ?>
                    <div class="flex flex-wrap items-center gap-3 py-2 border-t border-erp-border text-sm">
                        <input type="hidden" name="allocations[<?php echo e($index); ?>][customer_invoice_id]" value="<?php echo e($inv->id); ?>">
                        <span class="flex-1 font-mono"><?php echo e($inv->invoice_number); ?></span>
                        <span class="text-slate-500"><?php echo e(__('Due')); ?> <?php echo e(number_format($inv->balance_due, 2)); ?></span>
                        <input type="number" name="allocations[<?php echo e($index); ?>][amount]" step="0.01" min="0" max="<?php echo e($inv->balance_due); ?>" class="erp-input w-32" placeholder="0.00" value="<?php echo e($allocationDefault); ?>">
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
        <?php endif; ?>

        <div class="flex flex-wrap gap-2">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payments.post')): ?>
                <button type="submit" name="post_now" value="1" class="erp-btn-primary"><?php echo e(__('Record payment')); ?></button>
            <?php endif; ?>
            <button type="submit" class="erp-btn-secondary"><?php echo e(__('Save draft only')); ?></button>
        </div>
    </form>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\payments\create.blade.php ENDPATH**/ ?>