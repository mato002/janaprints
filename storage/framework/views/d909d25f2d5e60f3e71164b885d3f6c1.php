<?php ($fields = $formFields ?? []); ?>

<div class="erp-form-grid">
    <?php if(($fields['name']['visible'] ?? true)): ?>
        <?php if (isset($component)) { $__componentOriginal187e7f8ae725d0d7c586a97e85953c03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.input','data' => ['name' => 'name','label' => $fields['name']['label'] ?? __('Warehouse name'),'value' => old('name', $warehouse?->name),'required' => ($fields['name']['required'] ?? true),'readonly' => ($fields['name']['read_only'] ?? false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'name','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['name']['label'] ?? __('Warehouse name')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('name', $warehouse?->name)),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['name']['required'] ?? true)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['name']['read_only'] ?? false))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $attributes = $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $component = $__componentOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(($fields['code']['visible'] ?? true)): ?>
        <?php if (isset($component)) { $__componentOriginal6da14397ddf3530b276d246dba7e4584 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6da14397ddf3530b276d246dba7e4584 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.entity-code-input','data' => ['record' => $warehouse,'erp' => true,'maxlength' => '50']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.entity-code-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($warehouse),'erp' => true,'maxlength' => '50']); ?>
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
    <?php endif; ?>

    <?php if(($fields['branch_id']['visible'] ?? false)): ?>
        <?php if (isset($component)) { $__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-field','data' => ['name' => 'branch_id','label' => $fields['branch_id']['label'] ?? __('Branch'),'required' => ($fields['branch_id']['required'] ?? false),'readonly' => ($fields['branch_id']['read_only'] ?? false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'branch_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['branch_id']['label'] ?? __('Branch')),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['branch_id']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['branch_id']['read_only'] ?? false))]); ?>
            <select id="branch_id" name="branch_id" class="erp-select w-full" <?php if($fields['branch_id']['required'] ?? false): echo 'required'; endif; ?> <?php if($fields['branch_id']['read_only'] ?? false): echo 'disabled'; endif; ?>>
                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($branch->id); ?>" <?php if((string) old('branch_id', $warehouse?->branch_id ?? $selectedBranchId) === (string) $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1)): ?>
<?php $attributes = $__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1; ?>
<?php unset($__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1)): ?>
<?php $component = $__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1; ?>
<?php unset($__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(($fields['location']['visible'] ?? true)): ?>
        <?php if (isset($component)) { $__componentOriginal187e7f8ae725d0d7c586a97e85953c03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.input','data' => ['name' => 'location','label' => $fields['location']['label'] ?? __('Location'),'value' => old('location', $warehouse?->location),'required' => ($fields['location']['required'] ?? false),'readonly' => ($fields['location']['read_only'] ?? false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'location','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['location']['label'] ?? __('Location')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('location', $warehouse?->location)),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['location']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['location']['read_only'] ?? false))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $attributes = $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $component = $__componentOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(($fields['description']['visible'] ?? true) || ($fields['notes']['visible'] ?? true)): ?>
        <?php if (isset($component)) { $__componentOriginal694712473b787cd740db4e46be9da3f9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal694712473b787cd740db4e46be9da3f9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.textarea','data' => ['name' => 'notes','label' => $fields['notes']['label'] ?? $fields['description']['label'] ?? __('Notes'),'value' => old('notes', $warehouse?->description),'required' => ($fields['notes']['required'] ?? $fields['description']['required'] ?? false),'readonly' => ($fields['notes']['read_only'] ?? $fields['description']['read_only'] ?? false),'colSpan' => 2]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'notes','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['notes']['label'] ?? $fields['description']['label'] ?? __('Notes')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('notes', $warehouse?->description)),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['notes']['required'] ?? $fields['description']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['notes']['read_only'] ?? $fields['description']['read_only'] ?? false)),'colSpan' => 2]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal694712473b787cd740db4e46be9da3f9)): ?>
<?php $attributes = $__attributesOriginal694712473b787cd740db4e46be9da3f9; ?>
<?php unset($__attributesOriginal694712473b787cd740db4e46be9da3f9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal694712473b787cd740db4e46be9da3f9)): ?>
<?php $component = $__componentOriginal694712473b787cd740db4e46be9da3f9; ?>
<?php unset($__componentOriginal694712473b787cd740db4e46be9da3f9); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(($fields['is_active']['visible'] ?? true)): ?>
        <?php if (isset($component)) { $__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-field','data' => ['name' => 'is_active','label' => $fields['is_active']['label'] ?? __('Active store'),'readonly' => ($fields['is_active']['read_only'] ?? false),'colSpan' => 2]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'is_active','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['is_active']['label'] ?? __('Active store')),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['is_active']['read_only'] ?? false)),'colSpan' => 2]); ?>
            <input type="hidden" name="is_active" value="0">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" <?php if(old('is_active', $warehouse?->is_active ?? true)): echo 'checked'; endif; ?> <?php if($fields['is_active']['read_only'] ?? false): echo 'disabled'; endif; ?>>
                <span><?php echo e(__('Active store')); ?></span>
            </label>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1)): ?>
<?php $attributes = $__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1; ?>
<?php unset($__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1)): ?>
<?php $component = $__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1; ?>
<?php unset($__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1); ?>
<?php endif; ?>
    <?php endif; ?>
</div>
<?php echo $__env->make('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $warehouse ?? null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\inventory\warehouses\form.blade.php ENDPATH**/ ?>