<?php
    use App\Enums\CustomerPrintSpecificationStatus;
    use App\Support\Crm\CustomerArtworkTypeCatalog;

    $defaultArtworkType = app(CustomerArtworkTypeCatalog::class)->defaultCode();
?>

<?php if (isset($component)) { $__componentOriginala826d696a1cd5f6e2881b0defe272cb0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala826d696a1cd5f6e2881b0defe272cb0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-nested-form','data' => ['title' => $title,'action' => $action,'enctype' => 'multipart/form-data','maxWidth' => '3xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-nested-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action),'enctype' => 'multipart/form-data','max-width' => '3xl']); ?>
    <?php if($customer): ?>
        <input type="hidden" name="customer_id" value="<?php echo e($customer->id); ?>">
        <p class="text-sm text-slate-600">
            <span class="font-medium text-slate-800"><?php echo e(__('Customer')); ?>:</span>
            <?php echo e($customer->company_name); ?>

        </p>
    <?php else: ?>
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <?php echo e(__('Select a customer in the parent form before creating a print specification.')); ?>

        </div>
    <?php endif; ?>

    <div>
        <label class="erp-label" for="name"><?php echo e(__('Name')); ?></label>
        <input
            type="text"
            id="name"
            name="name"
            class="erp-input w-full"
            value="<?php echo e(old('name')); ?>"
            maxlength="255"
            placeholder="<?php echo e(__('e.g. Fortress Receipt Book')); ?>"
            <?php if((bool) $customer): echo 'required'; endif; ?>
            <?php if(! $customer): echo 'disabled'; endif; ?>
        >
    </div>

    <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'inventory_item_id','label' => __('Product / inventory item'),'options' => [],'value' => old('inventory_item_id'),'required' => (bool) $customer,'disabled' => ! $customer,'createRoute' => 'admin.inventory.items.quick-create','refreshRoute' => 'admin.lookups.items','permission' => 'catalogue.create','modalTitle' => __('Create product'),'selectClass' => 'erp-input w-full','emptyOption' => false,'placeholder' => __('Select product')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'inventory_item_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Product / inventory item')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([]),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('inventory_item_id')),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((bool) $customer),'disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(! $customer),'create-route' => 'admin.inventory.items.quick-create','refresh-route' => 'admin.lookups.items','permission' => 'catalogue.create','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create product')),'select-class' => 'erp-input w-full','empty-option' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Select product'))]); ?>
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

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="erp-label" for="default_quantity"><?php echo e(__('Default quantity')); ?></label>
            <input
                type="number"
                step="0.001"
                min="0"
                id="default_quantity"
                name="default_quantity"
                class="erp-input w-full"
                value="<?php echo e(old('default_quantity', '1')); ?>"
                <?php if(! $customer): echo 'disabled'; endif; ?>
            >
        </div>
        <div>
            <label class="erp-label" for="default_unit_price"><?php echo e(__('Default unit price')); ?></label>
            <input
                type="number"
                step="0.01"
                min="0"
                id="default_unit_price"
                name="default_unit_price"
                class="erp-input w-full"
                value="<?php echo e(old('default_unit_price')); ?>"
                <?php if(! $customer): echo 'disabled'; endif; ?>
            >
        </div>
    </div>

    <div>
        <label class="erp-label" for="status"><?php echo e(__('Status')); ?></label>
        <select id="status" name="status" class="erp-input w-full" <?php if((bool) $customer): echo 'required'; endif; ?> <?php if(! $customer): echo 'disabled'; endif; ?>>
            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($status->value); ?>" <?php if(old('status', $defaultStatus ?? CustomerPrintSpecificationStatus::Active->value) === $status->value): echo 'selected'; endif; ?>>
                    <?php echo e($status->label()); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Use Active so the specification is available for orders immediately.')); ?></p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'artwork_type','label' => __('Artwork type'),'options' => $artworkTypes,'value' => old('artwork_type', $defaultArtworkType),'createRoute' => 'admin.crm.artwork-types.quick-create','refreshRoute' => 'admin.lookups.artwork_types','permission' => 'crm.customers.update','modalTitle' => __('Create artwork type'),'selectClass' => 'erp-input w-full','emptyOption' => false,'disabled' => ! $customer]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'artwork_type','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Artwork type')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($artworkTypes),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('artwork_type', $defaultArtworkType)),'create-route' => 'admin.crm.artwork-types.quick-create','refresh-route' => 'admin.lookups.artwork_types','permission' => 'crm.customers.update','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create artwork type')),'select-class' => 'erp-input w-full','empty-option' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(! $customer)]); ?>
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
        <div>
            <label class="erp-label" for="artwork_file"><?php echo e(__('Initial artwork file')); ?></label>
            <input
                type="file"
                id="artwork_file"
                name="artwork_file"
                class="erp-input w-full"
                accept=".jpg,.jpeg,.png,.webp,.pdf"
                <?php if(! $customer): echo 'disabled'; endif; ?>
            >
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala826d696a1cd5f6e2881b0defe272cb0)): ?>
<?php $attributes = $__attributesOriginala826d696a1cd5f6e2881b0defe272cb0; ?>
<?php unset($__attributesOriginala826d696a1cd5f6e2881b0defe272cb0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala826d696a1cd5f6e2881b0defe272cb0)): ?>
<?php $component = $__componentOriginala826d696a1cd5f6e2881b0defe272cb0; ?>
<?php unset($__componentOriginala826d696a1cd5f6e2881b0defe272cb0); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\lookups\quick-create\print-specification.blade.php ENDPATH**/ ?>