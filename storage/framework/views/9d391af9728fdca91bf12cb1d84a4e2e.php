<?php
    $events = $tabData['events'] ?? null;
    $filter = $tabData['filter'] ?? 'all';
    $search = $tabData['search'] ?? '';
    $filters = $tabData['filters'] ?? [];
    $accountingPlaceholder = $tabData['accounting_placeholder'] ?? false;
?>

<div class="c360-timeline">
    <div class="c360-timeline__intro mb-4">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Unified customer timeline')); ?></h3>
        <p class="mt-1 text-sm text-slate-600"><?php echo e(__('Chronological audit trail across CRM, sales, artwork, production, and delivery.')); ?></p>
    </div>

    <form
        method="GET"
        action="<?php echo e(route('admin.crm.customers.show', $customer)); ?>"
        class="c360-timeline__toolbar mb-4 space-y-3"
        data-turbo-frame="erp-main"
    >
        <input type="hidden" name="tab" value="timeline">

        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="relative min-w-0 flex-1 max-w-xl">
                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'search','class' => 'pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','class' => 'pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400']); ?>
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
                <input
                    type="search"
                    name="timeline_search"
                    value="<?php echo e($search); ?>"
                    class="erp-input w-full py-2 pl-9 text-sm"
                    placeholder="<?php echo e(__('Search timeline…')); ?>"
                    aria-label="<?php echo e(__('Search timeline')); ?>"
                />
            </div>
            <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Apply')); ?></button>
        </div>

        <div class="c360-timeline__filters flex flex-wrap gap-1.5" role="tablist" aria-label="<?php echo e(__('Timeline filters')); ?>">
            <?php $__currentLoopData = $filters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php ($active = $filter === $option['value']); ?>
                <a
                    href="<?php echo e(route('admin.crm.customers.show', array_filter([
                        'customer' => $customer,
                        'tab' => 'timeline',
                        'timeline_filter' => $option['value'] !== 'all' ? $option['value'] : null,
                        'timeline_search' => $search ?: null,
                    ]))); ?>"
                    class="erp-filter-pill <?php echo e($active ? 'erp-filter-pill--active' : ''); ?>"
                    data-turbo-frame="erp-main"
                    <?php if($active): ?> aria-current="true" <?php endif; ?>
                ><?php echo e($option['label']); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </form>

    <?php if($accountingPlaceholder): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4 border-dashed']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4 border-dashed']); ?>
            <p class="text-sm text-slate-600"><?php echo e(__('Available after Accounting Activation')); ?></p>
            <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Accounting events will appear in this feed when the module is enabled.')); ?></p>
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
    <?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false,'class' => 'overflow-hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'class' => 'overflow-hidden']); ?>
        <div class="c360-timeline__feed px-4 py-3">
            <?php if($events): ?>
                <?php if (isset($component)) { $__componentOriginald535d753c616cb98d2a43b2025cef726 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald535d753c616cb98d2a43b2025cef726 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.customer-timeline-feed','data' => ['events' => $events]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.customer-timeline-feed'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['events' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($events)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald535d753c616cb98d2a43b2025cef726)): ?>
<?php $attributes = $__attributesOriginald535d753c616cb98d2a43b2025cef726; ?>
<?php unset($__attributesOriginald535d753c616cb98d2a43b2025cef726); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald535d753c616cb98d2a43b2025cef726)): ?>
<?php $component = $__componentOriginald535d753c616cb98d2a43b2025cef726; ?>
<?php unset($__componentOriginald535d753c616cb98d2a43b2025cef726); ?>
<?php endif; ?>
            <?php endif; ?>
        </div>
        <?php if($events && $events->hasPages()): ?>
            <div class="border-t border-erp-border px-4 py-3">
                <?php echo e($events->withQueryString()->links()); ?>

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
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\workspace\tabs\timeline.blade.php ENDPATH**/ ?>