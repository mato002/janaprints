<section class="exec-panel exec-panel--activity h-full">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title"><?php echo e(__('Activity Feed')); ?></h2>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\ActivityLog::class)): ?>
            <a href="<?php echo e(route('admin.activity-logs.index')); ?>" data-turbo-frame="erp-main" class="text-[11px] font-medium text-erp-accent hover:underline"><?php echo e(__('View all')); ?></a>
        <?php endif; ?>
    </div>
    <p class="mb-2 text-[10px] text-slate-500"><?php echo e(__('Newest system events first')); ?></p>
    <div class="exec-activity-feed exec-activity-feed--prominent">
        <?php if(count($dashboard['activity']) === 0): ?>
            <?php if (isset($component)) { $__componentOriginal1300bd4fc578b3dfcc7422a709312fdd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-empty-state','data' => ['title' => __('No activity yet'),'description' => __('Quotes, jobs, invoices, and payments will stream here.'),'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No activity yet')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Quotes, jobs, invoices, and payments will stream here.')),'compact' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd)): ?>
<?php $attributes = $__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd; ?>
<?php unset($__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1300bd4fc578b3dfcc7422a709312fdd)): ?>
<?php $component = $__componentOriginal1300bd4fc578b3dfcc7422a709312fdd; ?>
<?php unset($__componentOriginal1300bd4fc578b3dfcc7422a709312fdd); ?>
<?php endif; ?>
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal03272a71e6ea55b8791c72ff676dfebf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal03272a71e6ea55b8791c72ff676dfebf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.activity-timeline','data' => ['items' => $dashboard['activity']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.activity-timeline'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($dashboard['activity'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal03272a71e6ea55b8791c72ff676dfebf)): ?>
<?php $attributes = $__attributesOriginal03272a71e6ea55b8791c72ff676dfebf; ?>
<?php unset($__attributesOriginal03272a71e6ea55b8791c72ff676dfebf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal03272a71e6ea55b8791c72ff676dfebf)): ?>
<?php $component = $__componentOriginal03272a71e6ea55b8791c72ff676dfebf; ?>
<?php unset($__componentOriginal03272a71e6ea55b8791c72ff676dfebf); ?>
<?php endif; ?>
        <?php endif; ?>
    </div>
</section>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\janaprints\resources\views/admin/dashboard/partials/activity-feed.blade.php ENDPATH**/ ?>