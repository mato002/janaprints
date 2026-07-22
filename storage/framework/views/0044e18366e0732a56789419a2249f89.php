<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('New Bank Statement'),'breadcrumbs' => [['label' => __('Bank Reconciliation'), 'url' => route('admin.accounting.bank.reconciliation.index')], ['label' => __('New')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('New Bank Statement'),'description' => __('Create a statement and optionally import lines')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('New Bank Statement')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create a statement and optionally import lines'))]); ?>
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
        <form method="POST" action="<?php echo e(route('admin.accounting.bank.reconciliation.store')); ?>" class="space-y-4" x-data="{ lineCount: 1 }">
            <?php echo csrf_field(); ?>
            <div class="grid gap-4 sm:grid-cols-2 max-w-3xl">
                <div class="sm:col-span-2">
                    <label class="erp-label"><?php echo e(__('Bank account')); ?></label>
                    <select name="bank_account_id" class="erp-input" required>
                        <option value=""><?php echo e(__('Select…')); ?></option>
                        <?php $__currentLoopData = $bankAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($account->id); ?>" <?php if(old('bank_account_id') == $account->id): echo 'selected'; endif; ?>>
                                <?php echo e($account->name); ?> (<?php echo e($account->glAccount?->code); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Statement date')); ?></label>
                    <input type="date" name="statement_date" value="<?php echo e(old('statement_date', now()->toDateString())); ?>" class="erp-input" required>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Opening balance')); ?></label>
                    <input type="number" step="0.01" name="opening_balance" value="<?php echo e(old('opening_balance', 0)); ?>" class="erp-input" required>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Closing balance')); ?></label>
                    <input type="number" step="0.01" name="closing_balance" value="<?php echo e(old('closing_balance', 0)); ?>" class="erp-input" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="erp-label"><?php echo e(__('Notes')); ?></label>
                    <textarea name="notes" class="erp-input" rows="2"><?php echo e(old('notes')); ?></textarea>
                </div>
            </div>

            <div class="border-t border-erp-border pt-4">
                <h3 class="font-medium mb-2"><?php echo e(__('Statement lines (optional)')); ?></h3>
                <p class="text-xs text-slate-500 mb-3"><?php echo e(__('Use signed amounts: positive for deposits (DR bank), negative for withdrawals (CR bank).')); ?></p>
                <template x-for="i in lineCount" :key="i">
                    <div class="grid gap-2 sm:grid-cols-4 mb-2">
                        <input type="date" :name="`lines[${i-1}][line_date]`" class="erp-input" :value="<?php echo e(json_encode(now()->toDateString())); ?>">
                        <input type="text" :name="`lines[${i-1}][description]`" class="erp-input" placeholder="<?php echo e(__('Description')); ?>">
                        <input type="text" :name="`lines[${i-1}][reference]`" class="erp-input" placeholder="<?php echo e(__('Reference')); ?>">
                        <input type="number" step="0.01" :name="`lines[${i-1}][amount]`" class="erp-input" placeholder="<?php echo e(__('Amount')); ?>">
                    </div>
                </template>
                <button type="button" class="erp-btn-secondary text-sm" @click="lineCount++"><?php echo e(__('Add line')); ?></button>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="erp-btn-primary"><?php echo e(__('Create statement')); ?></button>
                <a href="<?php echo e(route('admin.accounting.bank.reconciliation.index')); ?>" class="erp-btn-secondary"><?php echo e(__('Cancel')); ?></a>
            </div>
        </form>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\accounting\bank\reconciliation-create.blade.php ENDPATH**/ ?>