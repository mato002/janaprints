<?php
    $activity = $activity ?? null;
    $fields = $formFields ?? [];
?>

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <?php if(($fields['customer_id']['visible'] ?? true)): ?>
        <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'customer_id','label' => $fields['customer_id']['label'] ?? __('Customer'),'options' => $customers,'value' => old('customer_id', $presetCustomerId ?? $activity?->customer_id),'required' => ($fields['customer_id']['required'] ?? false),'readonly' => ($fields['customer_id']['read_only'] ?? false),'createRoute' => 'admin.crm.customers.quick-create','refreshRoute' => 'admin.lookups.customers','permission' => 'crm.customers.create','modalTitle' => __('Create customer'),'optionLabelKey' => 'company_name','optionValueKey' => 'id','selectClass' => 'erp-input mt-1 w-full','placeholder' => __('— None —')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'customer_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['customer_id']['label'] ?? __('Customer')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($customers),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('customer_id', $presetCustomerId ?? $activity?->customer_id)),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['customer_id']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['customer_id']['read_only'] ?? false)),'create-route' => 'admin.crm.customers.quick-create','refresh-route' => 'admin.lookups.customers','permission' => 'crm.customers.create','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create customer')),'option-label-key' => 'company_name','option-value-key' => 'id','select-class' => 'erp-input mt-1 w-full','placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('— None —'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $attributes = $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $component = $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
    <?php endif; ?>
    <?php if(($fields['lead_id']['visible'] ?? true)): ?>
        <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'lead_id','label' => $fields['lead_id']['label'] ?? __('Lead'),'options' => $leads,'value' => old('lead_id', $presetLeadId ?? $activity?->lead_id),'required' => ($fields['lead_id']['required'] ?? false),'readonly' => ($fields['lead_id']['read_only'] ?? false),'createRoute' => 'admin.crm.leads.quick-create','refreshRoute' => 'admin.lookups.leads','permission' => 'crm.leads.create','modalTitle' => __('Create lead'),'optionLabelKey' => 'lead_name','optionValueKey' => 'id','selectClass' => 'erp-input mt-1 w-full','placeholder' => __('— None —')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'lead_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['lead_id']['label'] ?? __('Lead')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($leads),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('lead_id', $presetLeadId ?? $activity?->lead_id)),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['lead_id']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['lead_id']['read_only'] ?? false)),'create-route' => 'admin.crm.leads.quick-create','refresh-route' => 'admin.lookups.leads','permission' => 'crm.leads.create','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create lead')),'option-label-key' => 'lead_name','option-value-key' => 'id','select-class' => 'erp-input mt-1 w-full','placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('— None —'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $attributes = $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $component = $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
    <?php endif; ?>
    <?php if(($fields['activity_type']['visible'] ?? true)): ?>
        <div>
            <label class="text-sm font-medium text-slate-700"><?php echo e($fields['activity_type']['label'] ?? __('Activity type')); ?></label>
            <select name="activity_type" class="erp-input mt-1 w-full" <?php if($fields['activity_type']['required'] ?? true): echo 'required'; endif; ?> <?php if($fields['activity_type']['read_only'] ?? false): echo 'disabled'; endif; ?>>
                <?php $__currentLoopData = $activityTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($type->value); ?>" <?php if(old('activity_type', $activity?->activity_type?->value) === $type->value): echo 'selected'; endif; ?>><?php echo e(ucfirst(str_replace('_', ' ', $type->value))); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    <?php endif; ?>
    <?php if(($fields['status']['visible'] ?? true)): ?>
        <?php if (isset($component)) { $__componentOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-status-select','data' => ['formKey' => 'activity.create','field' => $fields['status'],'value' => $activity?->status ?? ($fields['status']['default'] ?? 'completed'),'model' => $activity]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-status-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['form-key' => 'activity.create','field' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['status']),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activity?->status ?? ($fields['status']['default'] ?? 'completed')),'model' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activity)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8)): ?>
<?php $attributes = $__attributesOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8; ?>
<?php unset($__attributesOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8)): ?>
<?php $component = $__componentOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8; ?>
<?php unset($__componentOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8); ?>
<?php endif; ?>
    <?php endif; ?>
    <?php if(($fields['user_id']['visible'] ?? true)): ?>
        <div>
            <label class="text-sm font-medium text-slate-700"><?php echo e($fields['user_id']['label'] ?? __('Assigned to')); ?></label>
            <select name="user_id" class="erp-input mt-1 w-full" <?php if($fields['user_id']['required'] ?? false): echo 'required'; endif; ?> <?php if($fields['user_id']['read_only'] ?? false): echo 'disabled'; endif; ?>>
                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($user->id); ?>" <?php if(old('user_id', $activity?->user_id ?? auth()->id()) == $user->id): echo 'selected'; endif; ?>><?php echo e($user->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    <?php endif; ?>
    <?php if(($fields['activity_at']['visible'] ?? true)): ?>
        <div>
            <label class="text-sm font-medium text-slate-700"><?php echo e($fields['activity_at']['label'] ?? __('When')); ?></label>
            <input type="datetime-local" name="activity_at" class="erp-input mt-1 w-full" <?php if($fields['activity_at']['required'] ?? true): echo 'required'; endif; ?> <?php if($fields['activity_at']['read_only'] ?? false): echo 'readonly'; endif; ?> value="<?php echo e(old('activity_at', ($activity?->activity_at ?? now())->format('Y-m-d\TH:i'))); ?>">
        </div>
    <?php endif; ?>
    <?php if(($fields['subject']['visible'] ?? true)): ?>
        <div class="md:col-span-2">
            <label class="text-sm font-medium text-slate-700"><?php echo e($fields['subject']['label'] ?? __('Subject')); ?></label>
            <input type="text" name="subject" class="erp-input mt-1 w-full" maxlength="255" <?php if($fields['subject']['required'] ?? true): echo 'required'; endif; ?> <?php if($fields['subject']['read_only'] ?? false): echo 'readonly'; endif; ?> value="<?php echo e(old('subject', $activity?->subject)); ?>">
        </div>
    <?php endif; ?>
    <?php if(($fields['description']['visible'] ?? true)): ?>
        <div class="md:col-span-2">
            <label class="text-sm font-medium text-slate-700"><?php echo e($fields['description']['label'] ?? __('Description')); ?></label>
            <textarea name="description" rows="4" class="erp-input mt-1 w-full" <?php if($fields['description']['required'] ?? false): echo 'required'; endif; ?> <?php if($fields['description']['read_only'] ?? false): echo 'readonly'; endif; ?>><?php echo e(old('description', $activity?->description)); ?></textarea>
        </div>
    <?php endif; ?>
</div>
<?php echo $__env->make('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $activity ?? null, 'formKey' => 'activity.create'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\commercial\activities\partials\form.blade.php ENDPATH**/ ?>