<?php
    $selectedModules = old('modules', $delegation->modules ?? []);
    $selectedApprovalTypes = old('approval_types', $delegation->approval_types ?? []);
?>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="space-y-4">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Delegation Parties')); ?></h3>

        <div>
            <label class="erp-label" for="delegator_user_id"><?php echo e(__('Delegator')); ?></label>
            <select id="delegator_user_id" name="delegator_user_id" class="erp-input w-full" required>
                <option value=""><?php echo e(__('Select delegator')); ?></option>
                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($user->id); ?>" <?php if((int) old('delegator_user_id', $delegation->delegator_user_id ?? 0) === $user->id): echo 'selected'; endif; ?>>
                        <?php echo e($user->name); ?> (<?php echo e($user->email); ?>)
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['delegator_user_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div>
            <label class="erp-label" for="delegate_user_id"><?php echo e(__('Delegate')); ?></label>
            <select id="delegate_user_id" name="delegate_user_id" class="erp-input w-full" required>
                <option value=""><?php echo e(__('Select delegate')); ?></option>
                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($user->id); ?>" <?php if((int) old('delegate_user_id', $delegation->delegate_user_id ?? 0) === $user->id): echo 'selected'; endif; ?>>
                        <?php echo e($user->name); ?> (<?php echo e($user->email); ?>)
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div>
            <label class="erp-label" for="reason"><?php echo e(__('Reason')); ?></label>
            <select id="reason" name="reason" class="erp-input w-full" required>
                <?php $__currentLoopData = $reasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>" <?php if(old('reason', $delegation->reason?->value ?? '') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    </div>

    <div class="space-y-4">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Coverage & Period')); ?></h3>

        <div>
            <label class="erp-label"><?php echo e(__('Modules')); ?></label>
            <p class="mb-2 text-xs text-slate-500"><?php echo e(__('Leave unchecked to delegate all modules.')); ?></p>
            <div class="grid gap-2 sm:grid-cols-2">
                <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="modules[]" value="<?php echo e($value); ?>" class="rounded border-erp-border text-erp-accent" <?php if(in_array($value, $selectedModules, true)): echo 'checked'; endif; ?>>
                        <span><?php echo e($label); ?></span>
                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div>
            <label class="erp-label"><?php echo e(__('Approval Types')); ?></label>
            <p class="mb-2 text-xs text-slate-500"><?php echo e(__('Leave unchecked to delegate all approval types.')); ?></p>
            <div class="grid gap-2">
                <?php $__currentLoopData = $approvalTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="approval_types[]" value="<?php echo e($value); ?>" class="rounded border-erp-border text-erp-accent" <?php if(in_array($value, $selectedApprovalTypes, true)): echo 'checked'; endif; ?>>
                        <span><?php echo e($label); ?></span>
                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="erp-label" for="start_date"><?php echo e(__('Start Date')); ?></label>
                <input type="date" id="start_date" name="start_date" value="<?php echo e(old('start_date', optional($delegation->start_date)->format('Y-m-d'))); ?>" class="erp-input w-full" required>
            </div>
            <div>
                <label class="erp-label" for="end_date"><?php echo e(__('End Date')); ?></label>
                <input type="date" id="end_date" name="end_date" value="<?php echo e(old('end_date', optional($delegation->end_date)->format('Y-m-d'))); ?>" class="erp-input w-full" required>
            </div>
        </div>

        <div>
            <label class="erp-label" for="notes"><?php echo e(__('Notes')); ?></label>
            <textarea id="notes" name="notes" rows="3" class="erp-input w-full"><?php echo e(old('notes', $delegation->notes ?? '')); ?></textarea>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\governance\delegations\partials\form.blade.php ENDPATH**/ ?>