<?php ($fields = $formFields ?? []); ?>

<div class="erp-form-grid">
    <?php if(($fields['warehouse_id']['visible'] ?? true)): ?>
        <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'warehouse_id','label' => $fields['warehouse_id']['label'] ?? __('From store'),'options' => $warehouses,'value' => old('warehouse_id'),'required' => ($fields['warehouse_id']['required'] ?? true),'createRoute' => 'admin.inventory.warehouses.quick-create','refreshRoute' => 'admin.lookups.warehouses','permission' => 'inventory.create','modalTitle' => __('Create warehouse'),'optionLabelKey' => 'name','selectClass' => 'erp-select mt-1','emptyOption' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'warehouse_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['warehouse_id']['label'] ?? __('From store')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($warehouses),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('warehouse_id')),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['warehouse_id']['required'] ?? true)),'create-route' => 'admin.inventory.warehouses.quick-create','refresh-route' => 'admin.lookups.warehouses','permission' => 'inventory.create','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create warehouse')),'option-label-key' => 'name','select-class' => 'erp-select mt-1','empty-option' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
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
    <?php if(($fields['to_warehouse_id']['visible'] ?? true)): ?>
        <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'to_warehouse_id','label' => $fields['to_warehouse_id']['label'] ?? __('To store'),'options' => $warehouses,'value' => old('to_warehouse_id'),'required' => ($fields['to_warehouse_id']['required'] ?? true),'createRoute' => 'admin.inventory.warehouses.quick-create','refreshRoute' => 'admin.lookups.warehouses','permission' => 'inventory.create','modalTitle' => __('Create warehouse'),'optionLabelKey' => 'name','selectClass' => 'erp-select mt-1','emptyOption' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'to_warehouse_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['to_warehouse_id']['label'] ?? __('To store')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($warehouses),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('to_warehouse_id')),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['to_warehouse_id']['required'] ?? true)),'create-route' => 'admin.inventory.warehouses.quick-create','refresh-route' => 'admin.lookups.warehouses','permission' => 'inventory.create','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create warehouse')),'option-label-key' => 'name','select-class' => 'erp-select mt-1','empty-option' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
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
    <?php if(($fields['issue_date']['visible'] ?? true)): ?>
        <div>
            <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'issue_date','value' => $fields['issue_date']['label'] ?? __('Transfer date')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'issue_date','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['issue_date']['label'] ?? __('Transfer date'))]); ?>
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
            <x-text-input id="issue_date" name="issue_date" type="date" class="block mt-1 w-full" :value="old('issue_date', now()->toDateString())" <?php if($fields['issue_date']['required'] ?? true): echo 'required'; endif; ?> />
        </div>
    <?php endif; ?>
    <?php if(($fields['notes']['visible'] ?? true)): ?>
        <div class="md:col-span-2">
            <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'notes','value' => $fields['notes']['label'] ?? __('Notes')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'notes','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['notes']['label'] ?? __('Notes'))]); ?>
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
            <textarea id="notes" name="notes" class="erp-input mt-1 w-full" rows="2" <?php if($fields['notes']['required'] ?? false): echo 'required'; endif; ?>><?php echo e(old('notes')); ?></textarea>
        </div>
    <?php endif; ?>
</div>

<?php echo $__env->make('admin.inventory.partials.line-items', ['items' => $items, 'formFields' => $formFields, 'dynamic' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\inventory\transfers\partials\form.blade.php ENDPATH**/ ?>