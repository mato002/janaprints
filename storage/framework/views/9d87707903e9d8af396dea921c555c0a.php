<?php
    $filters = $filters ?? [];
    $filterOptions = $filter_options ?? [];
    $bulkActions = $bulk_actions ?? [];
    $hasBulk = count($bulkActions) > 0;
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Asset register'),'breadcrumbs' => [
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Asset Register')],
    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Asset Register'),'description' => __('All company assets.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Asset Register')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('All company assets.'))]); ?>
        <?php if($can_create ?? false): ?>
             <?php $__env->slot('actions', null, []); ?> 
                <a href="<?php echo e(route('admin.assets.create')); ?>" class="erp-btn-primary" data-erp-modal-open><?php echo e(__('Register Asset')); ?></a>
             <?php $__env->endSlot(); ?>
        <?php endif; ?>
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

    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false,'class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'class' => 'mb-4']); ?>
        <?php if (isset($component)) { $__componentOriginal9577df2686262fb25ceb19a81119823d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9577df2686262fb25ceb19a81119823d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.index-toolbar','data' => ['action' => route('admin.assets.index'),'resetUrl' => route('admin.assets.index'),'compact' => true,'class' => 'erp-index-toolbar-form--compact']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.index-toolbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.assets.index')),'reset-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.assets.index')),'compact' => true,'class' => 'erp-index-toolbar-form--compact']); ?>
            <?php if($can_export ?? false): ?>
                 <?php $__env->slot('export', null, []); ?> 
                    <?php if (isset($component)) { $__componentOriginalf419e868e892b32e6daa894c958d94bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf419e868e892b32e6daa894c958d94bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.export-dropdown','data' => ['exportRoute' => 'admin.assets.export','exportQuery' => request()->query(),'formatInPath' => true,'canExport' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.export-dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['export-route' => 'admin.assets.export','export-query' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->query()),'format-in-path' => true,'can-export' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf419e868e892b32e6daa894c958d94bc)): ?>
<?php $attributes = $__attributesOriginalf419e868e892b32e6daa894c958d94bc; ?>
<?php unset($__attributesOriginalf419e868e892b32e6daa894c958d94bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf419e868e892b32e6daa894c958d94bc)): ?>
<?php $component = $__componentOriginalf419e868e892b32e6daa894c958d94bc; ?>
<?php unset($__componentOriginalf419e868e892b32e6daa894c958d94bc); ?>
<?php endif; ?>
                 <?php $__env->endSlot(); ?>
            <?php endif; ?>

            <input
                type="search"
                name="search"
                value="<?php echo e($filters['search'] ?? ''); ?>"
                class="erp-toolbar-input"
                placeholder="<?php echo e(__('Asset number, name, serial…')); ?>"
                data-erp-auto-search
                aria-label="<?php echo e(__('Search')); ?>"
            >
            <select name="category_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Category')); ?>">
                <option value=""><?php echo e(__('All categories')); ?></option>
                <?php $__currentLoopData = $filterOptions['categories'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($category->id); ?>" <?php if((string) ($filters['category_id'] ?? '') === (string) $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="branch_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Branch')); ?>">
                <option value=""><?php echo e(__('All branches')); ?></option>
                <?php $__currentLoopData = $filterOptions['branches'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($branch->id); ?>" <?php if((string) ($filters['branch_id'] ?? '') === (string) $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="assigned_to_user_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Assigned To')); ?>">
                <option value=""><?php echo e(__('All assignees')); ?></option>
                <?php $__currentLoopData = $filterOptions['users'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($user->id); ?>" <?php if((string) ($filters['assigned_to_user_id'] ?? '') === (string) $user->id): echo 'selected'; endif; ?>><?php echo e($user->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="sort" class="erp-toolbar-select" aria-label="<?php echo e(__('Sort')); ?>">
                <option value="newest" <?php if(($filters['sort'] ?? 'newest') === 'newest'): echo 'selected'; endif; ?>><?php echo e(__('Newest')); ?></option>
                <option value="oldest" <?php if(($filters['sort'] ?? '') === 'oldest'): echo 'selected'; endif; ?>><?php echo e(__('Oldest')); ?></option>
                <option value="cost_high" <?php if(($filters['sort'] ?? '') === 'cost_high'): echo 'selected'; endif; ?>><?php echo e(__('Cost High-Low')); ?></option>
                <option value="cost_low" <?php if(($filters['sort'] ?? '') === 'cost_low'): echo 'selected'; endif; ?>><?php echo e(__('Cost Low-High')); ?></option>
            </select>
            <select name="status" class="erp-toolbar-select" aria-label="<?php echo e(__('Status')); ?>">
                <option value=""><?php echo e(__('All statuses')); ?></option>
                <?php $__currentLoopData = $filterOptions['statuses'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status->value); ?>" <?php if(($filters['status'] ?? '') === $status->value): echo 'selected'; endif; ?>><?php echo e($status->label()); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9577df2686262fb25ceb19a81119823d)): ?>
<?php $attributes = $__attributesOriginal9577df2686262fb25ceb19a81119823d; ?>
<?php unset($__attributesOriginal9577df2686262fb25ceb19a81119823d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9577df2686262fb25ceb19a81119823d)): ?>
<?php $component = $__componentOriginal9577df2686262fb25ceb19a81119823d; ?>
<?php unset($__componentOriginal9577df2686262fb25ceb19a81119823d); ?>
<?php endif; ?>
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

    <?php if($hasBulk): ?>
        <form method="POST" action="<?php echo e(route('admin.assets.bulk')); ?>" id="asset-bulk-form">
            <?php echo csrf_field(); ?>
    <?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <?php if($hasBulk): ?>
            <?php if (isset($component)) { $__componentOriginalba67f25bb2ee5e3afb1324326bec6c1b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba67f25bb2ee5e3afb1324326bec6c1b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.bulk-action-bar','data' => ['selectAllId' => 'select-all-assets','checkboxClass' => 'asset-row-checkbox','class' => 'mb-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.bulk-action-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['select-all-id' => 'select-all-assets','checkbox-class' => 'asset-row-checkbox','class' => 'mb-3']); ?>
                <select name="action" class="erp-toolbar-select text-xs" required aria-label="<?php echo e(__('Bulk action')); ?>">
                    <option value=""><?php echo e(__('Bulk action…')); ?></option>
                    <?php $__currentLoopData = $bulkActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($action['key']); ?>"><?php echo e($action['label']); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select name="assigned_to_user_id" class="erp-toolbar-select text-xs" aria-label="<?php echo e(__('Assign to user')); ?>">
                    <option value=""><?php echo e(__('Assign to user…')); ?></option>
                    <?php $__currentLoopData = $filterOptions['users'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select name="status" class="erp-toolbar-select text-xs" aria-label="<?php echo e(__('Change status')); ?>">
                    <option value=""><?php echo e(__('Change status…')); ?></option>
                    <?php $__currentLoopData = $filterOptions['statuses'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($status->value); ?>"><?php echo e($status->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button type="submit" class="erp-btn-secondary py-1 text-xs"><?php echo e(__('Apply to selected')); ?></button>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba67f25bb2ee5e3afb1324326bec6c1b)): ?>
<?php $attributes = $__attributesOriginalba67f25bb2ee5e3afb1324326bec6c1b; ?>
<?php unset($__attributesOriginalba67f25bb2ee5e3afb1324326bec6c1b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba67f25bb2ee5e3afb1324326bec6c1b)): ?>
<?php $component = $__componentOriginalba67f25bb2ee5e3afb1324326bec6c1b; ?>
<?php unset($__componentOriginalba67f25bb2ee5e3afb1324326bec6c1b); ?>
<?php endif; ?>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <?php if($hasBulk): ?>
                            <th class="w-8"><input type="checkbox" id="select-all-assets" aria-label="<?php echo e(__('Select all')); ?>"></th>
                        <?php endif; ?>
                        <th><?php echo e(__('Asset Number')); ?></th>
                        <th><?php echo e(__('Asset Name')); ?></th>
                        <th><?php echo e(__('Category')); ?></th>
                        <th><?php echo e(__('Branch')); ?></th>
                        <th><?php echo e(__('Assigned To')); ?></th>
                        <th class="text-right"><?php echo e(__('Acquisition Cost')); ?></th>
                        <th class="text-right"><?php echo e(__('Book Value')); ?></th>
                        <th><?php echo e(__('Status')); ?></th>
                        <th><?php echo e(__('Created')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <?php if($hasBulk): ?>
                                <td><input type="checkbox" name="asset_ids[]" value="<?php echo e($asset->id); ?>" class="asset-row-checkbox" form="asset-bulk-form"></td>
                            <?php endif; ?>
                            <td><a href="<?php echo e(route('admin.assets.show', $asset)); ?>" class="erp-link font-medium"><?php echo e($asset->asset_number); ?></a></td>
                            <td><?php echo e($asset->asset_name); ?></td>
                            <td><?php echo e($asset->category?->name); ?></td>
                            <td><?php echo e($asset->branch?->name ?? '—'); ?></td>
                            <td><?php echo e($asset->assignedUser?->name ?? '—'); ?></td>
                            <td class="text-right"><?php echo e(number_format($asset->acquisition_cost, 2)); ?></td>
                            <td class="text-right"><?php echo e(number_format($asset->netBookValue(), 2)); ?></td>
                            <td><?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => $asset->status->badgeVariant()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($asset->status->badgeVariant())]); ?><?php echo e($asset->status->label()); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?></td>
                            <td><?php echo e($asset->created_at?->format('Y-m-d')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="<?php echo e($hasBulk ? 10 : 9); ?>" class="py-8 text-center text-slate-500"><?php echo e(__('No assets found.')); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($assets->hasPages()): ?>
            <div class="mt-4 border-t border-erp-border pt-3"><?php echo e($assets->links()); ?></div>
        <?php endif; ?>
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

    <?php if($hasBulk): ?>
        </form>
        <script>
            document.getElementById('select-all-assets')?.addEventListener('change', (event) => {
                document.querySelectorAll('.asset-row-checkbox').forEach((checkbox) => {
                    checkbox.checked = event.target.checked;
                    checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        </script>
    <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/assets/index.blade.php ENDPATH**/ ?>