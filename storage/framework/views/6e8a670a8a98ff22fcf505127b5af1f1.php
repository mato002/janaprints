<div class="crm-360__tab-stack">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $lead)): ?>
        <section class="crm-360__card crm-360__card--form">
            <h2 class="crm-360__card-title"><?php echo e(__('Schedule follow-up')); ?></h2>
            <form method="POST" action="<?php echo e(route('admin.crm.leads.follow-ups.store', $lead)); ?>" class="crm-360__form-grid">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="erp-label"><?php echo e(__('Scheduled at')); ?></label>
                    <?php if (isset($component)) { $__componentOriginal18c21970322f9e5c938bc954620c12bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18c21970322f9e5c938bc954620c12bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.text-input','data' => ['name' => 'scheduled_at','type' => 'datetime-local','class' => 'w-full','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'scheduled_at','type' => 'datetime-local','class' => 'w-full','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $attributes = $__attributesOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__attributesOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $component = $__componentOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__componentOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
                </div>
                <div class="sm:col-span-2">
                    <label class="erp-label"><?php echo e(__('Notes')); ?></label>
                    <textarea name="notes" class="erp-input w-full text-sm" rows="2"></textarea>
                </div>
                <div class="sm:col-span-3">
                    <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['type' => 'submit','variant' => 'primary','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','variant' => 'primary','size' => 'sm']); ?><?php echo e(__('Schedule follow-up')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
                </div>
            </form>
        </section>
    <?php endif; ?>

    <section class="crm-360__card">
        <h2 class="crm-360__card-title text-red-600"><?php echo e(__('Overdue')); ?> (<?php echo e($followUps['overdue']->count()); ?>)</h2>
        <?php echo $__env->make('admin.crm.leads.360.partials.follow-up-list', ['items' => $followUps['overdue'], 'empty' => __('No overdue follow-ups')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </section>

    <section class="crm-360__card">
        <h2 class="crm-360__card-title"><?php echo e(__('Scheduled')); ?> (<?php echo e($followUps['scheduled']->count()); ?>)</h2>
        <?php echo $__env->make('admin.crm.leads.360.partials.follow-up-list', ['items' => $followUps['scheduled'], 'empty' => __('No scheduled follow-ups')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </section>

    <section class="crm-360__card">
        <h2 class="crm-360__card-title"><?php echo e(__('Completed')); ?> (<?php echo e($followUps['completed']->count()); ?>)</h2>
        <?php echo $__env->make('admin.crm.leads.360.partials.follow-up-list', ['items' => $followUps['completed'], 'empty' => __('No completed follow-ups'), 'showComplete' => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </section>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\leads\360\tab-follow-ups.blade.php ENDPATH**/ ?>