<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $quoteRequest)): ?>
    <?php if (isset($component)) { $__componentOriginalb327e04d2aba66fca2df8a26a48e286d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb327e04d2aba66fca2df8a26a48e286d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.record-workspace.section','data' => ['id' => 'qr-360-review','title' => __('Sales review'),'tone' => 'edit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.record-workspace.section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'qr-360-review','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Sales review')),'tone' => 'edit']); ?>
        <form method="POST" action="<?php echo e(route('admin.public-quote-requests.update-review', $quoteRequest)); ?>" class="qr-360__review-grid">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PATCH'); ?>

            <div>
                <label class="qr-360__label"><?php echo e(__('Status')); ?></label>
                <select name="status" class="erp-input w-full text-sm">
                    <?php $__currentLoopData = App\Enums\PublicQuoteRequestStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($status->value); ?>" <?php if($quoteRequest->status === $status): echo 'selected'; endif; ?>><?php echo e($status->workspaceLabel()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label class="qr-360__label"><?php echo e(__('Priority')); ?></label>
                <select name="priority" class="erp-input w-full text-sm">
                    <option value=""><?php echo e(__('Not set')); ?></option>
                    <?php $__currentLoopData = App\Enums\PublicQuoteRequestPriority::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($priority->value); ?>" <?php if($quoteRequest->priority === $priority): echo 'selected'; endif; ?>><?php echo e($priority->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label class="qr-360__label"><?php echo e(__('Assigned salesperson')); ?></label>
                <select name="assigned_to" class="erp-input w-full text-sm">
                    <option value=""><?php echo e(__('Unassigned')); ?></option>
                    <?php $__currentLoopData = $workspace['assignable_users']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($user->id); ?>" <?php if($quoteRequest->assigned_to == $user->id): echo 'selected'; endif; ?>><?php echo e($user->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label class="qr-360__label"><?php echo e(__('Expected value')); ?></label>
                <input type="number" step="0.01" min="0" name="expected_value" value="<?php echo e(old('expected_value', $quoteRequest->expected_value)); ?>" class="erp-input w-full text-sm" placeholder="0.00">
            </div>

            <div>
                <label class="qr-360__label"><?php echo e(__('Follow-up date')); ?></label>
                <input type="date" name="target_follow_up_at" value="<?php echo e(old('target_follow_up_at', $quoteRequest->target_follow_up_at?->format('Y-m-d'))); ?>" class="erp-input w-full text-sm">
            </div>

            <div>
                <label class="qr-360__label"><?php echo e(__('Probability %')); ?></label>
                <input type="number" min="0" max="100" name="probability" value="<?php echo e(old('probability', $quoteRequest->probability)); ?>" class="erp-input w-full text-sm" placeholder="0">
            </div>

            <div class="qr-360__review-actions">
                <button type="submit" class="crm-360__btn crm-360__btn--primary crm-360__btn--sm"><?php echo e(__('Save review')); ?></button>
            </div>
        </form>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb327e04d2aba66fca2df8a26a48e286d)): ?>
<?php $attributes = $__attributesOriginalb327e04d2aba66fca2df8a26a48e286d; ?>
<?php unset($__attributesOriginalb327e04d2aba66fca2df8a26a48e286d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb327e04d2aba66fca2df8a26a48e286d)): ?>
<?php $component = $__componentOriginalb327e04d2aba66fca2df8a26a48e286d; ?>
<?php unset($__componentOriginalb327e04d2aba66fca2df8a26a48e286d); ?>
<?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/customer-service/quote-requests/workspace/sales-review.blade.php ENDPATH**/ ?>