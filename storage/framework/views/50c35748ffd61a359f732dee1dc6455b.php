<?php
    $indexUrl = route('admin.production.work-centers.index');
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
    <form method="GET" action="<?php echo e($indexUrl); ?>" x-data="erpIndexFilterForm()" @change="onFieldChange($event)" class="border-b border-erp-border px-4 py-3" data-turbo-frame="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::turboFrame()); ?>">
        <div class="flex flex-wrap items-center gap-2">
            <input type="search" name="search" value="<?php echo e($filters['search'] ?? ''); ?>" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="<?php echo e(__('Work center name or code…')); ?>" aria-label="<?php echo e(__('Search')); ?>" data-erp-auto-search>
            <?php if (isset($component)) { $__componentOriginal8d71058e635815f8f51e2bf876db5ad4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8d71058e635815f8f51e2bf876db5ad4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-pills','data' => ['options' => [['value' => '', 'label' => __('All statuses')], ['value' => 'active', 'label' => __('Active')], ['value' => 'inactive', 'label' => __('Inactive')]],'param' => 'status','current' => $filters['status'] ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-pills'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['value' => '', 'label' => __('All statuses')], ['value' => 'active', 'label' => __('Active')], ['value' => 'inactive', 'label' => __('Inactive')]]),'param' => 'status','current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filters['status'] ?? '')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8d71058e635815f8f51e2bf876db5ad4)): ?>
<?php $attributes = $__attributesOriginal8d71058e635815f8f51e2bf876db5ad4; ?>
<?php unset($__attributesOriginal8d71058e635815f8f51e2bf876db5ad4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8d71058e635815f8f51e2bf876db5ad4)): ?>
<?php $component = $__componentOriginal8d71058e635815f8f51e2bf876db5ad4; ?>
<?php unset($__componentOriginal8d71058e635815f8f51e2bf876db5ad4); ?>
<?php endif; ?>
            <select name="stage_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Stage / process area')); ?>">
                <option value=""><?php echo e(__('All stages')); ?></option>
                <?php $__currentLoopData = $filterOptions['stages'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($stage->id); ?>" <?php if(($filters['stage_id'] ?? null) == $stage->id): echo 'selected'; endif; ?>><?php echo e($stage->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="load" class="erp-toolbar-select" aria-label="<?php echo e(__('Load')); ?>">
                <option value=""><?php echo e(__('All load levels')); ?></option>
                <?php $__currentLoopData = $filterOptions['load_options'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>" <?php if(($filters['load'] ?? '') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <a href="<?php echo e($indexUrl); ?>" class="erp-btn-ghost py-1 text-xs text-slate-500" data-turbo-frame="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::turboFrame()); ?>"><?php echo e(__('Reset')); ?></a>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\work-centers\command-center\filters.blade.php ENDPATH**/ ?>