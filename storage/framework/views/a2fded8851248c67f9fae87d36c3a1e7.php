<?php ($fields = $formFields ?? []); ?>
<div class="erp-form-grid max-w-5xl">
    <?php if(($fields['warehouse_id']['visible'] ?? true)): ?>
        <?php if (isset($component)) { $__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-field','data' => ['name' => 'warehouse_id','label' => $fields['warehouse_id']['label'] ?? __('Warehouse'),'required' => ($fields['warehouse_id']['required'] ?? true),'readonly' => ($fields['warehouse_id']['read_only'] ?? false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'warehouse_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['warehouse_id']['label'] ?? __('Warehouse')),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['warehouse_id']['required'] ?? true)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['warehouse_id']['read_only'] ?? false))]); ?>
            <select name="warehouse_id" class="erp-select w-full" <?php if($fields['warehouse_id']['required'] ?? true): echo 'required'; endif; ?> <?php if($fields['warehouse_id']['read_only'] ?? false): echo 'disabled'; endif; ?>>
                <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($w->id); ?>" <?php if((string) old('warehouse_id', $selectedWarehouseId ?? null) === (string) $w->id): echo 'selected'; endif; ?>><?php echo e($w->name); ?></option>
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

    <?php if($type === 'receipt'): ?>
        <?php if(($fields['source']['visible'] ?? true)): ?>
            <?php if (isset($component)) { $__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-field','data' => ['name' => 'source','label' => $fields['source']['label'] ?? __('Source'),'required' => ($fields['source']['required'] ?? true)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'source','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['source']['label'] ?? __('Source')),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['source']['required'] ?? true))]); ?>
                <select name="source" class="erp-select w-full" <?php if($fields['source']['required'] ?? true): echo 'required'; endif; ?>>
                    <?php $__currentLoopData = $sources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->value); ?>"><?php echo e($s->value); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
        <?php if(($fields['receipt_date']['visible'] ?? true)): ?>
            <?php if (isset($component)) { $__componentOriginal187e7f8ae725d0d7c586a97e85953c03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.input','data' => ['name' => 'receipt_date','type' => 'date','label' => $fields['receipt_date']['label'] ?? __('Date'),'value' => old('receipt_date', now()->toDateString()),'required' => ($fields['receipt_date']['required'] ?? true)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'receipt_date','type' => 'date','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['receipt_date']['label'] ?? __('Date')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('receipt_date', now()->toDateString())),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['receipt_date']['required'] ?? true))]); ?>
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
    <?php elseif($type === 'issue'): ?>
        <?php if(($fields['destination']['visible'] ?? true)): ?>
            <?php if (isset($component)) { $__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-field','data' => ['name' => 'destination','label' => $fields['destination']['label'] ?? __('Destination'),'required' => ($fields['destination']['required'] ?? true)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'destination','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['destination']['label'] ?? __('Destination')),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['destination']['required'] ?? true))]); ?>
                <select name="destination" class="erp-select w-full" <?php if($fields['destination']['required'] ?? true): echo 'required'; endif; ?>>
                    <?php $__currentLoopData = $destinations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($d->value); ?>" <?php if(old('destination') === $d->value): echo 'selected'; endif; ?>><?php echo e($d->value); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
        <?php if(($fields['issue_date']['visible'] ?? true)): ?>
            <?php if (isset($component)) { $__componentOriginal187e7f8ae725d0d7c586a97e85953c03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.input','data' => ['name' => 'issue_date','type' => 'date','label' => $fields['issue_date']['label'] ?? __('Date'),'value' => old('issue_date', now()->toDateString()),'required' => ($fields['issue_date']['required'] ?? true)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'issue_date','type' => 'date','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['issue_date']['label'] ?? __('Date')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('issue_date', now()->toDateString())),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['issue_date']['required'] ?? true))]); ?>
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
        <?php if(($fields['to_warehouse_id']['visible'] ?? true)): ?>
            <?php if (isset($component)) { $__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-field','data' => ['name' => 'to_warehouse_id','label' => $fields['to_warehouse_id']['label'] ?? __('To warehouse (transfer)'),'required' => ($fields['to_warehouse_id']['required'] ?? false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'to_warehouse_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['to_warehouse_id']['label'] ?? __('To warehouse (transfer)')),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['to_warehouse_id']['required'] ?? false))]); ?>
                <select name="to_warehouse_id" class="erp-select w-full" <?php if($fields['to_warehouse_id']['required'] ?? false): echo 'required'; endif; ?>>
                    <option value=""><?php echo e(__('N/A')); ?></option>
                    <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($w->id); ?>" <?php if((string) old('to_warehouse_id') === (string) $w->id): echo 'selected'; endif; ?>><?php echo e($w->name); ?></option>
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
    <?php else: ?>
        <?php if(($fields['adjustment_date']['visible'] ?? true)): ?>
            <?php if (isset($component)) { $__componentOriginal187e7f8ae725d0d7c586a97e85953c03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.input','data' => ['name' => 'adjustment_date','type' => 'date','label' => $fields['adjustment_date']['label'] ?? __('Date'),'value' => old('adjustment_date', now()->toDateString()),'required' => ($fields['adjustment_date']['required'] ?? true)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'adjustment_date','type' => 'date','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['adjustment_date']['label'] ?? __('Date')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('adjustment_date', now()->toDateString())),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['adjustment_date']['required'] ?? true))]); ?>
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
        <?php if(($fields['reason']['visible'] ?? true)): ?>
            <?php if (isset($component)) { $__componentOriginal187e7f8ae725d0d7c586a97e85953c03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.input','data' => ['name' => 'reason','label' => $fields['reason']['label'] ?? __('Reason'),'value' => old('reason'),'required' => ($fields['reason']['required'] ?? true),'colSpan' => 2]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'reason','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['reason']['label'] ?? __('Reason')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('reason')),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['reason']['required'] ?? true)),'colSpan' => 2]); ?>
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
    <?php endif; ?>
</div>

<?php if($type === 'issue' && ($fields['notes']['visible'] ?? true)): ?>
    <div class="mt-4 max-w-5xl">
        <?php if (isset($component)) { $__componentOriginal694712473b787cd740db4e46be9da3f9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal694712473b787cd740db4e46be9da3f9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.textarea','data' => ['name' => 'notes','label' => $fields['notes']['label'] ?? __('Notes'),'value' => old('notes'),'required' => ($fields['notes']['required'] ?? false),'readonly' => ($fields['notes']['read_only'] ?? false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'notes','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['notes']['label'] ?? __('Notes')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('notes')),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['notes']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['notes']['read_only'] ?? false))]); ?>
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
    </div>
<?php endif; ?>

<?php echo $__env->make('admin.partials.form-custom-fields', ['fields' => $formFields ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/inventory/partials/document-header.blade.php ENDPATH**/ ?>