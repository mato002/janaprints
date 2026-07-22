<section x-data="{ q: '' }">
    <?php if (isset($component)) { $__componentOriginalba421f08b6b43aecb09f8eebe577a4f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-section','data' => ['title' => __('Permissions'),'description' => __('Effective access rights from your roles. Read-only on this page.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Permissions')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Effective access rights from your roles. Read-only on this page.'))]); ?>
        <div class="md:col-span-2 space-y-4">
            <?php if($permissions->isEmpty()): ?>
                <p class="text-sm text-slate-500"><?php echo e(__('No permissions assigned.')); ?></p>
            <?php else: ?>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-600">
                        <?php echo e(__(':count permissions', ['count' => $permissions->count()])); ?>

                    </p>
                    <input
                        type="search"
                        x-model.debounce.150ms="q"
                        class="erp-input w-full sm:max-w-xs"
                        placeholder="<?php echo e(__('Filter permissions…')); ?>"
                        aria-label="<?php echo e(__('Filter permissions')); ?>"
                    >
                </div>

                <div class="max-h-80 space-y-4 overflow-y-auto pr-1">
                    <?php $__currentLoopData = $permissionsByModule; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module => $modulePermissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div
                            data-permission-module="<?php echo e($module); ?>"
                            x-show="!q.trim() || Array.from($el.querySelectorAll('[data-permission]')).some((node) => node.dataset.permission.includes(q.trim().toLowerCase())) || $el.dataset.permissionModule.includes(q.trim().toLowerCase())"
                        >
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e($module); ?></h3>
                            <ul class="mt-2 flex flex-wrap gap-1.5">
                                <?php $__currentLoopData = $modulePermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li
                                        data-permission="<?php echo e(strtolower($permission)); ?>"
                                        x-show="!q.trim() || $el.dataset.permission.includes(q.trim().toLowerCase())"
                                        class="rounded-md border border-erp-border bg-white px-2 py-1 font-mono text-[11px] text-slate-700"
                                    ><?php echo e($permission); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3)): ?>
<?php $attributes = $__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3; ?>
<?php unset($__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba421f08b6b43aecb09f8eebe577a4f3)): ?>
<?php $component = $__componentOriginalba421f08b6b43aecb09f8eebe577a4f3; ?>
<?php unset($__componentOriginalba421f08b6b43aecb09f8eebe577a4f3); ?>
<?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\profile\partials\permissions-summary.blade.php ENDPATH**/ ?>