<ul class="crm-360__feed" role="list">
    <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $followUp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <li class="crm-360__feed-item">
            <div class="crm-360__feed-head">
                <span class="crm-360__feed-title"><?php echo e($followUp['scheduled_at']?->format('d M Y H:i')); ?></span>
                <span class="crm-360__pill"><?php echo e(str_replace('_', ' ', $followUp['status'])); ?></span>
            </div>
            <?php if($followUp['notes']): ?>
                <p class="crm-360__feed-meta"><?php echo e($followUp['notes']); ?></p>
            <?php endif; ?>
            <?php if($followUp['assignee']): ?>
                <p class="crm-360__feed-meta"><?php echo e(__('Assigned to')); ?> <?php echo e($followUp['assignee']); ?></p>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $lead)): ?>
                <?php if(($showComplete ?? true) && $followUp['status'] === 'pending'): ?>
                    <form method="POST" action="<?php echo e(route('admin.crm.leads.follow-ups.update', [$lead, $followUp['id']])); ?>" class="mt-2"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                        <input type="hidden" name="status" value="completed">
                        <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['type' => 'submit','variant' => 'outline','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','variant' => 'outline','size' => 'sm']); ?><?php echo e(__('Mark complete')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <li class="crm-360__empty-inline"><?php echo e($empty); ?></li>
    <?php endif; ?>
</ul>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\leads\360\partials\follow-up-list.blade.php ENDPATH**/ ?>