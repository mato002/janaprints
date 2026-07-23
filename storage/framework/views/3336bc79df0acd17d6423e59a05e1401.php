<span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
    'flex h-7 w-7 shrink-0 items-center justify-center rounded-md',
    'bg-erp-page text-slate-500 group-hover:bg-erp-accent/10 group-hover:text-erp-accent' => ! ($comingSoon ?? false),
    'bg-erp-page/80 text-slate-400' => ($comingSoon ?? false),
]); ?>">
    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $icon,'class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'class' => 'h-3.5 w-3.5']); ?>
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
    <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
        <span class="text-sm font-medium text-erp-primary group-hover:text-erp-accent"><?php echo e($title); ?></span>
        <?php if($domainLabel): ?>
            <span class="hidden shrink-0 text-[10px] text-slate-400 sm:inline"><?php echo e($domainLabel); ?></span>
        <?php endif; ?>
    </div>
    <p class="line-clamp-1 text-[11px] text-slate-500"><?php echo e($description); ?></p>
</div>

<?php if($statusLabel): ?>
    <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-medium ring-1 ring-inset <?php echo e($statusClasses); ?>">
        <?php echo e($statusLabel); ?>

    </span>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\partials\settings-list-row-inner.blade.php ENDPATH**/ ?>