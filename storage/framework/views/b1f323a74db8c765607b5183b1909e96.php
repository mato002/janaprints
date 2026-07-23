<div class="crm-360__tab-stack">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $customer)): ?>
        <section class="crm-360__card crm-360__card--form">
            <h2 class="crm-360__card-title"><?php echo e(__('Add note')); ?></h2>
            <form method="POST" action="<?php echo e(route('admin.crm.customers.notes.store', $customer)); ?>">
                <?php echo csrf_field(); ?>
                <textarea name="note" class="erp-input w-full min-h-[4.5rem] text-sm" rows="3" placeholder="<?php echo e(__('Internal note about this customer…')); ?>" required></textarea>
                <div class="mt-3">
                    <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['type' => 'submit','variant' => 'primary','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','variant' => 'primary','size' => 'sm']); ?><?php echo e(__('Add note')); ?> <?php echo $__env->renderComponent(); ?>
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
        <h2 class="crm-360__card-title"><?php echo e(__('Notes feed')); ?></h2>
        <ul class="crm-360__notes-feed" role="list">
            <?php $__empty_1 = true; $__currentLoopData = $customer->customerNotes->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li class="crm-360__note-card">
                    <div class="crm-360__note-head">
                        <span class="crm-360__note-author"><?php echo e($note->user?->name ?? __('Unknown')); ?></span>
                        <time class="crm-360__note-time"><?php echo e($note->created_at?->diffForHumans()); ?></time>
                    </div>
                    <p class="crm-360__note-body"><?php echo e($note->note); ?></p>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li class="crm-360__empty-inline"><?php echo e(__('No notes yet')); ?></li>
            <?php endif; ?>
        </ul>
    </section>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\360\tab-notes.blade.php ENDPATH**/ ?>