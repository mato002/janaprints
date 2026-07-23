<?php
    $statusClasses = match ($card['statusVariant'] ?? 'neutral') {
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'warning' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'danger' => 'bg-red-50 text-red-700 ring-red-600/20',
        default => 'bg-slate-100 text-slate-600 ring-slate-500/10',
    };

    $metrics = $card['metrics'];
    $isInteractive = ! ($card['comingSoon'] ?? false) && filled($card['href'] ?? null);
?>

<div class="flex items-start justify-between gap-3">
    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'flex h-10 w-10 shrink-0 items-center justify-center rounded-lg transition-colors',
        'bg-erp-page text-slate-500 group-hover:bg-erp-accent/10 group-hover:text-erp-accent' => $isInteractive,
        'bg-erp-page/80 text-slate-400' => ! $isInteractive,
    ]); ?>">
        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $card['icon'],'class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['icon']),'class' => 'h-5 w-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
    </span>

    <div class="shrink-0 text-right">
        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1 ring-inset <?php echo e($statusClasses); ?>">
            <?php echo e($card['statusLabel']); ?>

        </span>
        <p class="mt-1 text-[10px] font-medium text-slate-400"><?php echo e($card['category_label']); ?></p>
    </div>
</div>

<h3 class="<?php echo \Illuminate\Support\Arr::toCssClasses([
    'mt-3 text-sm font-semibold leading-tight text-erp-primary',
    'group-hover:text-erp-accent' => $isInteractive,
]); ?>">
    <?php echo e($card['title']); ?>

</h3>

<p class="mt-1 line-clamp-2 flex-1 text-xs leading-relaxed text-slate-500">
    <?php echo e($card['description']); ?>

</p>

<div class="mt-3 grid grid-cols-2 gap-2 border-t border-erp-border/70 pt-3 sm:grid-cols-4">
    <div class="min-w-0">
        <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400"><?php echo e(__('Fields')); ?></p>
        <p class="text-sm font-semibold tabular-nums text-erp-primary"><?php echo e(number_format($metrics['field_count'])); ?></p>
    </div>
    <div class="min-w-0">
        <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400"><?php echo e(__('Required')); ?></p>
        <p class="text-sm font-semibold tabular-nums text-emerald-700"><?php echo e(number_format($metrics['required_count'])); ?></p>
    </div>
    <div class="min-w-0">
        <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400"><?php echo e(__('Read-only')); ?></p>
        <p class="text-sm font-semibold tabular-nums text-slate-600"><?php echo e(number_format($metrics['read_only_count'])); ?></p>
    </div>
    <div class="min-w-0">
        <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400"><?php echo e(__('Hidden')); ?></p>
        <p class="text-sm font-semibold tabular-nums text-amber-700"><?php echo e(number_format($metrics['hidden_count'])); ?></p>
    </div>
</div>

<div class="mt-3 flex items-center justify-between gap-2 border-t border-erp-border/70 pt-2">
    <p class="min-w-0 truncate text-[10px] text-slate-400">
        <span class="font-medium text-slate-500"><?php echo e(__('Updated')); ?>:</span>
        <?php echo e($card['updated_label']); ?>

    </p>

    <?php if($card['has_governance_issues'] ?? false): ?>
        <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-medium text-amber-800 ring-1 ring-inset ring-amber-600/20">
            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'exclamation','class' => 'h-3 w-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'exclamation','class' => 'h-3 w-3']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
            <?php echo e(__('Review')); ?>

        </span>
    <?php elseif($isInteractive): ?>
        <span class="inline-flex shrink-0 items-center gap-1 text-[10px] font-medium text-erp-accent opacity-0 transition-opacity group-hover:opacity-100">
            <?php echo e(__('Configure')); ?>

            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'chevron-left','class' => 'h-3 w-3 rotate-180']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-left','class' => 'h-3 w-3 rotate-180']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
        </span>
    <?php else: ?>
        <span class="shrink-0 text-[10px] font-medium text-slate-400"><?php echo e(__('Coming soon')); ?></span>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\forms\partials\form-card-inner.blade.php ENDPATH**/ ?>