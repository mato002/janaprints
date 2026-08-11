<?php if($orderPresentation): ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <h3 class="mb-2 text-sm font-semibold text-slate-900"><?php echo e(__('Order actions')); ?></h3>
        <div class="flex flex-wrap gap-2">
            <?php if($orderPresentation['edit_url'] ?? null): ?>
                <a href="<?php echo e($orderPresentation['edit_url']); ?>" class="erp-btn-secondary text-xs" data-erp-modal-open><?php echo e(__('Edit order')); ?></a>
            <?php endif; ?>
            <?php if($orderPresentation['payment_url'] ?? null): ?>
                <a href="<?php echo e($orderPresentation['payment_url']); ?>" class="erp-btn-secondary text-xs" data-erp-modal-open><?php echo e(__('Record payment')); ?></a>
            <?php endif; ?>
            <?php if($orderPresentation['invoice_url'] ?? null): ?>
                <a href="<?php echo e($orderPresentation['invoice_url']); ?>" class="erp-btn-secondary text-xs" data-erp-modal-open><?php echo e(__('Create invoice')); ?></a>
            <?php endif; ?>
            <?php if($orderPresentation['latest_invoice']['document_url'] ?? null): ?>
                <a href="<?php echo e($orderPresentation['latest_invoice']['document_url']); ?>" class="erp-btn-secondary text-xs" target="_blank" rel="noopener"><?php echo e(__('Print invoice')); ?></a>
            <?php endif; ?>
        </div>

        <?php if($orderPresentation['financial'] ?? null): ?>
            <p class="mt-3 text-xs text-slate-600">
                <?php echo e(__('Payment status')); ?>:
                <span class="font-medium"><?php echo e($orderPresentation['financial']['financial_status_label'] ?? '—'); ?></span>
                <?php if(! empty($orderPresentation['financial']['deposit']['required_amount'])): ?>
                    · <?php echo e(__('Deposit due')); ?>: <?php echo e(number_format((float) $orderPresentation['financial']['deposit']['required_amount'], 2)); ?>

                <?php endif; ?>
            </p>
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
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/desk/partials/order-actions.blade.php ENDPATH**/ ?>