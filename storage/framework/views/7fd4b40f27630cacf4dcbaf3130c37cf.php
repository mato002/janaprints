<?php ($m = $subcategory ?? null); ?>
<?php ($defaultCategoryId = $defaultCategoryId ?? null); ?>
<div class="erp-form-grid">
    <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'inventory_category_id','label' => __('Category'),'options' => $categories,'value' => old('inventory_category_id', $m?->inventory_category_id ?? $defaultCategoryId),'required' => true,'createRoute' => 'admin.inventory.catalogue.categories.quick-create','refreshRoute' => 'admin.lookups.categories','permission' => 'catalogue.create','modalTitle' => __('Create category'),'optionLabelKey' => 'name','optionValueKey' => 'id','selectClass' => 'erp-select w-full','emptyOption' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'inventory_category_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Category')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($categories),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('inventory_category_id', $m?->inventory_category_id ?? $defaultCategoryId)),'required' => true,'create-route' => 'admin.inventory.catalogue.categories.quick-create','refresh-route' => 'admin.lookups.categories','permission' => 'catalogue.create','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create category')),'option-label-key' => 'name','option-value-key' => 'id','select-class' => 'erp-select w-full','empty-option' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
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
    <div><label class="erp-label"><?php echo e(__('Name')); ?></label><input name="name" class="erp-input w-full" value="<?php echo e(old('name', $m?->name)); ?>" required></div>
    <?php if (isset($component)) { $__componentOriginal6da14397ddf3530b276d246dba7e4584 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6da14397ddf3530b276d246dba7e4584 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.entity-code-input','data' => ['record' => $m,'erp' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.entity-code-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m),'erp' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6da14397ddf3530b276d246dba7e4584)): ?>
<?php $attributes = $__attributesOriginal6da14397ddf3530b276d246dba7e4584; ?>
<?php unset($__attributesOriginal6da14397ddf3530b276d246dba7e4584); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6da14397ddf3530b276d246dba7e4584)): ?>
<?php $component = $__componentOriginal6da14397ddf3530b276d246dba7e4584; ?>
<?php unset($__componentOriginal6da14397ddf3530b276d246dba7e4584); ?>
<?php endif; ?>
    <div class="md:col-span-2"><label class="erp-label"><?php echo e(__('Description')); ?></label><textarea name="description" class="erp-input w-full" rows="3"><?php echo e(old('description', $m?->description)); ?></textarea></div>
    <div><input type="hidden" name="is_active" value="0"><label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" <?php if(old('is_active', $m?->is_active ?? true)): echo 'checked'; endif; ?>><span><?php echo e(__('Active')); ?></span></label></div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\inventory\catalogue\subcategories\partials\form.blade.php ENDPATH**/ ?>