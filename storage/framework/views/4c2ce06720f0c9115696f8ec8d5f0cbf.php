<p class="text-sm text-slate-600 mb-4"><?php echo e(__('Store contracts, IDs, statutory records, and HR files.')); ?></p>
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
        <label class="erp-label" for="category"><?php echo e(__('Category')); ?></label>
        <select id="category" name="category" class="erp-input w-full" required>
            <option value=""><?php echo e(__('Select category')); ?></option>
            <?php $__currentLoopData = $formData['categories']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($category->value); ?>" <?php if(old('category') === $category->value): echo 'selected'; endif; ?>><?php echo e($category->label()); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div>
        <label class="erp-label" for="title"><?php echo e(__('Title')); ?></label>
        <input id="title" type="text" name="title" value="<?php echo e(old('title')); ?>" class="erp-input w-full" required>
        <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div>
        <label class="erp-label" for="expires_at"><?php echo e(__('Expiry Date')); ?></label>
        <input id="expires_at" type="date" name="expires_at" value="<?php echo e(old('expires_at')); ?>" class="erp-input w-full">
        <?php $__errorArgs = ['expires_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div>
        <label class="erp-label" for="renewal_reminder_days"><?php echo e(__('Renewal Alert (days before)')); ?></label>
        <input id="renewal_reminder_days" type="number" name="renewal_reminder_days" value="<?php echo e(old('renewal_reminder_days', 30)); ?>" min="1" max="365" class="erp-input w-full">
        <?php $__errorArgs = ['renewal_reminder_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="description"><?php echo e(__('Description')); ?></label>
        <textarea id="description" name="description" rows="3" class="erp-input w-full"><?php echo e(old('description')); ?></textarea>
        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="file"><?php echo e(__('File')); ?></label>
        <input id="file" type="file" name="file" class="erp-input w-full" required>
        <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="notes"><?php echo e(__('Version Notes')); ?></label>
        <input id="notes" type="text" name="notes" value="<?php echo e(old('notes')); ?>" class="erp-input w-full" placeholder="<?php echo e(__('Optional notes for this version')); ?>">
        <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\documents\partials\form.blade.php ENDPATH**/ ?>