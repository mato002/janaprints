<?php
    $book = $priceBook ?? null;
    $fields = $formFields ?? [];
?>
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <?php if(($fields['name']['visible'] ?? true)): ?>
        <div>
            <label class="erp-label"><?php echo e($fields['name']['label'] ?? __('Name')); ?></label>
            <input type="text" name="name" class="erp-input w-full" value="<?php echo e(old('name', $book?->name)); ?>" <?php if($fields['name']['required'] ?? true): echo 'required'; endif; ?> <?php if($fields['name']['read_only'] ?? false): echo 'readonly'; endif; ?>>
        </div>
    <?php endif; ?>
    <?php if(($fields['code']['visible'] ?? true)): ?>
        <div>
            <label class="erp-label"><?php echo e($fields['code']['label'] ?? __('Code')); ?></label>
            <input type="text" name="code" class="erp-input w-full" value="<?php echo e(old('code', $book?->code)); ?>" placeholder="<?php echo e(filled($book) ? '' : __('Auto-generated')); ?>" <?php if(filled($book) && ($fields['code']['required'] ?? true)): echo 'required'; endif; ?> <?php if($fields['code']['read_only'] ?? false): echo 'readonly'; endif; ?>>
        </div>
    <?php endif; ?>
    <?php if(($fields['description']['visible'] ?? true)): ?>
        <div class="md:col-span-2">
            <label class="erp-label"><?php echo e($fields['description']['label'] ?? __('Description')); ?></label>
            <textarea name="description" class="erp-input w-full" rows="3" <?php if($fields['description']['required'] ?? false): echo 'required'; endif; ?> <?php if($fields['description']['read_only'] ?? false): echo 'readonly'; endif; ?>><?php echo e(old('description', $book?->description)); ?></textarea>
        </div>
    <?php endif; ?>
    <?php if(($fields['currency']['visible'] ?? true)): ?>
        <div>
            <label class="erp-label"><?php echo e($fields['currency']['label'] ?? __('Currency')); ?></label>
            <input type="text" name="currency" class="erp-input w-full" value="<?php echo e(old('currency', $book?->currency ?? ($fields['currency']['default'] ?? 'KES'))); ?>" <?php if($fields['currency']['required'] ?? true): echo 'required'; endif; ?> <?php if($fields['currency']['read_only'] ?? false): echo 'readonly'; endif; ?>>
        </div>
    <?php endif; ?>
    <?php if(($fields['branch_id']['visible'] ?? true)): ?>
        <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'branch_id','label' => $fields['branch_id']['label'] ?? __('Branch'),'options' => $branches,'value' => old('branch_id', $book?->branch_id),'required' => ($fields['branch_id']['required'] ?? false),'readonly' => ($fields['branch_id']['read_only'] ?? false),'createRoute' => 'admin.branches.quick-create','refreshRoute' => 'admin.lookups.branches','permission' => 'branches.manage','modalTitle' => __('Create branch'),'optionLabelKey' => 'name','optionValueKey' => 'id','selectClass' => 'erp-input w-full','emptyLabel' => __('Company-wide')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'branch_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['branch_id']['label'] ?? __('Branch')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($branches),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('branch_id', $book?->branch_id)),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['branch_id']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['branch_id']['read_only'] ?? false)),'create-route' => 'admin.branches.quick-create','refresh-route' => 'admin.lookups.branches','permission' => 'branches.manage','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create branch')),'option-label-key' => 'name','option-value-key' => 'id','select-class' => 'erp-input w-full','empty-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Company-wide'))]); ?>
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
    <?php if(($fields['status']['visible'] ?? true)): ?>
        <?php if (isset($component)) { $__componentOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-status-select','data' => ['formKey' => 'commercial_price_book.create','field' => $fields['status'],'value' => $book?->status ?? ($fields['status']['default'] ?? 'active'),'model' => $book,'selectClass' => 'erp-input w-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-status-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['form-key' => 'commercial_price_book.create','field' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['status']),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($book?->status ?? ($fields['status']['default'] ?? 'active')),'model' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($book),'select-class' => 'erp-input w-full']); ?>
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
    <?php if(($fields['starts_at']['visible'] ?? true)): ?>
        <div>
            <label class="erp-label"><?php echo e($fields['starts_at']['label'] ?? __('Starts at')); ?></label>
            <input type="date" name="starts_at" class="erp-input w-full" value="<?php echo e(old('starts_at', $book?->starts_at?->toDateString())); ?>" <?php if($fields['starts_at']['required'] ?? false): echo 'required'; endif; ?> <?php if($fields['starts_at']['read_only'] ?? false): echo 'readonly'; endif; ?>>
        </div>
    <?php endif; ?>
    <?php if(($fields['ends_at']['visible'] ?? true)): ?>
        <div>
            <label class="erp-label"><?php echo e($fields['ends_at']['label'] ?? __('Ends at')); ?></label>
            <input type="date" name="ends_at" class="erp-input w-full" value="<?php echo e(old('ends_at', $book?->ends_at?->toDateString())); ?>" <?php if($fields['ends_at']['required'] ?? false): echo 'required'; endif; ?> <?php if($fields['ends_at']['read_only'] ?? false): echo 'readonly'; endif; ?>>
        </div>
    <?php endif; ?>
    <?php if(($fields['is_default']['visible'] ?? true)): ?>
        <div class="md:col-span-2">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_default" value="1" <?php if(old('is_default', $book?->is_default)): echo 'checked'; endif; ?> <?php if($fields['is_default']['read_only'] ?? false): echo 'disabled'; endif; ?>>
                <?php echo e($fields['is_default']['label'] ?? __('Set as default price book for this scope')); ?>

            </label>
        </div>
    <?php endif; ?>
</div>
<?php echo $__env->make('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $book ?? null, 'formKey' => 'commercial_price_book.create'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\commercial\price-books\form.blade.php ENDPATH**/ ?>