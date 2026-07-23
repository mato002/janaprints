<?php if (isset($component)) { $__componentOriginald3ad0f200dc20b794011e332a16c068d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3ad0f200dc20b794011e332a16c068d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal-form','data' => ['title' => __('Create quotation'),'breadcrumbs' => [['label' => __('Quotations'), 'url' => route('admin.quotations.index')], ['label' => __('Create')]],'maxWidth' => '5xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create quotation')),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => __('Quotations'), 'url' => route('admin.quotations.index')], ['label' => __('Create')]]),'maxWidth' => '5xl']); ?>
    <?php if (isset($component)) { $__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-shell','data' => ['action' => route('admin.quotations.store'),'class' => 'space-y-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.quotations.store')),'class' => 'space-y-6']); ?>
        <?php if(request('from') === 'sales-desk'): ?>
            <input type="hidden" name="from" value="sales-desk">
        <?php endif; ?>
        <?php ($fields = $formFields ?? []); ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php if(($fields['customer_id']['visible'] ?? true)): ?>
            <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'customer_id','label' => __('Customer'),'options' => $customers,'value' => old('customer_id', $presetCustomerId ?? null),'required' => ($fields['customer_id']['required'] ?? true),'readonly' => ($fields['customer_id']['read_only'] ?? false),'createRoute' => 'admin.crm.customers.quick-create','refreshRoute' => 'admin.lookups.customers','permission' => 'crm.customers.create','modalTitle' => __('Create customer'),'optionLabelKey' => 'company_name','optionValueKey' => 'id','selectClass' => 'erp-input w-full','emptyOption' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'customer_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Customer')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($customers),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('customer_id', $presetCustomerId ?? null)),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['customer_id']['required'] ?? true)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['customer_id']['read_only'] ?? false)),'create-route' => 'admin.crm.customers.quick-create','refresh-route' => 'admin.lookups.customers','permission' => 'crm.customers.create','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create customer')),'option-label-key' => 'company_name','option-value-key' => 'id','select-class' => 'erp-input w-full','empty-option' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'lead_id','label' => __('Lead (optional)'),'options' => $leads,'value' => old('lead_id', $presetLeadId ?? null),'required' => ($fields['lead_id']['required'] ?? false),'readonly' => ($fields['lead_id']['read_only'] ?? false),'createRoute' => 'admin.crm.leads.quick-create','refreshRoute' => 'admin.lookups.leads','permission' => 'crm.leads.create','modalTitle' => __('Create lead'),'optionLabelKey' => 'lead_name','optionValueKey' => 'id','selectClass' => 'erp-input w-full','placeholder' => __('None')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'lead_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Lead (optional)')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($leads),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('lead_id', $presetLeadId ?? null)),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['lead_id']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['lead_id']['read_only'] ?? false)),'create-route' => 'admin.crm.leads.quick-create','refresh-route' => 'admin.lookups.leads','permission' => 'crm.leads.create','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create lead')),'option-label-key' => 'lead_name','option-value-key' => 'id','select-class' => 'erp-input w-full','placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('None'))]); ?>
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
            <?php echo $__env->make('admin.sales.quotations.partials.artwork-picker-field', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php if(($fields['quotation_date']['visible'] ?? true)): ?>
            <div>
                <label class="erp-label"><?php echo e(__('Quotation date')); ?></label>
                <input type="date" name="quotation_date" class="erp-input" value="<?php echo e(old('quotation_date', now()->toDateString())); ?>" <?php if($fields['quotation_date']['required'] ?? true): echo 'required'; endif; ?> <?php if($fields['quotation_date']['read_only'] ?? false): echo 'readonly'; endif; ?>>
            </div>
            <?php endif; ?>
            <?php if(($fields['valid_until']['visible'] ?? true)): ?>
            <div>
                <label class="erp-label"><?php echo e(__('Valid until')); ?></label>
                <input type="date" name="valid_until" class="erp-input" value="<?php echo e(old('valid_until', $fields['valid_until']['default'] ?? '')); ?>" <?php if($fields['valid_until']['required'] ?? false): echo 'required'; endif; ?> <?php if($fields['valid_until']['read_only'] ?? false): echo 'readonly'; endif; ?>>
            </div>
            <?php endif; ?>
            <?php if(($fields['currency']['visible'] ?? true)): ?>
            <div>
                <label class="erp-label"><?php echo e($fields['currency']['label'] ?? __('Currency')); ?></label>
                <input type="text" name="currency" class="erp-input" value="<?php echo e(old('currency', $fields['currency']['default'] ?? 'KES')); ?>" maxlength="3" <?php if($fields['currency']['required'] ?? true): echo 'required'; endif; ?> <?php if($fields['currency']['read_only'] ?? false): echo 'readonly'; endif; ?>>
            </div>
            <?php endif; ?>
            <?php if(($fields['notes']['visible'] ?? true)): ?>
            <div class="md:col-span-2">
                <label class="erp-label"><?php echo e($fields['notes']['label'] ?? __('Notes')); ?></label>
                <textarea name="notes" class="erp-input w-full" rows="3" <?php if($fields['notes']['required'] ?? false): echo 'required'; endif; ?> <?php if($fields['notes']['read_only'] ?? false): echo 'readonly'; endif; ?>><?php echo e(old('notes')); ?></textarea>
            </div>
            <?php endif; ?>
        </div>
        <?php echo $__env->make('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div>
            <h3 class="font-medium text-slate-800 mb-3"><?php echo e(__('Line items')); ?></h3>
            <?php echo $__env->make('admin.sales.quotations.partials.items-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <?php if (isset($component)) { $__componentOriginald865c6e99253c837baa94b9ed23bdb6d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald865c6e99253c837baa94b9ed23bdb6d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-modal-actions','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-modal-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e(__('Create quotation')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald865c6e99253c837baa94b9ed23bdb6d)): ?>
<?php $attributes = $__attributesOriginald865c6e99253c837baa94b9ed23bdb6d; ?>
<?php unset($__attributesOriginald865c6e99253c837baa94b9ed23bdb6d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald865c6e99253c837baa94b9ed23bdb6d)): ?>
<?php $component = $__componentOriginald865c6e99253c837baa94b9ed23bdb6d; ?>
<?php unset($__componentOriginald865c6e99253c837baa94b9ed23bdb6d); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d)): ?>
<?php $attributes = $__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d; ?>
<?php unset($__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d)): ?>
<?php $component = $__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d; ?>
<?php unset($__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald3ad0f200dc20b794011e332a16c068d)): ?>
<?php $attributes = $__attributesOriginald3ad0f200dc20b794011e332a16c068d; ?>
<?php unset($__attributesOriginald3ad0f200dc20b794011e332a16c068d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald3ad0f200dc20b794011e332a16c068d)): ?>
<?php $component = $__componentOriginald3ad0f200dc20b794011e332a16c068d; ?>
<?php unset($__componentOriginald3ad0f200dc20b794011e332a16c068d); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/quotations/create.blade.php ENDPATH**/ ?>