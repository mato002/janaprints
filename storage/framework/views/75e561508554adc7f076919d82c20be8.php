<div class="grid gap-4">
    <?php echo $__env->make('admin.hr.partials.employee-lookup-select', [
        'employees' => $formData['employees'],
        'selectClass' => 'erp-input w-full',
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div>
        <label class="erp-label" for="training_program_id"><?php echo e(__('Training Program')); ?></label>
        <select id="training_program_id" name="training_program_id" class="erp-input w-full" required>
            <option value=""><?php echo e(__('Select program')); ?></option>
            <?php $__currentLoopData = $formData['programs']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($program->id); ?>" <?php if(old('training_program_id', $selectedProgramId ?? null) == $program->id): echo 'selected'; endif; ?>><?php echo e($program->title); ?> (<?php echo e($program->type->label()); ?>)</option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label class="erp-label" for="due_date"><?php echo e(__('Due Date')); ?></label>
        <input id="due_date" type="date" name="due_date" value="<?php echo e(old('due_date')); ?>" class="erp-input w-full">
    </div>
    <div>
        <label class="erp-label" for="notes"><?php echo e(__('Notes')); ?></label>
        <textarea id="notes" name="notes" rows="3" class="erp-input w-full"><?php echo e(old('notes')); ?></textarea>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\training\assignments\partials\form.blade.php ENDPATH**/ ?>