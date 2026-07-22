<?php
    $dashboardUrl = route('admin.production.costing.dashboard');
?>

<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
    <form method="GET" action="<?php echo e($dashboardUrl); ?>" x-data="erpIndexFilterForm()" @change="onFieldChange($event)" class="border-b border-erp-border px-4 py-3" data-turbo-frame="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::turboFrame()); ?>">
        <div class="flex flex-wrap items-center gap-2">
            <input type="date" name="date_from" value="<?php echo e($filters['date_from'] ?? ''); ?>" class="erp-toolbar-input" aria-label="<?php echo e(__('Date from')); ?>">
            <input type="date" name="date_to" value="<?php echo e($filters['date_to'] ?? ''); ?>" class="erp-toolbar-input" aria-label="<?php echo e(__('Date to')); ?>">
            <select name="branch_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Branch')); ?>">
                <option value=""><?php echo e(__('All branches')); ?></option>
                <?php $__currentLoopData = $filterOptions['branches'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($branch->id); ?>" <?php if(($filters['branch_id'] ?? null) == $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="customer_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Customer')); ?>">
                <option value=""><?php echo e(__('All customers')); ?></option>
                <?php $__currentLoopData = $filterOptions['customers'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($customer->id); ?>" <?php if(($filters['customer_id'] ?? null) == $customer->id): echo 'selected'; endif; ?>><?php echo e($customer->company_name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="production_type" class="erp-toolbar-select" aria-label="<?php echo e(__('Product / service type')); ?>">
                <option value=""><?php echo e(__('All types')); ?></option>
                <?php $__currentLoopData = $filterOptions['production_types'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($type->value); ?>" <?php if(($filters['production_type'] ?? '') === $type->value): echo 'selected'; endif; ?>><?php echo e(str($type->value)->replace('_', ' ')->headline()); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="margin_category" class="erp-toolbar-select" aria-label="<?php echo e(__('Margin category')); ?>">
                <option value=""><?php echo e(__('All margins')); ?></option>
                <?php $__currentLoopData = $filterOptions['margin_categories'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>" <?php if(($filters['margin_category'] ?? '') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <a href="<?php echo e($dashboardUrl); ?>" class="erp-btn-ghost py-1 text-xs text-slate-500" data-turbo-frame="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::turboFrame()); ?>"><?php echo e(__('Reset')); ?></a>
        </div>
    </form>

    <?php if(count($activeChips) > 0): ?>
        <div class="flex flex-wrap items-center gap-2 border-t border-erp-border px-4 py-2">
            <span class="text-xs font-medium text-slate-500"><?php echo e(__('Active filters')); ?>:</span>
            <?php $__currentLoopData = $activeChips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e($chip['url']); ?>" class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700 hover:bg-slate-200" data-turbo-frame="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::turboFrame()); ?>">
                    <?php echo e($chip['label']); ?>

                    <span aria-hidden="true">×</span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\costing\command-center\filters.blade.php ENDPATH**/ ?>