<turbo-frame id="erp-form-modal">
    <div class="erp-form-modal w-full" data-erp-form-modal-panel>
        <div class="erp-form-modal__header">
            <h2 id="erp-form-modal-title" class="erp-form-modal__title">
                <?php echo e($presentation['category_label'] ?? __('System Errors')); ?>

            </h2>
            <button
                type="button"
                class="erp-form-modal__close"
                data-erp-form-modal-close
                aria-label="<?php echo e(__('Close')); ?>"
            >
                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'x-mark','class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'h-5 w-5']); ?>
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
        <div class="erp-form-modal__body">
            <?php echo $__env->make('admin.partials.modal-validation-alert', [
                'validationMessages' => $validationMessages ?? [],
                'validationPresentation' => $presentation ?? null,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('admin.partials.governed-form-errors', [
                'presentation' => $presentation ?? null,
                'message' => $message ?? null,
                'detail' => $detail ?? null,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <button type="button" class="erp-btn-secondary mt-4" data-erp-form-modal-close>
                <?php echo e(__('Close')); ?>

            </button>
        </div>
    </div>
</turbo-frame>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\partials\modal-form-error.blade.php ENDPATH**/ ?>