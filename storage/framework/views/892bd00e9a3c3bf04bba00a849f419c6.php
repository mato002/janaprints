<?php ($fields = $formFields ?? []); ?>
<?php ($model = $request ?? null); ?>

<?php if(($fields['customer_id']['visible'] ?? true)): ?>
<?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'customer_id','label' => __('Customer'),'options' => $customers,'value' => old('customer_id', $model?->customer_id ?? ($presetCustomerId ?? null)),'required' => ($fields['customer_id']['required'] ?? true),'readonly' => ($fields['customer_id']['read_only'] ?? false),'createRoute' => 'admin.crm.customers.quick-create','refreshRoute' => 'admin.lookups.customers','permission' => 'crm.customers.create','modalTitle' => __('Create customer'),'optionLabelKey' => 'company_name','optionValueKey' => 'id','selectClass' => 'erp-input w-full','placeholder' => __('Select customer')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'customer_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Customer')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($customers),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('customer_id', $model?->customer_id ?? ($presetCustomerId ?? null))),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['customer_id']['required'] ?? true)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['customer_id']['read_only'] ?? false)),'create-route' => 'admin.crm.customers.quick-create','refresh-route' => 'admin.lookups.customers','permission' => 'crm.customers.create','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create customer')),'option-label-key' => 'company_name','option-value-key' => 'id','select-class' => 'erp-input w-full','placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Select customer'))]); ?>
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

<?php if(($fields['quotation_id']['visible'] ?? true)): ?>
<?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'quotation_id','label' => __('Quotation'),'options' => $quotations,'value' => old('quotation_id', $model?->quotation_id),'required' => ($fields['quotation_id']['required'] ?? false),'readonly' => ($fields['quotation_id']['read_only'] ?? false),'createRoute' => 'admin.quotations.quick-create','refreshRoute' => 'admin.lookups.quotations','permission' => 'quotations.create','modalTitle' => __('Create quotation'),'optionLabelKey' => 'quotation_number','optionValueKey' => 'id','selectClass' => 'erp-input w-full','scopeCustomerField' => 'customer_id','placeholder' => __('None')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'quotation_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Quotation')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quotations),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('quotation_id', $model?->quotation_id)),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['quotation_id']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['quotation_id']['read_only'] ?? false)),'create-route' => 'admin.quotations.quick-create','refresh-route' => 'admin.lookups.quotations','permission' => 'quotations.create','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create quotation')),'option-label-key' => 'quotation_number','option-value-key' => 'id','select-class' => 'erp-input w-full','scope-customer-field' => 'customer_id','placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('None'))]); ?>
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

<?php if(($fields['artwork_work_type_id']['visible'] ?? true)): ?>
<?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'artwork_work_type_id','label' => __('Type of work'),'options' => $workTypes,'value' => old('artwork_work_type_id', $model?->artwork_work_type_id),'required' => ($fields['artwork_work_type_id']['required'] ?? true),'readonly' => ($fields['artwork_work_type_id']['read_only'] ?? false),'createRoute' => 'admin.artwork.work-types.quick-create','refreshRoute' => 'admin.lookups.artwork_work_types','permission' => 'artwork.create','modalTitle' => __('Create type of work'),'optionLabelKey' => 'name','optionValueKey' => 'id','selectClass' => 'erp-input w-full','placeholder' => __('Select type of work')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'artwork_work_type_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Type of work')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($workTypes),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('artwork_work_type_id', $model?->artwork_work_type_id)),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['artwork_work_type_id']['required'] ?? true)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['artwork_work_type_id']['read_only'] ?? false)),'create-route' => 'admin.artwork.work-types.quick-create','refresh-route' => 'admin.lookups.artwork_work_types','permission' => 'artwork.create','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create type of work')),'option-label-key' => 'name','option-value-key' => 'id','select-class' => 'erp-input w-full','placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Select type of work'))]); ?>
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

<?php if(($fields['description']['visible'] ?? true)): ?>
<div>
    <label class="erp-label"><?php echo e(__('Description')); ?></label>
    <textarea name="description" class="erp-input w-full" rows="3" <?php if($fields['description']['required'] ?? false): echo 'required'; endif; ?> <?php if($fields['description']['read_only'] ?? false): echo 'readonly'; endif; ?>><?php echo e(old('description', $model?->description ?? ($fields['description']['default'] ?? ''))); ?></textarea>
</div>
<?php endif; ?>

<?php if(($fields['priority']['visible'] ?? true)): ?>
<div>
    <label class="erp-label"><?php echo e(__('Priority')); ?></label>
    <select name="priority" class="erp-input w-full" <?php if($fields['priority']['required'] ?? true): echo 'required'; endif; ?> <?php if($fields['priority']['read_only'] ?? false): echo 'disabled'; endif; ?>>
        <?php $__currentLoopData = $priorities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($priority->value); ?>" <?php if(old('priority', $model?->priority?->value ?? ($fields['priority']['default'] ?? 'normal')) === $priority->value): echo 'selected'; endif; ?>>
                <?php echo e(ucfirst($priority->value)); ?>

            </option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
</div>
<?php endif; ?>

<?php if(($fields['due_date']['visible'] ?? true)): ?>
<div>
    <label class="erp-label"><?php echo e(__('Due date')); ?></label>
    <input type="date" name="due_date" class="erp-input w-full" value="<?php echo e(old('due_date', $model?->due_date?->format('Y-m-d') ?? ($fields['due_date']['default'] ?? ''))); ?>" <?php if($fields['due_date']['required'] ?? false): echo 'required'; endif; ?> <?php if($fields['due_date']['read_only'] ?? false): echo 'readonly'; endif; ?>>
</div>
<?php endif; ?>

<?php if(($fields['start_time']['visible'] ?? true) || ($fields['end_time']['visible'] ?? true)): ?>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <?php if(($fields['start_time']['visible'] ?? true)): ?>
    <div>
        <label class="erp-label"><?php echo e(__('Start time')); ?></label>
        <input
            type="datetime-local"
            name="start_time"
            class="erp-input w-full"
            value="<?php echo e(old('start_time', $model?->start_time?->format('Y-m-d\TH:i') ?? ($fields['start_time']['default'] ?? ''))); ?>"
            <?php if($fields['start_time']['required'] ?? false): echo 'required'; endif; ?>
            <?php if($fields['start_time']['read_only'] ?? false): echo 'readonly'; endif; ?>
        >
    </div>
    <?php endif; ?>
    <?php if(($fields['end_time']['visible'] ?? true)): ?>
    <div>
        <label class="erp-label"><?php echo e(__('End time')); ?></label>
        <input
            type="datetime-local"
            name="end_time"
            class="erp-input w-full"
            value="<?php echo e(old('end_time', $model?->end_time?->format('Y-m-d\TH:i') ?? ($fields['end_time']['default'] ?? ''))); ?>"
            <?php if($fields['end_time']['required'] ?? false): echo 'required'; endif; ?>
            <?php if($fields['end_time']['read_only'] ?? false): echo 'readonly'; endif; ?>
        >
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if(! ($model ?? null)): ?>
<div>
    <label class="erp-label"><?php echo e(__('Photos')); ?></label>
    <input
        type="file"
        name="photos[]"
        class="erp-input w-full"
        accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp"
        multiple
    >
    <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Optional. PNG, JPG, or WEBP.')); ?></p>
</div>
<?php endif; ?>

<?php echo $__env->make('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $model ?? null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/artwork/requests/partials/form.blade.php ENDPATH**/ ?>