<div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <label class="erp-label" for="title"><?php echo e(__('Title')); ?></label>
        <input id="title" type="text" name="title" value="<?php echo e(old('title')); ?>" class="erp-input w-full" required>
    </div>
    <div>
        <label class="erp-label" for="job_requisition_id"><?php echo e(__('Requisition')); ?></label>
        <select id="job_requisition_id" name="job_requisition_id" class="erp-input w-full">
            <option value=""><?php echo e(__('None')); ?></option>
            <?php $__currentLoopData = $formData['requisitions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $requisition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($requisition->id); ?>" <?php if(old('job_requisition_id') == $requisition->id): echo 'selected'; endif; ?>><?php echo e($requisition->reference); ?> — <?php echo e($requisition->title); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label class="erp-label" for="positions"><?php echo e(__('Positions')); ?></label>
        <input id="positions" type="number" name="positions" value="<?php echo e(old('positions', 1)); ?>" min="1" class="erp-input w-full">
    </div>
    <div>
        <label class="erp-label" for="department_id"><?php echo e(__('Department')); ?></label>
        <select id="department_id" name="department_id" class="erp-input w-full">
            <option value=""><?php echo e(__('Select')); ?></option>
            <?php $__currentLoopData = $formData['departments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($department->id); ?>" <?php if(old('department_id') == $department->id): echo 'selected'; endif; ?>><?php echo e($department->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label class="erp-label" for="job_title_id"><?php echo e(__('Job Title')); ?></label>
        <select id="job_title_id" name="job_title_id" class="erp-input w-full">
            <option value=""><?php echo e(__('Select')); ?></option>
            <?php $__currentLoopData = $formData['jobTitles']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jobTitle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($jobTitle->id); ?>" <?php if(old('job_title_id') == $jobTitle->id): echo 'selected'; endif; ?>><?php echo e($jobTitle->title); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="description"><?php echo e(__('Description')); ?></label>
        <textarea id="description" name="description" rows="3" class="erp-input w-full"><?php echo e(old('description')); ?></textarea>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\recruitment\partials\vacancy-form.blade.php ENDPATH**/ ?>