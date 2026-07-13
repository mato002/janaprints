<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['actions']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['actions']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if(! empty($actions)): ?>
    <div
        class="pointer-events-none fixed bottom-4 right-4 z-40"
        x-data="{ open: false }"
    >
        <div
            class="pointer-events-auto mb-2 flex flex-col items-end gap-2"
            x-show="open"
            x-cloak
            x-transition
        >
            <?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a
                    href="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::url($action['url'])); ?>"
                    <?php if($action['modal'] ?? false): ?> data-erp-modal-open <?php endif; ?>
                    class="rounded-full border border-erp-border bg-white px-4 py-2 text-xs font-medium text-erp-primary shadow-md transition hover:border-erp-accent hover:text-erp-accent"
                >
                    <?php echo e($action['label']); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <button
            type="button"
            class="pointer-events-auto flex h-12 w-12 items-center justify-center rounded-full bg-erp-accent text-white shadow-lg transition hover:bg-erp-accent-hover"
            @click="open = !open"
            :aria-expanded="open"
            aria-label="<?php echo e(__('Quick Actions')); ?>"
        >
            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'plus','class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','class' => 'h-5 w-5']); ?>
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
        </button>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/hr/dashboard/partials/quick-actions-floating.blade.php ENDPATH**/ ?>