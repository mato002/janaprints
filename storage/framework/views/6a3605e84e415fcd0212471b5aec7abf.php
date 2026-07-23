<section>
    <?php if (isset($component)) { $__componentOriginalba421f08b6b43aecb09f8eebe577a4f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-section','data' => ['title' => __('Roles'),'description' => __('Security groups assigned to your account. These are managed by administrators.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Roles')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Security groups assigned to your account. These are managed by administrators.'))]); ?>
        <div class="md:col-span-2">
            <?php if($roles->isEmpty()): ?>
                <p class="text-sm text-slate-500"><?php echo e(__('No roles assigned.')); ?></p>
            <?php else: ?>
                <div class="flex flex-wrap gap-2">
                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="inline-flex items-center rounded-full border border-erp-accent/30 bg-erp-accent/10 px-3 py-1 text-sm font-medium text-erp-accent">
                            <?php echo e($role); ?>

                        </span>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\profile\partials\roles-summary.blade.php ENDPATH**/ ?>