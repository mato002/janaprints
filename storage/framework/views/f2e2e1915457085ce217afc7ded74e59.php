<div class="crm-360__tab-stack">
    <section class="crm-360__card">
        <h2 class="crm-360__card-title"><?php echo e(__('Customer Files')); ?></h2>
        <ul class="crm-360__file-list" role="list">
            <?php $__empty_1 = true; $__currentLoopData = $customer->files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li class="crm-360__file-row">
                    <div>
                        <span class="font-medium text-erp-primary"><?php echo e($file->original_name); ?></span>
                        <span class="block text-[11px] text-slate-500"><?php echo e($file->uploader?->name); ?> · <?php echo e($file->created_at?->diffForHumans()); ?></span>
                    </div>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $customer)): ?>
                        <form method="POST" action="<?php echo e(route('admin.crm.customers.files.destroy', [$customer, $file])); ?>"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['type' => 'submit','variant' => 'danger','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','variant' => 'danger','size' => 'sm']); ?><?php echo e(__('Remove')); ?> <?php echo $__env->renderComponent(); ?>
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
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li class="crm-360__empty-inline"><?php echo e(__('No customer files uploaded')); ?></li>
            <?php endif; ?>
        </ul>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $customer)): ?>
            <form method="POST" action="<?php echo e(route('admin.crm.customers.files.store', $customer)); ?>" enctype="multipart/form-data" data-turbo-frame="erp-main" class="crm-360__upload-form mt-4">
                <?php echo csrf_field(); ?>
                <label class="erp-label"><?php echo e(__('Upload file')); ?></label>
                <input type="file" name="file" class="erp-input text-sm" required>
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
<?php $component->withAttributes(['type' => 'submit','variant' => 'primary','size' => 'sm']); ?><?php echo e(__('Upload')); ?> <?php echo $__env->renderComponent(); ?>
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
        <?php endif; ?>
    </section>

    <section class="crm-360__card">
        <h2 class="crm-360__card-title"><?php echo e(__('Artwork Files')); ?></h2>
        <?php if($canArtwork && $commercial['artwork']->isNotEmpty()): ?>
            <ul class="crm-360__mini-list" role="list">
                <?php $__currentLoopData = $commercial['artwork']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>
                        <?php if($row['url']): ?>
                            <a href="<?php echo e($row['url']); ?>" class="crm-360__row-link" data-turbo-frame="erp-main"><?php echo e($row['number']); ?></a>
                        <?php else: ?>
                            <?php echo e($row['number']); ?>

                        <?php endif; ?>
                        <span class="block text-[11px] text-slate-500"><?php echo e($row['status']); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        <?php else: ?>
            <p class="crm-360__empty-inline"><?php echo e(__('No artwork requests linked')); ?></p>
        <?php endif; ?>
    </section>

    <section class="crm-360__card">
        <h2 class="crm-360__card-title"><?php echo e(__('Documents')); ?></h2>
        <p class="text-[11px] text-slate-500"><?php echo e(__('General documents are stored as customer files above.')); ?></p>
    </section>

    <section class="crm-360__card">
        <h2 class="crm-360__card-title"><?php echo e(__('Approvals')); ?></h2>
        <p class="crm-360__empty-inline"><?php echo e(__('Approval documents appear when artwork workflows are connected.')); ?></p>
    </section>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\360\tab-files.blade.php ENDPATH**/ ?>