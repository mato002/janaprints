<p class="text-sm text-slate-600 mb-4"><?php echo e(__('KPIs are calculated automatically from attendance, production, sales, and quality data.')); ?></p>
<div class="grid gap-4 md:grid-cols-2">
    <?php echo $__env->make('admin.hr.partials.employee-lookup-select', [
        'employees' => $formData['employees'],
        'selectClass' => 'erp-input w-full',
        'class' => 'md:col-span-2',
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php $__errorArgs = ['employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600 md:col-span-2"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    <div>
        <label class="erp-label" for="cycle"><?php echo e(__('Appraisal Cycle')); ?></label>
        <select id="cycle" name="cycle" class="erp-input w-full" required>
            <?php $__currentLoopData = $formData['cycles']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cycle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($cycle->value); ?>" <?php if(old('cycle') === $cycle->value): echo 'selected'; endif; ?>><?php echo e($cycle->label()); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['cycle'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div>
        <label class="erp-label" for="rating"><?php echo e(__('Rating (optional override)')); ?></label>
        <select id="rating" name="rating" class="erp-input w-full">
            <option value=""><?php echo e(__('Auto from KPI score')); ?></option>
            <?php $__currentLoopData = $formData['ratings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rating): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($rating->value); ?>" <?php if(old('rating') === $rating->value): echo 'selected'; endif; ?>><?php echo e($rating->label()); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['rating'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div>
        <label class="erp-label" for="period_start"><?php echo e(__('Period Start')); ?></label>
        <input id="period_start" type="date" name="period_start" value="<?php echo e(old('period_start', now()->startOfQuarter()->toDateString())); ?>" class="erp-input w-full" required>
        <?php $__errorArgs = ['period_start'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div>
        <label class="erp-label" for="period_end"><?php echo e(__('Period End')); ?></label>
        <input id="period_end" type="date" name="period_end" value="<?php echo e(old('period_end', now()->endOfQuarter()->toDateString())); ?>" class="erp-input w-full" required>
        <?php $__errorArgs = ['period_end'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="strengths"><?php echo e(__('Strengths')); ?></label>
        <textarea id="strengths" name="strengths" rows="3" class="erp-input w-full"><?php echo e(old('strengths')); ?></textarea>
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="improvements"><?php echo e(__('Areas for Improvement')); ?></label>
        <textarea id="improvements" name="improvements" rows="3" class="erp-input w-full"><?php echo e(old('improvements')); ?></textarea>
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="manager_notes"><?php echo e(__('Manager Notes')); ?></label>
        <textarea id="manager_notes" name="manager_notes" rows="3" class="erp-input w-full"><?php echo e(old('manager_notes')); ?></textarea>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\performance\partials\form.blade.php ENDPATH**/ ?>