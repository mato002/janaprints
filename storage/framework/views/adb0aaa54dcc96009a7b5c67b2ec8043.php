<?php ($fields = $formFields ?? []); ?>
<?php if (isset($component)) { $__componentOriginald3ad0f200dc20b794011e332a16c068d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3ad0f200dc20b794011e332a16c068d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal-form','data' => ['title' => __('New stock count'),'breadcrumbs' => [['label' => __('Stock Count'), 'url' => route('admin.inventory.stock-counts.index')], ['label' => __('Create')]],'maxWidth' => '3xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('New stock count')),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => __('Stock Count'), 'url' => route('admin.inventory.stock-counts.index')], ['label' => __('Create')]]),'maxWidth' => '3xl']); ?>
    <?php if (isset($component)) { $__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-shell','data' => ['action' => route('admin.inventory.stock-counts.store')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.inventory.stock-counts.store'))]); ?>
        <?php if($fromStoreDesk ?? request('from') === 'store-desk'): ?>
            <input type="hidden" name="from" value="store-desk">
        <?php endif; ?>
        <div class="erp-form-grid">
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
                        <option value=""><?php echo e(__('Select warehouse')); ?></option>
                        <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warehouse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($warehouse->id); ?>" <?php if(old('warehouse_id') == $warehouse->id): echo 'selected'; endif; ?>><?php echo e($warehouse->name); ?></option>
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

            <?php if(($fields['count_type']['visible'] ?? true)): ?>
                <?php if (isset($component)) { $__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-field','data' => ['name' => 'count_type','label' => $fields['count_type']['label'] ?? __('Count type'),'required' => ($fields['count_type']['required'] ?? true),'readonly' => ($fields['count_type']['read_only'] ?? false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'count_type','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['count_type']['label'] ?? __('Count type')),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['count_type']['required'] ?? true)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['count_type']['read_only'] ?? false))]); ?>
                    <select name="count_type" class="erp-select w-full" <?php if($fields['count_type']['required'] ?? true): echo 'required'; endif; ?> <?php if($fields['count_type']['read_only'] ?? false): echo 'disabled'; endif; ?>>
                        <?php $__currentLoopData = $countTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($type->value); ?>" <?php if(old('count_type') === $type->value): echo 'selected'; endif; ?>><?php echo e(ucfirst($type->value)); ?></option>
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

            <?php if(($fields['count_date']['visible'] ?? true)): ?>
                <?php if (isset($component)) { $__componentOriginal187e7f8ae725d0d7c586a97e85953c03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.input','data' => ['name' => 'count_date','type' => 'date','label' => $fields['count_date']['label'] ?? __('Count date'),'value' => old('count_date', now()->toDateString()),'required' => ($fields['count_date']['required'] ?? true),'readonly' => ($fields['count_date']['read_only'] ?? false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'count_date','type' => 'date','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['count_date']['label'] ?? __('Count date')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('count_date', now()->toDateString())),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['count_date']['required'] ?? true)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['count_date']['read_only'] ?? false))]); ?>
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

            <?php if(($fields['notes']['visible'] ?? true)): ?>
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
            <?php endif; ?>
        </div>

        <div id="partial-items" class="hidden mt-4">
            <?php if (isset($component)) { $__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-field','data' => ['name' => 'item_ids','label' => __('Items (partial count)'),'colSpan' => 2]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'item_ids','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Items (partial count)')),'colSpan' => 2]); ?>
                <select name="item_ids[]" class="erp-select w-full" multiple size="8">
                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($item->id); ?>"><?php echo e($item->item_name); ?> (<?php echo e($item->sku); ?>)</option>
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
        </div>

        <?php echo $__env->make('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if (isset($component)) { $__componentOriginal661c5ca87570cde504c602ae668d3776 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal661c5ca87570cde504c602ae668d3776 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-actions','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-actions'); ?>
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
<?php $component->withAttributes([]); ?><?php echo e(__('Create count')); ?> <?php echo $__env->renderComponent(); ?>
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
<?php if (isset($__attributesOriginal661c5ca87570cde504c602ae668d3776)): ?>
<?php $attributes = $__attributesOriginal661c5ca87570cde504c602ae668d3776; ?>
<?php unset($__attributesOriginal661c5ca87570cde504c602ae668d3776); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal661c5ca87570cde504c602ae668d3776)): ?>
<?php $component = $__componentOriginal661c5ca87570cde504c602ae668d3776; ?>
<?php unset($__componentOriginal661c5ca87570cde504c602ae668d3776); ?>
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

    <script>
        document.querySelector('[name=count_type]')?.addEventListener('change', function () {
            document.getElementById('partial-items')?.classList.toggle('hidden', this.value !== 'partial');
        });
    </script>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\inventory\control\stock-counts\create.blade.php ENDPATH**/ ?>