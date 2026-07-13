<?php
    $employee = $employee ?? null;
    $fields = $formFields ?? [];
?>

<?php if(($fields['company_id']['visible'] ?? true) && auth()->user()->hasRole('Super Admin')): ?>
    <?php if (isset($component)) { $__componentOriginalb030971c7c9708d6349440db7ba2684d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb030971c7c9708d6349440db7ba2684d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-company-select','data' => ['companies' => $companies,'value' => old('company_id', $employee?->company_id),'selectClass' => 'block mt-1 w-full rounded-md border-gray-300','class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-company-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['companies' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($companies),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('company_id', $employee?->company_id)),'select-class' => 'block mt-1 w-full rounded-md border-gray-300','class' => 'mb-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb030971c7c9708d6349440db7ba2684d)): ?>
<?php $attributes = $__attributesOriginalb030971c7c9708d6349440db7ba2684d; ?>
<?php unset($__attributesOriginalb030971c7c9708d6349440db7ba2684d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb030971c7c9708d6349440db7ba2684d)): ?>
<?php $component = $__componentOriginalb030971c7c9708d6349440db7ba2684d; ?>
<?php unset($__componentOriginalb030971c7c9708d6349440db7ba2684d); ?>
<?php endif; ?>
<?php elseif(! auth()->user()->hasRole('Super Admin')): ?>
    <input type="hidden" name="company_id" value="<?php echo e(auth()->user()->company_id); ?>">
