<?php
    $designerOperator = $designerOperator ?? auth()->user()?->prefersDesignerOperatorMode() ?? false;
    $compact = (bool) ($compact ?? false);
?>

<header class="<?php echo \Illuminate\Support\Arr::toCssClasses(['artwork-detail-header', 'mb-1' => $compact, 'mb-2' => ! $compact]); ?>">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <p class="artwork-detail-header__number"><?php echo e($request->request_number); ?></p>
            <h1 class="artwork-detail-header__title"><?php echo e($request->title); ?></h1>
            <?php if($request->customer?->company_name): ?>
                <p class="artwork-detail-header__customer"><?php echo e($request->customer->company_name); ?></p>
            <?php endif; ?>
        </div>
        <div class="flex shrink-0 flex-wrap items-center justify-end gap-2">
            <?php if($designerOperator): ?>
                <a href="<?php echo e(route('admin.artwork.desk')); ?>" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main"><?php echo e(__('Back to Designer Desk')); ?></a>
            <?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal29cf37a6f8106e009d4d5ad1c3842a2a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal29cf37a6f8106e009d4d5ad1c3842a2a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.artwork-status-badge','data' => ['status' => $request->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.artwork-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($request->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal29cf37a6f8106e009d4d5ad1c3842a2a)): ?>
<?php $attributes = $__attributesOriginal29cf37a6f8106e009d4d5ad1c3842a2a; ?>
<?php unset($__attributesOriginal29cf37a6f8106e009d4d5ad1c3842a2a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal29cf37a6f8106e009d4d5ad1c3842a2a)): ?>
<?php $component = $__componentOriginal29cf37a6f8106e009d4d5ad1c3842a2a; ?>
<?php unset($__componentOriginal29cf37a6f8106e009d4d5ad1c3842a2a); ?>
<?php endif; ?>
            <span class="text-sm tabular-nums text-slate-500">v<?php echo e($request->current_version ?: '0'); ?></span>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $request)): ?>
                <a href="<?php echo e(route('admin.artwork.edit', $request)); ?>" class="erp-btn-secondary text-sm"><?php echo e(__('Edit')); ?></a>
            <?php endif; ?>
        </div>
    </div>
</header>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\artwork\requests\partials\detail-header.blade.php ENDPATH**/ ?>