<turbo-frame id="module-workspace-content">
    <div class="module-workspace-embedded w-full min-w-0">
        <?php if(! empty($statusMessage)): ?>
            <div
                class="mb-4 flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-erp-success"
                role="status"
                data-erp-flash-status
            >
                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'badge-check','class' => 'h-5 w-5 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'badge-check','class' => 'h-5 w-5 shrink-0']); ?>
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
                <span><?php echo e($statusMessage); ?></span>
            </div>
        <?php endif; ?>

        <?php if(! empty($errorMessage)): ?>
            <div
                class="mb-4 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-erp-danger"
                role="alert"
                data-erp-flash-error
                data-erp-validation-errors
            >
                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'exclamation','class' => 'h-5 w-5 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'exclamation','class' => 'h-5 w-5 shrink-0']); ?>
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
                <div>
                    <p class="font-medium"><?php echo e($errorMessage); ?></p>
                    <?php if(! empty($validationErrors) && $validationErrors->any()): ?>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            <?php $__currentLoopData = $validationErrors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php echo $__env->make('admin.partials.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.settings.partials.scope-selector', [
            'action' => route('admin.settings.forms.index', ['form' => $activeFormKey, 'embedded' => '1'] + $scopeQuery),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $companies,
            'branches' => $branches,
            'compact' => true,
            'activeFormKey' => $activeFormKey,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.settings.forms.partials.workspace', [
            'form' => $activeForm,
            'canManage' => $canManage,
            'companyId' => $companyId,
            'branchId' => $branchId,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</turbo-frame>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/settings/forms/embedded-frame.blade.php ENDPATH**/ ?>