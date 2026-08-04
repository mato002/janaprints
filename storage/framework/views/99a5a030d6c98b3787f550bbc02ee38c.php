<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Capitalization Workbench'),'breadcrumbs' => [['label' => __('Acquisitions'), 'url' => route('admin.assets.acquisitions.dashboard', ['tab' => 'queue'])], ['label' => $candidate->candidate_number]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Capitalization Workbench'),'description' => __('Review and create fixed assets from procurement receipt.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Capitalization Workbench')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Review and create fixed assets from procurement receipt.'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $attributes = $__attributesOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__attributesOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $component = $__componentOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__componentOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>

    <?php if($requiresApproval && ! $candidate->approved_at): ?>
        <?php if (isset($component)) { $__componentOriginald888329b8246e32afd68d2decbd25cf1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald888329b8246e32afd68d2decbd25cf1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.alert','data' => ['variant' => 'warning','class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'warning','class' => 'mb-4']); ?>
            <?php echo e(__('This capitalization requires authorized approval before assets can be created.')); ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('approve', $candidate)): ?>
                <form method="POST" action="<?php echo e(route('admin.assets.acquisitions.approve', $candidate)); ?>" class="mt-3 inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="erp-btn-primary"><?php echo e(__('Approve Capitalization')); ?></button>
                </form>
            <?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald888329b8246e32afd68d2decbd25cf1)): ?>
<?php $attributes = $__attributesOriginald888329b8246e32afd68d2decbd25cf1; ?>
<?php unset($__attributesOriginald888329b8246e32afd68d2decbd25cf1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald888329b8246e32afd68d2decbd25cf1)): ?>
<?php $component = $__componentOriginald888329b8246e32afd68d2decbd25cf1; ?>
<?php unset($__componentOriginald888329b8246e32afd68d2decbd25cf1); ?>
<?php endif; ?>
    <?php elseif($candidate->approved_at): ?>
        <?php if (isset($component)) { $__componentOriginald888329b8246e32afd68d2decbd25cf1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald888329b8246e32afd68d2decbd25cf1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.alert','data' => ['variant' => 'success','class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'success','class' => 'mb-4']); ?>
            <?php echo e(__('Approved by :user on :date.', ['user' => $candidate->approver?->name ?? __('Authorized user'), 'date' => $candidate->approved_at->format('M j, Y')])); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald888329b8246e32afd68d2decbd25cf1)): ?>
<?php $attributes = $__attributesOriginald888329b8246e32afd68d2decbd25cf1; ?>
<?php unset($__attributesOriginald888329b8246e32afd68d2decbd25cf1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald888329b8246e32afd68d2decbd25cf1)): ?>
<?php $component = $__componentOriginald888329b8246e32afd68d2decbd25cf1; ?>
<?php unset($__componentOriginald888329b8246e32afd68d2decbd25cf1); ?>
<?php endif; ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'lg:col-span-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:col-span-1']); ?>
            <h3 class="mb-3 text-sm font-semibold"><?php echo e(__('Receipt Details')); ?></h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('GRN')); ?></dt><dd><?php echo e($candidate->goodsReceipt?->receipt_number); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('PO')); ?></dt><dd><?php echo e($candidate->purchaseOrder?->po_number); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Vendor')); ?></dt><dd><?php echo e($candidate->vendor?->vendor_name); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Quantity')); ?></dt><dd><?php echo e(number_format($candidate->remainingQuantity(), 0)); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Unit Cost')); ?></dt><dd><?php echo e(number_format($candidate->unit_cost, 2)); ?></dd></div>
            </dl>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'lg:col-span-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:col-span-2']); ?>
            <form method="POST" action="<?php echo e(route('admin.assets.acquisitions.capitalize', $candidate)); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="erp-label"><?php echo e(__('Quantity to Capitalize')); ?></label>
                        <input type="number" name="quantity" min="1" max="<?php echo e((int) $candidate->remainingQuantity()); ?>" value="<?php echo e((int) $candidate->remainingQuantity()); ?>" class="erp-input w-full" required />
                    </div>
                    <div>
                        <label class="erp-label"><?php echo e(__('Asset Category')); ?></label>
                        <select name="asset_category_id" class="erp-select w-full" required>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>" <?php if($candidate->asset_category_id == $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="erp-label"><?php echo e(__('Branch')); ?></label>
                        <select name="branch_id" class="erp-select w-full">
                            <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($branch->id); ?>" <?php if($candidate->branch_id == $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="erp-label"><?php echo e(__('Custodian')); ?></label>
                        <select name="assigned_custodian_user_id" class="erp-select w-full">
                            <option value=""><?php echo e(__('None')); ?></option>
                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="erp-label"><?php echo e(__('Asset Name')); ?></label>
                        <input name="asset_name" value="<?php echo e($candidate->goodsReceiptItem?->purchaseOrderItem?->description); ?>" class="erp-input w-full" required />
                    </div>
                    <div>
                        <label class="erp-label"><?php echo e(__('Manufacturer')); ?></label>
                        <input name="manufacturer" class="erp-input w-full" />
                    </div>
                    <div>
                        <label class="erp-label"><?php echo e(__('Model')); ?></label>
                        <input name="model" class="erp-input w-full" />
                    </div>
                    <div>
                        <label class="erp-label"><?php echo e(__('Useful Life (Years)')); ?></label>
                        <input type="number" name="useful_life_years" min="1" value="<?php echo e($candidate->category?->useful_life_years); ?>" class="erp-input w-full" />
                    </div>
                    <div>
                        <label class="erp-label"><?php echo e(__('Residual Value')); ?></label>
                        <input type="number" step="0.01" name="residual_value" value="0" class="erp-input w-full" />
                    </div>
                    <div>
                        <label class="erp-label"><?php echo e(__('Warranty End')); ?></label>
                        <input type="date" name="warranty_end" class="erp-input w-full" />
                    </div>
                    <div>
                        <label class="erp-label"><?php echo e(__('Warranty Reference')); ?></label>
                        <input name="warranty_reference" class="erp-input w-full" />
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="erp-btn-primary"><?php echo e(__('Capitalize Assets')); ?></button>
                    <a href="<?php echo e(route('admin.assets.acquisitions.dashboard', ['tab' => 'queue'])); ?>" class="erp-btn-secondary"><?php echo e(__('Back')); ?></a>
                </div>
            </form>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\assets\acquisitions\workbench.blade.php ENDPATH**/ ?>