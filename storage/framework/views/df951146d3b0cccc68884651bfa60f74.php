<?php if(! empty($workspaceNavigation['show_back']) && ! empty($workspaceNavigation['parent_url'])): ?>
    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['mb-1' => ! empty($compact), 'mb-3' => empty($compact)]); ?>">
        <a
            href="<?php echo e($workspaceNavigation['parent_url']); ?>"
            data-turbo-frame="erp-main"
            data-turbo-action="advance"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'inline-flex items-center gap-1.5 font-medium text-slate-600 transition hover:text-erp-accent',
                'rounded-md border border-erp-border bg-white px-2 py-1 text-xs shadow-sm hover:border-erp-accent/40 hover:bg-slate-50' => ! empty($compact),
                'gap-2 rounded-lg border border-erp-border bg-white px-3 py-2 text-sm shadow-sm hover:border-erp-accent/40 hover:bg-slate-50' => empty($compact),
            ]); ?>"
        >
            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'chevron-left','class' => 'h-4 w-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-left','class' => 'h-4 w-4 shrink-0']); ?>
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
            <span class="sm:hidden"><?php echo e(__('Back')); ?></span>
            <span class="hidden sm:inline"><?php echo e(__('Back to :workspace', ['workspace' => $workspaceNavigation['parent_workspace_title']])); ?></span>
        </a>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\partials\workspace-back.blade.php ENDPATH**/ ?>