<?php
    use App\Enums\CustomerArtworkType;
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

    <div>
        <label class="erp-label" for="artwork_type"><?php echo e(__('Artwork type')); ?></label>
        <select id="artwork_type" name="artwork_type" class="erp-input w-full" <?php if((bool) $customer): echo 'required'; endif; ?> <?php if(! $customer): echo 'disabled'; endif; ?>>
            <?php $__currentLoopData = $artworkTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($type->value); ?>" <?php if(old('artwork_type', CustomerArtworkType::Layout->value) === $type->value): echo 'selected'; endif; ?>>
                    <?php echo e($type->label()); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

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