<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $asset->asset_number,'breadcrumbs' => [
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Asset Management'), 'url' => route('admin.assets.index')],
        ['label' => $asset->asset_number],
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $asset->asset_name,'description' => $asset->asset_number]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($asset->asset_name),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($asset->asset_number)]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
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
<?php endif; ?>
            <a href="<?php echo e(route('admin.assets.barcode', $asset)); ?>" class="erp-btn-secondary" target="_blank"><?php echo e(__('Print Barcode')); ?></a>
            <?php if($asset->machineProfile): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $asset->machineProfile)): ?>
                    <a href="<?php echo e(route('admin.assets.machines.show', $asset)); ?>" class="erp-btn-secondary"><?php echo e(__('Machine Profile')); ?></a>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view360', $asset)): ?>
                <a href="<?php echo e(route('admin.assets.360.show', $asset)); ?>" class="erp-btn-primary"><?php echo e(__('View 360')); ?></a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $asset)): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', \App\Models\Assets\DepreciationRun::class)): ?>
                    <a href="<?php echo e(route('admin.assets.finance.profile', $asset)); ?>" class="erp-btn-secondary"><?php echo e(__('Financial Profile')); ?></a>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $asset)): ?>
                <a href="<?php echo e(route('admin.assets.edit', $asset)); ?>" class="erp-btn-secondary"><?php echo e(__('Edit Asset')); ?></a>
            <?php endif; ?>
         <?php $__env->endSlot(); ?>
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

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="space-y-4 xl:col-span-2">
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
                <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Overview')); ?></h3>
                <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500"><?php echo e(__('Category')); ?></dt><dd><?php echo e($asset->category?->name); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Branch')); ?></dt><dd><?php echo e($asset->branch?->name ?? '—'); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Serial Number')); ?></dt><dd><?php echo e($asset->serial_number ?? '—'); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Barcode')); ?></dt><dd><?php echo e($asset->barcode ?? $asset->asset_number); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Manufacturer')); ?></dt><dd><?php echo e($asset->manufacturer ?? '—'); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Model')); ?></dt><dd><?php echo e($asset->model ?? '—'); ?></dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-500"><?php echo e(__('Notes')); ?></dt><dd><?php echo e($asset->notes ?: '—'); ?></dd></div>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Financial Information')); ?></h3>
                <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500"><?php echo e(__('Acquisition Date')); ?></dt><dd><?php echo e($asset->acquisition_date?->format('Y-m-d')); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Acquisition Cost')); ?></dt><dd><?php echo e(number_format($asset->acquisition_cost, 2)); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Residual Value')); ?></dt><dd><?php echo e(number_format($asset->residual_value, 2)); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Accumulated Depreciation')); ?></dt><dd><?php echo e(number_format($asset->accumulated_depreciation, 2)); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Book Value')); ?></dt><dd class="font-semibold"><?php echo e(number_format($asset->netBookValue(), 2)); ?></dd></div>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Custody & Assignment')); ?></h3>
                <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500"><?php echo e(__('Custody Status')); ?></dt><dd><?php echo e($asset->custody_status?->label() ?? '—'); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Current Condition')); ?></dt><dd><?php echo e($asset->current_condition?->label() ?? '—'); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Assigned Employee')); ?></dt><dd><?php echo e($asset->assignedEmployee?->full_name ?? '—'); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Assigned Department')); ?></dt><dd><?php echo e($asset->assignedDepartment?->name ?? '—'); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Assigned User')); ?></dt><dd><?php echo e($asset->assignedUser?->name ?? '—'); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Assigned Branch')); ?></dt><dd><?php echo e($asset->assignedBranch?->name ?? '—'); ?></dd></div>
                </dl>
                <?php if($asset->assignmentHistories->isNotEmpty()): ?>
                    <div class="mt-4 overflow-x-auto">
                        <table class="erp-table w-full text-sm">
                            <thead><tr><th><?php echo e(__('Type')); ?></th><th><?php echo e(__('Assigned To')); ?></th><th><?php echo e(__('Status')); ?></th><th><?php echo e(__('By')); ?></th><th><?php echo e(__('Date')); ?></th></tr></thead>
                            <tbody>
                                <?php $__currentLoopData = $asset->assignmentHistories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e(ucfirst($history->assignment_type->value)); ?></td>
                                        <td><?php echo e($history->assignedEmployee?->full_name ?? $history->assignedDepartment?->name ?? $history->assignedUser?->name ?? $history->assignedBranch?->name ?? '—'); ?></td>
                                        <td><?php echo e($history->status?->label() ?? '—'); ?></td>
                                        <td><?php echo e($history->assigner?->name); ?></td>
                                        <td><?php echo e($history->assigned_at?->format('Y-m-d H:i')); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
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

            <?php if($asset->custodyTimelineEntries->isNotEmpty()): ?>
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
                    <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Custody Timeline')); ?></h3>
                    <ul class="space-y-2 text-sm">
                        <?php $__currentLoopData = $asset->custodyTimelineEntries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex justify-between gap-3 border-b border-erp-border pb-2">
                                <span><span class="font-medium"><?php echo e($entry->title); ?></span> — <?php echo e($entry->user?->name ?? __('System')); ?></span>
                                <span class="text-slate-500"><?php echo e($entry->occurred_at?->format('Y-m-d H:i')); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
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
            <?php endif; ?>

            <?php if(! $asset->machineProfile): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', \App\Models\Assets\MachineProfile::class)): ?>
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
                        <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Production Machine')); ?></h3>
                        <p class="mb-3 text-sm text-slate-500"><?php echo e(__('Activate this asset as a production machine.')); ?></p>
                        <form method="POST" action="<?php echo e(route('admin.assets.machines.activate', $asset)); ?>" class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <?php echo csrf_field(); ?>
                            <div>
                                <label class="erp-label"><?php echo e(__('Machine Code')); ?></label>
                                <input type="text" name="machine_code" class="erp-input w-full" required maxlength="50" value="<?php echo e(strtoupper(substr($asset->asset_number, -8))); ?>">
                            </div>
                            <div>
                                <label class="erp-label"><?php echo e(__('Machine Type')); ?></label>
                                <input type="text" name="machine_type" class="erp-input w-full" required maxlength="50" placeholder="<?php echo e(__('Offset Press, Digital Press…')); ?>">
                            </div>
                            <div>
                                <label class="erp-label"><?php echo e(__('Shift Capacity')); ?></label>
                                <input type="number" step="0.01" min="0" name="shift_capacity" class="erp-input w-full" value="10">
                            </div>
                            <div>
                                <label class="erp-label"><?php echo e(__('Hourly Capacity')); ?></label>
                                <input type="number" step="0.01" min="0" name="hourly_capacity" class="erp-input w-full" value="2">
                            </div>
                            <div class="sm:col-span-2">
                                <button type="submit" class="erp-btn-primary"><?php echo e(__('Activate for Production')); ?></button>
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
                <?php endif; ?>
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
                <div class="mb-3 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold text-slate-900"><?php echo e(__('Maintenance Timeline')); ?></h3>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('maintenance.view')): ?>
                        <?php if(Route::has('admin.assets.maintenance.work-orders.index')): ?>
                            <a href="<?php echo e(route('admin.assets.maintenance.dashboard', ['tab' => 'work-orders', 'search' => $asset->asset_number])); ?>" class="text-xs text-erp-accent hover:underline"><?php echo e(__('Work Orders')); ?></a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php if($asset->maintenanceTimelineEntries->isEmpty() && $asset->maintenanceWorkOrders->isEmpty()): ?>
                    <p class="text-sm text-slate-500"><?php echo e(__('No maintenance activity recorded yet.')); ?></p>
                <?php else: ?>
                    <ul class="space-y-2 text-sm">
                        <?php $__currentLoopData = $asset->maintenanceTimelineEntries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="border-b border-erp-border pb-2">
                                <p class="font-medium"><?php echo e($entry->title); ?></p>
                                <p class="text-xs text-slate-500"><?php echo e($entry->user?->name); ?> — <?php echo e($entry->occurred_at?->format('Y-m-d H:i')); ?></p>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
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
                <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Documents')); ?></h3>
                <p class="text-sm text-slate-500"><?php echo e(__('Document storage will be available in a later phase.')); ?></p>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Audit History')); ?></h3>
                <?php if($activityLogs->isEmpty()): ?>
                    <p class="text-sm text-slate-500"><?php echo e(__('No activity recorded yet.')); ?></p>
                <?php else: ?>
                    <ul class="space-y-2 text-sm">
                        <?php $__currentLoopData = $activityLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex justify-between gap-3 border-b border-erp-border pb-2">
                                <span><?php echo e(ucfirst(str_replace('_', ' ', $log->action))); ?> — <?php echo e($log->user?->name ?? __('System')); ?></span>
                                <span class="text-slate-500"><?php echo e($log->created_at?->format('Y-m-d H:i')); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
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
        </div>

        <div class="space-y-4">
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
                <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Actions')); ?></h3>
                <div class="flex flex-col gap-2">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('assign', $asset)): ?>
                        <details class="rounded border border-erp-border p-3">
                            <summary class="cursor-pointer text-sm font-medium"><?php echo e(__('Assign Asset')); ?></summary>
                            <form method="POST" action="<?php echo e(route('admin.assets.assign', $asset)); ?>" class="mt-3 space-y-2">
                                <?php echo csrf_field(); ?>
                                <select name="assignment_type" class="erp-select w-full" required>
                                    <option value="user"><?php echo e(__('User')); ?></option>
                                    <option value="branch"><?php echo e(__('Branch')); ?></option>
                                </select>
                                <select name="assigned_to_user_id" class="erp-select w-full">
                                    <option value=""><?php echo e(__('Select user…')); ?></option>
                                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <select name="assigned_to_branch_id" class="erp-select w-full">
                                    <option value=""><?php echo e(__('Select branch…')); ?></option>
                                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($branch->id); ?>"><?php echo e($branch->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <button type="submit" class="erp-btn-primary w-full"><?php echo e(__('Assign')); ?></button>
                            </form>
                        </details>
                        <a href="<?php echo e(route('admin.assets.transfer', $asset)); ?>" class="erp-btn-secondary w-full text-center"><?php echo e(__('Transfer Asset')); ?></a>
                        <details class="rounded border border-erp-border p-3">
                            <summary class="cursor-pointer text-sm font-medium"><?php echo e(__('Change Status')); ?></summary>
                            <form method="POST" action="<?php echo e(route('admin.assets.status', $asset)); ?>" class="mt-3 space-y-2">
                                <?php echo csrf_field(); ?>
                                <select name="status" class="erp-select w-full" required>
                                    <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($status->value); ?>" <?php if($asset->status === $status): echo 'selected'; endif; ?>><?php echo e($status->label()); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <button type="submit" class="erp-btn-secondary w-full"><?php echo e(__('Update Status')); ?></button>
                            </form>
                        </details>
                        <form method="POST" action="<?php echo e(route('admin.assets.archive', $asset)); ?>" onsubmit="return confirm('<?php echo e(__('Archive this asset?')); ?>')">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="erp-btn-secondary w-full"><?php echo e(__('Archive Asset')); ?></button>
                        </form>
                    <?php endif; ?>
                </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\assets\show.blade.php ENDPATH**/ ?>