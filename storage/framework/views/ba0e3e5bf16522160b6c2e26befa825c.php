<?php
    $asset = $asset ?? null;
?>

<div class="max-w-2xl space-y-4">
    <div>
        <label class="text-xs text-slate-600" for="asset_category_id"><?php echo e(__('Category')); ?></label>
        <select id="asset_category_id" name="asset_category_id" class="erp-select mt-1 w-full" required>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($category->id); ?>" <?php if(old('asset_category_id', $asset?->asset_category_id) == $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label class="text-xs text-slate-600" for="asset_name"><?php echo e(__('Asset Name')); ?></label>
        <input id="asset_name" name="asset_name" class="erp-input mt-1 w-full" value="<?php echo e(old('asset_name', $asset?->asset_name)); ?>" required>
    </div>
    <?php if($asset): ?>
        <div>
            <label class="text-xs text-slate-600"><?php echo e(__('Asset Number')); ?></label>
            <input class="erp-input mt-1 w-full bg-slate-50" value="<?php echo e($asset->asset_number); ?>" readonly>
        </div>
    <?php endif; ?>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="text-xs text-slate-600" for="manufacturer"><?php echo e(__('Manufacturer')); ?></label>
            <input id="manufacturer" name="manufacturer" class="erp-input mt-1 w-full" value="<?php echo e(old('manufacturer', $asset?->manufacturer)); ?>">
        </div>
        <div>
            <label class="text-xs text-slate-600" for="model"><?php echo e(__('Model')); ?></label>
            <input id="model" name="model" class="erp-input mt-1 w-full" value="<?php echo e(old('model', $asset?->model)); ?>">
        </div>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="text-xs text-slate-600" for="serial_number"><?php echo e(__('Serial Number')); ?></label>
            <input id="serial_number" name="serial_number" class="erp-input mt-1 w-full" value="<?php echo e(old('serial_number', $asset?->serial_number)); ?>">
        </div>
        <div>
            <label class="text-xs text-slate-600" for="barcode"><?php echo e(__('Barcode')); ?></label>
            <input id="barcode" name="barcode" class="erp-input mt-1 w-full" value="<?php echo e(old('barcode', $asset?->barcode)); ?>">
        </div>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="text-xs text-slate-600" for="acquisition_date"><?php echo e(__('Acquisition Date')); ?></label>
            <input id="acquisition_date" type="date" name="acquisition_date" class="erp-input mt-1 w-full" value="<?php echo e(old('acquisition_date', $asset?->acquisition_date?->format('Y-m-d'))); ?>" required>
        </div>
        <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'branch_id','label' => __('Branch'),'options' => $branches,'value' => old('branch_id', $asset?->branch_id),'createRoute' => 'admin.branches.quick-create','refreshRoute' => 'admin.lookups.branches','permission' => 'branches.manage','modalTitle' => __('Create branch'),'optionLabelKey' => 'name','optionValueKey' => 'id','selectClass' => 'erp-select mt-1 w-full','placeholder' => __('Select branch…')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'branch_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Branch')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($branches),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('branch_id', $asset?->branch_id)),'create-route' => 'admin.branches.quick-create','refresh-route' => 'admin.lookups.branches','permission' => 'branches.manage','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create branch')),'option-label-key' => 'name','option-value-key' => 'id','select-class' => 'erp-select mt-1 w-full','placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Select branch…'))]); ?>
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
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="text-xs text-slate-600" for="acquisition_cost"><?php echo e(__('Acquisition Cost')); ?></label>
            <input id="acquisition_cost" type="number" step="0.01" min="0" name="acquisition_cost" class="erp-input mt-1 w-full" value="<?php echo e(old('acquisition_cost', $asset?->acquisition_cost)); ?>" required>
        </div>
        <div>
            <label class="text-xs text-slate-600" for="residual_value"><?php echo e(__('Residual Value')); ?></label>
            <input id="residual_value" type="number" step="0.01" min="0" name="residual_value" class="erp-input mt-1 w-full" value="<?php echo e(old('residual_value', $asset?->residual_value ?? 0)); ?>">
        </div>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="text-xs text-slate-600" for="status"><?php echo e(__('Status')); ?></label>
            <select id="status" name="status" class="erp-select mt-1 w-full">
                <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status->value); ?>" <?php if(old('status', $asset?->status?->value ?? 'active') === $status->value): echo 'selected'; endif; ?>><?php echo e($status->label()); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label class="text-xs text-slate-600" for="assigned_to_user_id"><?php echo e(__('Assigned User')); ?></label>
            <select id="assigned_to_user_id" name="assigned_to_user_id" class="erp-select mt-1 w-full">
                <option value=""><?php echo e(__('None')); ?></option>
                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($user->id); ?>" <?php if(old('assigned_to_user_id', $asset?->assigned_to_user_id) == $user->id): echo 'selected'; endif; ?>><?php echo e($user->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    </div>
    <div>
        <label class="text-xs text-slate-600" for="notes"><?php echo e(__('Notes')); ?></label>
        <textarea id="notes" name="notes" rows="3" class="erp-input mt-1 w-full"><?php echo e(old('notes', $asset?->notes)); ?></textarea>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\assets\partials\form-fields.blade.php ENDPATH**/ ?>