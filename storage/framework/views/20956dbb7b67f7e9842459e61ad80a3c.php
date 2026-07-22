<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('New Budget'),'breadcrumbs' => [['label' => __('Budgets'), 'url' => route('admin.accounting.budgets.index')], ['label' => __('New')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('New Budget'),'description' => __('Draft budget with one or more GL lines')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('New Budget')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Draft budget with one or more GL lines'))]); ?>
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
        <form method="POST" action="<?php echo e(route('admin.accounting.budgets.store')); ?>" class="space-y-4" x-data="{ lineCount: 1 }">
            <?php echo csrf_field(); ?>
            <div class="grid gap-4 sm:grid-cols-2 max-w-3xl">
                <div class="sm:col-span-2">
                    <label class="erp-label"><?php echo e(__('Name')); ?></label>
                    <input type="text" name="name" value="<?php echo e(old('name')); ?>" class="erp-input" required>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Fiscal year')); ?></label>
                    <select name="fiscal_year_id" class="erp-input">
                        <option value=""><?php echo e(__('Optional')); ?></option>
                        <?php $__currentLoopData = $fiscalYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($fy->id); ?>" <?php if(old('fiscal_year_id') == $fy->id): echo 'selected'; endif; ?>><?php echo e($fy->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div></div>
                <div>
                    <label class="erp-label"><?php echo e(__('From date')); ?></label>
                    <input type="date" name="from_date" value="<?php echo e(old('from_date', now()->startOfYear()->toDateString())); ?>" class="erp-input" required>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('To date')); ?></label>
                    <input type="date" name="to_date" value="<?php echo e(old('to_date', now()->endOfYear()->toDateString())); ?>" class="erp-input" required>
                </div>
            </div>

            <div class="border-t border-erp-border pt-4">
                <h3 class="font-medium mb-2"><?php echo e(__('Budget lines')); ?></h3>
                <template x-for="i in lineCount" :key="i">
                    <div class="grid gap-2 sm:grid-cols-3 mb-2">
                        <select :name="`lines[${i-1}][gl_account_id]`" class="erp-input" required>
                            <option value=""><?php echo e(__('GL account')); ?></option>
                            <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($account->id); ?>"><?php echo e($account->code); ?> — <?php echo e($account->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <input type="text" :name="`lines[${i-1}][period_month]`" class="erp-input" placeholder="<?php echo e(__('YYYY-MM optional')); ?>">
                        <input type="number" step="0.01" :name="`lines[${i-1}][amount]`" class="erp-input" placeholder="<?php echo e(__('Amount')); ?>" required>
                    </div>
                </template>
                <button type="button" class="erp-btn-secondary text-sm" @click="lineCount++"><?php echo e(__('Add line')); ?></button>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="erp-btn-primary"><?php echo e(__('Create budget')); ?></button>
                <a href="<?php echo e(route('admin.accounting.budgets.index')); ?>" class="erp-btn-secondary"><?php echo e(__('Cancel')); ?></a>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\accounting\budgets\create.blade.php ENDPATH**/ ?>