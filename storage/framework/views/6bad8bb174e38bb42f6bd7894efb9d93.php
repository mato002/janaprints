<p class="text-sm text-slate-600 mb-4"><?php echo e(__('Starts offboarding with clearance checklist and final dues calculation.')); ?></p>
<div class="grid gap-4 md:grid-cols-2">
    <?php echo $__env->make('admin.hr.partials.employee-lookup-select', [
        'employees' => $formData['employees'],
        'selectClass' => 'erp-input w-full',
        'class' => 'md:col-span-2',
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div>
        <label class="erp-label" for="exit_type"><?php echo e(__('Exit Type')); ?></label>
        <select id="exit_type" name="exit_type" class="erp-input w-full" required>
            <?php $__currentLoopData = $formData['exitTypes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($type->value); ?>" <?php if(old('exit_type') === $type->value): echo 'selected'; endif; ?>><?php echo e($type->label()); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label class="erp-label" for="last_working_date"><?php echo e(__('Last Working Date')); ?></label>
        <input id="last_working_date" type="date" name="last_working_date" value="<?php echo e(old('last_working_date')); ?>" class="erp-input w-full" required>
    </div>
    <div>
        <label class="erp-label" for="exit_date"><?php echo e(__('Exit Date')); ?></label>
        <input id="exit_date" type="date" name="exit_date" value="<?php echo e(old('exit_date')); ?>" class="erp-input w-full">
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="reason"><?php echo e(__('Reason')); ?></label>
        <textarea id="reason" name="reason" rows="3" class="erp-input w-full"><?php echo e(old('reason')); ?></textarea>
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="notes"><?php echo e(__('Notes')); ?></label>
        <textarea id="notes" name="notes" rows="2" class="erp-input w-full"><?php echo e(old('notes')); ?></textarea>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\exit\partials\form.blade.php ENDPATH**/ ?>