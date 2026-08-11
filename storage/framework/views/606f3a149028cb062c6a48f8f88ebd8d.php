<?php if(! empty($quoteRequestsTopbar['visible']) && ! empty($quoteRequestsTopbar['route'])): ?>
    <a
        href="<?php echo e($quoteRequestsTopbar['route']); ?>"
        data-turbo-frame="erp-main"
        class="erp-quote-topbar-btn"
        title="<?php echo e($quoteRequestsTopbar['label']); ?>"
        aria-label="<?php echo e($quoteRequestsTopbar['label']); ?>"
    >
        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'document-text','class' => 'erp-quote-topbar-btn__icon h-4 w-4 shrink-0 sm:hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'document-text','class' => 'erp-quote-topbar-btn__icon h-4 w-4 shrink-0 sm:hidden']); ?>
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
        <span class="erp-quote-topbar-btn__label hidden sm:inline"><?php echo e($quoteRequestsTopbar['label']); ?></span>
        <?php if(($quoteRequestsTopbar['count'] ?? 0) > 0): ?>
            <span class="erp-quote-topbar-btn__badge"><?php echo e($quoteRequestsTopbar['count']); ?></span>
        <?php endif; ?>
    </a>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/layouts/admin/partials/quote-requests-topbar.blade.php ENDPATH**/ ?>