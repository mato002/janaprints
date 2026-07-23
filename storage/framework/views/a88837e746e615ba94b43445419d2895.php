<div class="flex h-full min-w-0 gap-2.5">
    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'flex h-8 w-8 shrink-0 items-center justify-center rounded-md',
        'bg-erp-page text-slate-500 group-hover:bg-erp-accent/10 group-hover:text-erp-accent' => ! ($comingSoon ?? false),
        'bg-erp-page/80 text-slate-400' => ($comingSoon ?? false),
    ]); ?>">
        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $icon,'class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'class' => 'h-4 w-4']); ?>
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

    <div class="min-w-0 flex-1">
        <h3 class="text-sm font-semibold leading-snug text-erp-primary group-hover:text-erp-accent [overflow-wrap:anywhere]">
            <?php echo e($title); ?>

        </h3>

        <?php if($statusLabel): ?>
            <span class="mt-1 inline-flex max-w-full rounded px-1.5 py-0.5 text-[10px] font-medium leading-snug ring-1 ring-inset <?php echo e($statusClasses); ?>">
                <?php echo e($statusLabel); ?>

            </span>
        <?php endif; ?>

        <p class="mt-1.5 line-clamp-2 text-[11px] leading-snug text-slate-500">
            <?php echo e($description); ?>

        </p>

        <?php if(isset($count) && $count !== null): ?>
            <p class="mt-1.5 text-xs font-semibold tabular-nums text-erp-primary">
                <?php echo e(number_format($count)); ?>

            </p>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\partials\settings-tile-inner.blade.php ENDPATH**/ ?>