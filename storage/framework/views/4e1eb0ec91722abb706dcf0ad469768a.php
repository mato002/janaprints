<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Onboarding')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Employee Onboarding'),'description' => $onboarding->application->candidate->full_name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Employee Onboarding')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($onboarding->application->candidate->full_name)]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <span class="erp-badge bg-slate-100 text-slate-700"><?php echo e($onboarding->status->label()); ?></span>
            <a href="<?php echo e(route('admin.hr.recruitment.applications.show', $onboarding->application)); ?>" class="erp-btn-secondary text-xs"><?php echo e(__('Application')); ?></a>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $attributes = $__attributesOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__attributesOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $component = $__componentOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__componentOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $onboarding)): ?>
        <form method="POST" action="<?php echo e(route('admin.hr.recruitment.onboarding.update', $onboarding)); ?>" class="erp-card max-w-4xl">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="erp-label" for="employee_number"><?php echo e(__('Employee Number')); ?></label>
                    <input id="employee_number" type="text" name="employee_number" value="<?php echo e(old('employee_number', $onboarding->employee_number)); ?>" class="erp-input w-full" required>
                </div>
                <div>
                    <label class="erp-label" for="hire_date"><?php echo e(__('Hire Date')); ?></label>
                    <input id="hire_date" type="date" name="hire_date" value="<?php echo e(old('hire_date', $onboarding->hire_date?->format('Y-m-d'))); ?>" class="erp-input w-full">
                </div>
                <div>
                    <label class="erp-label" for="branch_id"><?php echo e(__('Branch')); ?></label>
                    <select id="branch_id" name="branch_id" class="erp-input w-full">
                        <option value=""><?php echo e(__('Select')); ?></option>
                        <?php $__currentLoopData = $formData['branches']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($branch->id); ?>" <?php if(old('branch_id', $onboarding->branch_id) == $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="erp-label" for="department_id"><?php echo e(__('Department')); ?></label>
                    <select id="department_id" name="department_id" class="erp-input w-full">
                        <option value=""><?php echo e(__('Select')); ?></option>
                        <?php $__currentLoopData = $formData['departments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($department->id); ?>" <?php if(old('department_id', $onboarding->department_id) == $department->id): echo 'selected'; endif; ?>><?php echo e($department->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="erp-label" for="job_title_id"><?php echo e(__('Job Title')); ?></label>
                    <select id="job_title_id" name="job_title_id" class="erp-input w-full">
                        <option value=""><?php echo e(__('Select')); ?></option>
                        <?php $__currentLoopData = $formData['jobTitles']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jobTitle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($jobTitle->id); ?>" <?php if(old('job_title_id', $onboarding->job_title_id) == $jobTitle->id): echo 'selected'; endif; ?>><?php echo e($jobTitle->title); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="erp-label" for="supervisor_employee_id"><?php echo e(__('Supervisor')); ?></label>
                    <select id="supervisor_employee_id" name="supervisor_employee_id" class="erp-input w-full">
                        <option value=""><?php echo e(__('Select')); ?></option>
                        <?php $__currentLoopData = $formData['employees']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($employee->id); ?>" <?php if(old('supervisor_employee_id', $onboarding->supervisor_employee_id) == $employee->id): echo 'selected'; endif; ?>><?php echo e($employee->full_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="md:col-span-2 flex flex-wrap gap-4">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="documents_collected" value="1" <?php if(old('documents_collected', $onboarding->documents_collected)): echo 'checked'; endif; ?>>
                        <span><?php echo e(__('Documents Collected')); ?></span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="system_access_granted" value="1" <?php if(old('system_access_granted', $onboarding->system_access_granted)): echo 'checked'; endif; ?>>
                        <span><?php echo e(__('System Access Granted')); ?></span>
                    </label>
                </div>
                <div class="md:col-span-2">
                    <label class="erp-label" for="notes"><?php echo e(__('Notes')); ?></label>
                    <textarea id="notes" name="notes" rows="2" class="erp-input w-full"><?php echo e(old('notes', $onboarding->notes)); ?></textarea>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="erp-btn-secondary"><?php echo e(__('Save')); ?></button>
            </div>
        </form>

        <?php if($onboarding->status !== \App\Enums\OnboardingStatus::Completed): ?>
            <form method="POST" action="<?php echo e(route('admin.hr.recruitment.onboarding.complete', $onboarding)); ?>" class="mt-4">
                <?php echo csrf_field(); ?>
                <button type="submit" class="erp-btn-primary"><?php echo e(__('Complete & Create Employee')); ?></button>
            </form>
        <?php endif; ?>
    <?php endif; ?>

    <?php if($onboarding->employee): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-6','title' => __('Created Employee')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Created Employee'))]); ?>
            <p class="text-sm"><?php echo e($onboarding->employee->full_name); ?> (<?php echo e($onboarding->employee->employee_number); ?>)</p>
            <a href="<?php echo e(route('admin.hr.employees.show', $onboarding->employee)); ?>" class="erp-btn-secondary mt-3 inline-block text-xs"><?php echo e(__('Employee 360')); ?></a>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\recruitment\onboarding\show.blade.php ENDPATH**/ ?>