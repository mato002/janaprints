<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Communications\Inbox\CommunicationConversation::class)): ?>
    <div class="space-y-1.5">
        <form method="GET" class="flex gap-1" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>">
            <?php $__currentLoopData = request()->except(['pick_q', 'page']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(is_scalar($value) && $value !== ''): ?>
                    <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>">
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <input type="search" name="pick_q" value="<?php echo e($pickQ ?? ''); ?>" class="erp-input min-w-0 flex-1 text-xs"
                   placeholder="<?php echo e(__('Customer name, code, phone…')); ?>">
            <button type="submit" class="erp-btn erp-btn--secondary erp-btn--xs shrink-0"><?php echo e(__('Find')); ?></button>
        </form>

        <form method="POST" action="<?php echo e(route('admin.communications.inbox.start')); ?>" class="flex gap-1" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>">
            <?php echo csrf_field(); ?>
            <select name="customer_id" class="erp-input min-w-0 flex-1 text-xs" required>
                <option value=""><?php echo e(__('Select customer…')); ?></option>
                <?php $__empty_1 = true; $__currentLoopData = $pickCustomers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <option value="<?php echo e($customer->id); ?>">
                        <?php echo e($customer->company_name); ?>

                        <?php if($customer->customer_code): ?> (<?php echo e($customer->customer_code); ?>) <?php endif; ?>
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <option value="" disabled><?php echo e(__('Search to find customers')); ?></option>
                <?php endif; ?>
            </select>
            <button type="submit" class="erp-btn erp-btn--primary erp-btn--xs shrink-0" <?php if($pickCustomers->isEmpty()): echo 'disabled'; endif; ?>>
                <?php echo e(__('Open')); ?>

            </button>
        </form>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('crm.customers.view')): ?>
            <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['variant' => 'ghost','size' => 'xs','href' => route('admin.crm.customers.index'),'class' => 'w-full justify-center','dataTurboFrame' => 'erp-main']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','size' => 'xs','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.index')),'class' => 'w-full justify-center','data-turbo-frame' => 'erp-main']); ?>
                <?php echo e(__('All customers')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\partials\start-conversation.blade.php ENDPATH**/ ?>