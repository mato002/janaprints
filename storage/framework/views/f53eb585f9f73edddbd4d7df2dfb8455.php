<turbo-frame
    id="erp-main"
    data-turbo-action="advance"
    class="flex min-h-0 flex-1 flex-col overflow-x-hidden overflow-y-auto"
>
    <?php
        $frameQuickCreate = array_values(array_map(
            fn (array $item) => [
                'label' => $item['label'],
                'coming_soon' => (bool) ($item['coming_soon'] ?? false),
                'modal' => (bool) ($item['modal'] ?? false),
                'href' => empty($item['coming_soon']) && ! empty($item['route']) && Route::has($item['route'])
                    ? route($item['route'], $item['route_params'] ?? [])
                    : null,
            ],
            array_filter(
                app(\App\Support\Navigation\WorkspacePresenter::class)->quickCreateForRoute(Route::currentRouteName()),
                fn (array $item) => $item['visible'] ?? true,
            ),
        ));
    ?>
    <span
        id="erp-route-meta"
        class="sr-only"
        data-route="<?php echo e(Route::currentRouteName()); ?>"
        data-title="<?php echo e($title); ?>"
        data-compact-page="0"
        data-app-name="<?php echo e(config('app.name')); ?>"
        data-quick-create="<?php echo e(json_encode($frameQuickCreate, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)); ?>"
        data-i18n-create="<?php echo e(__('Create')); ?>"
        data-i18n-soon="<?php echo e(__('Soon')); ?>"
        aria-hidden="true"
    ></span>
    <main class="flex min-h-0 flex-1 flex-col p-4 sm:p-6 lg:p-8">
        <?php echo $__env->make('admin.partials.breadcrumbs', [
            'breadcrumbs' => [
                ['label' => __('Administration')],
                ['label' => __('Settings'), 'url' => $hubBackUrl],
                ['label' => __('Forms'), 'url' => route('admin.settings.forms.index', $scopeQuery)],
                ['label' => $activeForm['label']],
            ],
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

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
            'action' => route('admin.settings.forms.index', ['form' => $activeFormKey] + $scopeQuery),
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
    </main>
</turbo-frame>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/settings/forms/frame.blade.php ENDPATH**/ ?>