<?php endif; ?>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'employee_number','value' => __('Employee number')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'employee_number','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Employee number'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $attributes = $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $component = $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal18c21970322f9e5c938bc954620c12bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18c21970322f9e5c938bc954620c12bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.text-input','data' => ['id' => 'employee_number','type' => 'text','class' => 'block mt-1 w-full bg-slate-50 erp-ref-code','value' => $employee ? old('employee_number', $employee->employee_number) : ($suggestedEmployeeNumber ?? ''),'readonly' => true,'tabindex' => '-1','ariaReadonly' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'employee_number','type' => 'text','class' => 'block mt-1 w-full bg-slate-50 erp-ref-code','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($employee ? old('employee_number', $employee->employee_number) : ($suggestedEmployeeNumber ?? '')),'readonly' => true,'tabindex' => '-1','aria-readonly' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $attributes = $__attributesOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__attributesOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $component = $__componentOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__componentOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
        <p class="mt-1 text-xs text-gray-500">
            <?php if($employee): ?>
                <?php echo e(__('Assigned automatically and cannot be changed.')); ?>

            <?php else: ?>
                <?php echo e(__('Assigned automatically on save using the :prefix prefix (e.g. :prefix-0766).', [
                    'prefix' => $employeeNumberPrefix ?? 'JPEMP',
                ])); ?>

            <?php endif; ?>
        </p>
    </div>
    <?php if(($fields['branch_id']['visible'] ?? true)): ?>
    <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'branch_id','label' => $fields['branch_id']['label'] ?? __('Branch'),'options' => $branches,'value' => old('branch_id', $employee?->branch_id),'required' => ($fields['branch_id']['required'] ?? true),'createRoute' => 'admin.branches.quick-create','refreshRoute' => 'admin.lookups.branches','permission' => 'branches.manage','modalTitle' => __('Create branch'),'optionLabelKey' => 'name','selectClass' => 'block mt-1 w-full rounded-md border-gray-300','scopeCompanyField' => 'company_id','emptyOption' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'branch_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['branch_id']['label'] ?? __('Branch')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($branches),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('branch_id', $employee?->branch_id)),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['branch_id']['required'] ?? true)),'create-route' => 'admin.branches.quick-create','refresh-route' => 'admin.lookups.branches','permission' => 'branches.manage','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create branch')),'option-label-key' => 'name','select-class' => 'block mt-1 w-full rounded-md border-gray-300','scope-company-field' => 'company_id','empty-option' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
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
    <?php if(($fields['department_id']['visible'] ?? true)): ?>
    <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'department_id','label' => $fields['department_id']['label'] ?? __('Department'),'options' => $departments,'value' => old('department_id', $employee?->department_id),'createRoute' => 'admin.departments.quick-create','refreshRoute' => 'admin.lookups.departments','permission' => 'departments.manage','modalTitle' => __('Create department'),'optionLabelKey' => 'name','selectClass' => 'block mt-1 w-full rounded-md border-gray-300','scopeCompanyField' => 'company_id','placeholder' => __('None')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'department_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['department_id']['label'] ?? __('Department')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($departments),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('department_id', $employee?->department_id)),'create-route' => 'admin.departments.quick-create','refresh-route' => 'admin.lookups.departments','permission' => 'departments.manage','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create department')),'option-label-key' => 'name','select-class' => 'block mt-1 w-full rounded-md border-gray-300','scope-company-field' => 'company_id','placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('None'))]); ?>
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
    <?php if(($fields['first_name']['visible'] ?? true)): ?>
    <div><?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'first_name','value' => $fields['first_name']['label'] ?? __('First name')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'first_name','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['first_name']['label'] ?? __('First name'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $attributes = $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $component = $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?><?php if (isset($component)) { $__componentOriginal18c21970322f9e5c938bc954620c12bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18c21970322f9e5c938bc954620c12bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.text-input','data' => ['id' => 'first_name','name' => 'first_name','class' => 'block mt-1 w-full','value' => old('first_name', $employee?->first_name),'required' => $fields['first_name']['required'] ?? true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'first_name','name' => 'first_name','class' => 'block mt-1 w-full','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('first_name', $employee?->first_name)),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['first_name']['required'] ?? true)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $attributes = $__attributesOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__attributesOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $component = $__componentOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__componentOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?></div>
    <?php endif; ?>
    <?php if(($fields['middle_name']['visible'] ?? true)): ?>
    <div><?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'middle_name','value' => $fields['middle_name']['label'] ?? __('Middle name')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'middle_name','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['middle_name']['label'] ?? __('Middle name'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $attributes = $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $component = $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?><?php if (isset($component)) { $__componentOriginal18c21970322f9e5c938bc954620c12bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18c21970322f9e5c938bc954620c12bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.text-input','data' => ['id' => 'middle_name','name' => 'middle_name','class' => 'block mt-1 w-full','value' => old('middle_name', $employee?->middle_name)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'middle_name','name' => 'middle_name','class' => 'block mt-1 w-full','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('middle_name', $employee?->middle_name))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $attributes = $__attributesOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__attributesOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $component = $__componentOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__componentOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?></div>
    <?php endif; ?>
    <?php if(($fields['last_name']['visible'] ?? true)): ?>
    <div><?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'last_name','value' => $fields['last_name']['label'] ?? __('Last name')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'last_name','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['last_name']['label'] ?? __('Last name'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $attributes = $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $component = $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?><?php if (isset($component)) { $__componentOriginal18c21970322f9e5c938bc954620c12bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18c21970322f9e5c938bc954620c12bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.text-input','data' => ['id' => 'last_name','name' => 'last_name','class' => 'block mt-1 w-full','value' => old('last_name', $employee?->last_name),'required' => $fields['last_name']['required'] ?? true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'last_name','name' => 'last_name','class' => 'block mt-1 w-full','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('last_name', $employee?->last_name)),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['last_name']['required'] ?? true)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $attributes = $__attributesOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__attributesOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $component = $__componentOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__componentOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?></div>
    <?php endif; ?>
    <?php if(($fields['job_title_id']['visible'] ?? true)): ?>
    <div><?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'job_title_id','value' => $fields['job_title_id']['label'] ?? __('Job Title')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'job_title_id','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['job_title_id']['label'] ?? __('Job Title'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $attributes = $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $component = $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
        <select name="job_title_id" class="erp-select mt-1 w-full">
            <option value=""><?php echo e(__('Select job title')); ?></option>
            <?php $__currentLoopData = $jobTitles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jobTitle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($jobTitle->id); ?>" <?php if(old('job_title_id', $employee?->job_title_id) == $jobTitle->id): echo 'selected'; endif; ?>><?php echo e($jobTitle->title); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php if($employee?->designation && ! $employee?->job_title_id): ?>
            <p class="mt-1 text-xs text-amber-700"><?php echo e(__('Legacy designation')); ?>: <?php echo e($employee->designation); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if(($fields['employment_status']['visible'] ?? true)): ?>
    <div><?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'employment_status','value' => $fields['employment_status']['label'] ?? __('Employment status')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'employment_status','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['employment_status']['label'] ?? __('Employment status'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $attributes = $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $component = $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
        <select name="employment_status" class="block mt-1 w-full rounded-md border-gray-300" <?php if($fields['employment_status']['required'] ?? true): echo 'required'; endif; ?>>
            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($status->value); ?>" <?php if(old('employment_status', $employee?->employment_status?->value) === $status->value): echo 'selected'; endif; ?>><?php echo e($status->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select></div>
    <?php endif; ?>
    <?php if(! $employee): ?>
        <div>
            <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'hire_date','value' => __('Hire date')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'hire_date','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Hire date'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $attributes = $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $component = $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
            <input
                id="hire_date"
                name="hire_date"
                type="date"
                class="erp-input mt-1 w-full"
                value="<?php echo e(old('hire_date', now()->toDateString())); ?>"
            />
        </div>
    <?php else: ?>
        <div>
            <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'hire_date','value' => __('Hire date')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'hire_date','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Hire date'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $attributes = $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $component = $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
            <input
                id="hire_date"
                name="hire_date"
                type="date"
                class="erp-input mt-1 w-full"
                value="<?php echo e(old('hire_date', $employee->hire_date?->toDateString())); ?>"
            />
        </div>
    <?php endif; ?>
    <?php if(! $employee && ($payrollClasses ?? collect())->isNotEmpty()): ?>
        <div>
            <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'salary_template_id','value' => __('Payroll class')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'salary_template_id','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Payroll class'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $attributes = $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $component = $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
            <select id="salary_template_id" name="salary_template_id" class="erp-select mt-1 w-full">
                <option value=""><?php echo e(__('None — set salary later')); ?></option>
                <?php $__currentLoopData = $payrollClasses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payrollClass): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option
                        value="<?php echo e($payrollClass->id); ?>"
                        <?php if((int) old('salary_template_id') === $payrollClass->id): echo 'selected'; endif; ?>
                    >
                        <?php echo e($payrollClass->name); ?> — <?php echo e(number_format($payrollClass->grossComponents(), 2)); ?> <?php echo e($payrollClass->currency); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <p class="mt-1 text-xs text-gray-500">
                <?php echo e(__('Applies basic salary and standard allowances from the class. Adjust per employee later in the salary register.')); ?>

                <a href="<?php echo e(route('admin.hr.compensation.templates')); ?>" class="text-erp-accent hover:underline" data-turbo="false"><?php echo e(__('Manage payroll classes')); ?></a>
            </p>
        </div>
    <?php elseif(! $employee && auth()->user()?->can('create', App\Models\Hr\EmployeeCompensation::class)): ?>
        <div class="md:col-span-2 rounded-lg border border-dashed border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
            <?php echo e(__('No payroll classes yet.')); ?>

            <a href="<?php echo e(route('admin.hr.compensation.templates.create')); ?>" class="font-medium text-erp-accent hover:underline"><?php echo e(__('Create a payroll class')); ?></a>
            <?php echo e(__('to auto-set salary during onboarding.')); ?>

        </div>
    <?php endif; ?>
    <div>
        <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'email','value' => __('Personal email (login & onboarding)')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'email','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Personal email (login & onboarding)'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $attributes = $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $component = $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
        <input
            id="email"
            name="email"
            type="email"
            class="erp-input mt-1 w-full"
            value="<?php echo e(old('email', $employee?->email)); ?>"
            autocomplete="email"
            <?php if(! $employee): ?> required <?php endif; ?>
        />
        <p class="mt-1 text-xs text-gray-500"><?php echo e(__('Used for invitations, activation, and ERP login.')); ?></p>
    </div>
    <div><?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'phone','value' => __('Phone')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'phone','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Phone'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $attributes = $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $component = $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?><?php if (isset($component)) { $__componentOriginal18c21970322f9e5c938bc954620c12bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18c21970322f9e5c938bc954620c12bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.text-input','data' => ['id' => 'phone','name' => 'phone','class' => 'block mt-1 w-full','value' => old('phone', $employee?->phone)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'phone','name' => 'phone','class' => 'block mt-1 w-full','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('phone', $employee?->phone))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $attributes = $__attributesOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__attributesOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $component = $__componentOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__componentOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?></div>
    <?php if(($canAssignActivationRole ?? false) && ! $employee): ?>
        <div>
            <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'activation_role','value' => __('System role')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'activation_role','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('System role'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $attributes = $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $component = $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
            <select id="activation_role" name="activation_role" class="erp-select mt-1 w-full">
                <option value=""><?php echo e(__('Use job title / department / default mapping')); ?></option>
                <?php $__currentLoopData = ($assignableRoles ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($role->name); ?>" <?php if(old('activation_role') === $role->name): echo 'selected'; endif; ?>><?php echo e($role->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <p class="mt-1 text-xs text-gray-500"><?php echo e(__('Optional. Applied when the employee activates their account.')); ?></p>
        </div>
    <?php endif; ?>
</div>

<?php echo $__env->make('admin.employees.partials.form-fields-profile', ['employee' => $employee], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<label class="flex gap-2 mt-4"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" <?php if(old('is_active', $employee?->is_active ?? true)): echo 'checked'; endif; ?>> <?php echo e(__('Active')); ?></label>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/employees/partials/form-fields.blade.php ENDPATH**/ ?>