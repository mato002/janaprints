<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Invoice from order'),'breadcrumbs' => [
    ['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.dashboard')],
    ['label' => $salesOrder->order_number, 'url' => route('admin.sales-orders.show', $salesOrder)],
    ['label' => __('Create invoice')],
]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Create invoice'),'description' => $salesOrder->order_number.' — '.__('Remaining billable').': '.number_format($salesOrder->remainingInvoiceTotal(), 2)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create invoice')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($salesOrder->order_number.' — '.__('Remaining billable').': '.number_format($salesOrder->remainingInvoiceTotal(), 2))]); ?>
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

    <?php if($pendingInvoices->isNotEmpty()): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
            <ul class="text-sm">
                <?php $__currentLoopData = $pendingInvoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pendingInvoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>
                        <a href="<?php echo e(route('admin.invoices.show', $pendingInvoice)); ?>" class="text-erp-accent font-mono"><?php echo e($pendingInvoice->invoice_number); ?></a>
                        <span class="text-slate-500"> — <?php echo e($pendingInvoice->invoice_type->label()); ?> <?php echo e(number_format($pendingInvoice->total_amount, 2)); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
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

    <form method="POST" action="<?php echo e(route('admin.invoices.store-from-sales-order', $salesOrder)); ?>" class="space-y-6 max-w-2xl" x-data="{
        billingType: '<?php echo e(old('invoice_type', 'standard')); ?>',
        eligibility: <?php echo \Illuminate\Support\Js::from($billingEligibilityByType)->toHtml() ?>,
        selectedEligibility() { return this.eligibility[this.billingType] ?? { eligible: true, blockers: [] }; }
    }">
        <?php echo csrf_field(); ?>

        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'border-amber-200 bg-amber-50','xShow' => '! selectedEligibility().eligible','xCloak' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'border-amber-200 bg-amber-50','x-show' => '! selectedEligibility().eligible','x-cloak' => true]); ?>
            <p class="mb-2 text-sm font-medium text-amber-950"><?php echo e(__('This billing type is blocked')); ?></p>
            <ul class="list-disc ps-5 text-sm text-amber-900">
                <template x-for="blocker in selectedEligibility().blockers" :key="blocker">
                    <li x-text="blocker"></li>
                </template>
            </ul>
            <?php if($salesOrder->jobCard): ?>
                <p class="mt-3 text-sm text-amber-900">
                    <a href="<?php echo e(route('admin.production.job-cards.show', $salesOrder->jobCard)); ?>" class="font-medium text-erp-accent underline" data-turbo-frame="_top"><?php echo e(__('Open production job')); ?></a>
                    <?php echo e(__('to post finished goods, or complete dispatch/collection on the job.')); ?>

                </p>
            <?php endif; ?>
            <p class="mt-2 text-sm text-amber-900" x-show="eligibility.deposit?.eligible || eligibility.progress?.eligible">
                <?php echo e(__('To bill before production finishes, switch billing type to Deposit or Progress billing.')); ?>

            </p>
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
            <div class="mt-4">
                <label class="erp-label"><?php echo e(__('Notes')); ?></label>
                <textarea name="notes" rows="2" class="erp-input w-full"><?php echo e(old('notes')); ?></textarea>
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

        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['xShow' => 'billingType === \'partial\'','xCloak' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-show' => 'billingType === \'partial\'','x-cloak' => true]); ?>
            <h3 class="font-medium mb-3"><?php echo e(__('Lines')); ?></h3>
            <?php $__currentLoopData = $salesOrder->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex flex-wrap items-center gap-3 py-2 border-t border-erp-border text-sm">
                    <input type="checkbox" name="lines[<?php echo e($index); ?>][selected]" value="1" class="rounded">
                    <input type="hidden" name="lines[<?php echo e($index); ?>][sales_order_item_id]" value="<?php echo e($item->id); ?>">
                    <span class="flex-1 font-medium"><?php echo e($item->item_name); ?></span>
                    <span class="text-slate-500"><?php echo e(__('Max')); ?> <?php echo e($item->quantity); ?></span>
                    <input type="number" name="lines[<?php echo e($index); ?>][quantity]" value="<?php echo e($item->quantity); ?>" min="0.001" step="0.001" class="erp-input w-24">
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

        <div class="space-y-2">
            <button
                type="submit"
                class="erp-btn-primary disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="! selectedEligibility().eligible"
                :title="selectedEligibility().eligible ? '' : selectedEligibility().blockers.join(' ')"
            ><?php echo e(__('Create invoice')); ?></button>
            <p class="text-sm text-slate-500" x-show="! selectedEligibility().eligible" x-cloak>
                <?php echo e(__('Create invoice is disabled until the blockers above are resolved, or you choose a billing type that is allowed at this stage.')); ?>

            </p>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\invoices\create-from-order.blade.php ENDPATH**/ ?>