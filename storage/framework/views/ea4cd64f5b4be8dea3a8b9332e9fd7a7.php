<?php
    use App\Support\Crm\CustomerArtworkTypeCatalog;

    $defaultArtworkType = app(CustomerArtworkTypeCatalog::class)->defaultCode();
?>

<?php if (isset($component)) { $__componentOriginala826d696a1cd5f6e2881b0defe272cb0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala826d696a1cd5f6e2881b0defe272cb0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-nested-form','data' => ['title' => $title,'action' => $action,'enctype' => 'multipart/form-data','maxWidth' => '2xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-nested-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action),'enctype' => 'multipart/form-data','max-width' => '2xl']); ?>
    <?php if($customer): ?>
        <input type="hidden" name="customer_id" value="<?php echo e($customer->id); ?>">
        <p class="text-sm text-slate-600">
            <span class="font-medium text-slate-800"><?php echo e(__('Customer')); ?>:</span>
            <?php echo e($customer->company_name); ?>

        </p>
    <?php else: ?>
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <?php echo e(__('Select a customer in the parent form before uploading artwork.')); ?>

        </div>
    <?php endif; ?>

    <div>
        <label class="erp-label" for="artwork_name"><?php echo e(__('Artwork name')); ?></label>
        <input
            type="text"
            id="artwork_name"
            name="artwork_name"
            class="erp-input w-full"
            value="<?php echo e(old('artwork_name')); ?>"
            maxlength="255"
            <?php if((bool) $customer): echo 'required'; endif; ?>
            <?php if(! $customer): echo 'disabled'; endif; ?>
        >
    </div>

    <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'artwork_type','label' => __('Artwork type'),'options' => $artworkTypes,'value' => old('artwork_type', $defaultArtworkType),'createRoute' => 'admin.crm.artwork-types.quick-create','refreshRoute' => 'admin.lookups.artwork_types','permission' => 'crm.customers.edit','modalTitle' => __('Create artwork type'),'selectClass' => 'erp-input w-full','emptyOption' => false,'disabled' => ! $customer,'required' => (bool) $customer]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'artwork_type','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Artwork type')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($artworkTypes),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('artwork_type', $defaultArtworkType)),'create-route' => 'admin.crm.artwork-types.quick-create','refresh-route' => 'admin.lookups.artwork_types','permission' => 'crm.customers.edit','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create artwork type')),'select-class' => 'erp-input w-full','empty-option' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(! $customer),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((bool) $customer)]); ?>
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
        <label class="erp-label" for="file"><?php echo e(__('Artwork file')); ?></label>
        <input
            type="file"
            id="file"
            name="file"
            class="erp-input w-full"
            accept=".jpg,.jpeg,.png,.webp,.pdf"
            <?php if((bool) $customer): echo 'required'; endif; ?>
            <?php if(! $customer): echo 'disabled'; endif; ?>
        >
        <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Accepted formats: JPG, PNG, WebP, PDF. Max 20 MB.')); ?></p>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\lookups\quick-create\customer-artwork.blade.php ENDPATH**/ ?>