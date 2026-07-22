<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $quoteRequest)): ?>
    <section id="qr-360-review" class="qr-360__card qr-360__card--compact">
        <h2 class="qr-360__card-title"><?php echo e(__('Sales Review')); ?></h2>

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
                <label class="qr-360__label"><?php echo e(__('Assigned Salesperson')); ?></label>
                <select name="assigned_to" class="erp-input w-full text-sm">
                    <option value=""><?php echo e(__('Unassigned')); ?></option>
                    <?php $__currentLoopData = $workspace['assignable_users']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($user->id); ?>" <?php if($quoteRequest->assigned_to == $user->id): echo 'selected'; endif; ?>><?php echo e($user->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label class="qr-360__label"><?php echo e(__('Expected Value')); ?></label>
                <input type="number" step="0.01" min="0" name="expected_value" value="<?php echo e(old('expected_value', $quoteRequest->expected_value)); ?>" class="erp-input w-full text-sm" placeholder="0.00">
            </div>

            <div>
                <label class="qr-360__label"><?php echo e(__('Follow-up Date')); ?></label>
                <input type="date" name="target_follow_up_at" value="<?php echo e(old('target_follow_up_at', $quoteRequest->target_follow_up_at?->format('Y-m-d'))); ?>" class="erp-input w-full text-sm">
            </div>

            <div>
                <label class="qr-360__label"><?php echo e(__('Probability %')); ?></label>
                <input type="number" min="0" max="100" name="probability" value="<?php echo e(old('probability', $quoteRequest->probability)); ?>" class="erp-input w-full text-sm" placeholder="0">
            </div>

            <div class="qr-360__review-actions">
                <button type="submit" class="crm-360__btn crm-360__btn--primary crm-360__btn--sm"><?php echo e(__('Save Review')); ?></button>
            </div>
        </form>
    </section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\customer-service\quote-requests\workspace\sales-review.blade.php ENDPATH**/ ?>