<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Asset Reports'),'breadcrumbs' => [['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Reports')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Fixed Asset Reports'),'description' => __('Tenant-scoped, branch-filterable read-only reports.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Fixed Asset Reports')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Tenant-scoped, branch-filterable read-only reports.'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.index-toolbar','data' => ['action' => route('admin.assets.finance.reports.index'),'resetUrl' => route('admin.assets.finance.reports.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.index-toolbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.assets.finance.reports.index')),'reset-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.assets.finance.reports.index'))]); ?>
            <select name="report" class="erp-toolbar-select min-w-[12rem]" aria-label="<?php echo e(__('Report')); ?>">
                <option value="register" <?php if($report === 'register'): echo 'selected'; endif; ?>><?php echo e(__('Asset Register')); ?></option>
                <option value="valuation" <?php if($report === 'valuation'): echo 'selected'; endif; ?>><?php echo e(__('Asset Valuation')); ?></option>
                <option value="depreciation_schedule" <?php if($report === 'depreciation_schedule'): echo 'selected'; endif; ?>><?php echo e(__('Depreciation Report')); ?></option>
                <option value="maintenance" <?php if($report === 'maintenance'): echo 'selected'; endif; ?>><?php echo e(__('Maintenance Report')); ?></option>
                <option value="custody" <?php if($report === 'custody'): echo 'selected'; endif; ?>><?php echo e(__('Custody Report')); ?></option>
                <option value="warranty_expiry" <?php if($report === 'warranty_expiry'): echo 'selected'; endif; ?>><?php echo e(__('Warranty Expiry')); ?></option>
                <option value="replacement" <?php if($report === 'replacement'): echo 'selected'; endif; ?>><?php echo e(__('Replacement Candidates')); ?></option>
                <option value="fully_depreciated" <?php if($report === 'fully_depreciated'): echo 'selected'; endif; ?>><?php echo e(__('Fully Depreciated')); ?></option>
                <option value="near_end_of_life" <?php if($report === 'near_end_of_life'): echo 'selected'; endif; ?>><?php echo e(__('Near End of Life')); ?></option>
            </select>
            <?php if(in_array($report, ['depreciation_schedule', 'maintenance'], true)): ?>
                <input type="date" name="from" value="<?php echo e($filters['from'] ?? ''); ?>" class="erp-toolbar-input" aria-label="<?php echo e(__('From date')); ?>">
                <input type="date" name="to" value="<?php echo e($filters['to'] ?? ''); ?>" class="erp-toolbar-input" aria-label="<?php echo e(__('To date')); ?>">
            <?php endif; ?>
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
        <div class="overflow-x-auto">
            <?php if($report === 'valuation'): ?>
                <table class="erp-table w-full text-sm">
                    <thead><tr><th><?php echo e(__('Asset')); ?></th><th><?php echo e(__('Cost')); ?></th><th><?php echo e(__('NBV')); ?></th><th><?php echo e(__('Monthly Depr.')); ?></th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($row['asset']->asset_number); ?></td>
                                <td><?php echo e(number_format($row['profile']['acquisition_cost'], 2)); ?></td>
                                <td><?php echo e(number_format($row['profile']['net_book_value'], 2)); ?></td>
                                <td><?php echo e(number_format($row['profile']['monthly_depreciation'], 2)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="text-slate-500"><?php echo e(__('No assets match the selected filters.')); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php elseif($report === 'depreciation_schedule'): ?>
                <table class="erp-table w-full text-sm">
                    <thead><tr><th><?php echo e(__('Period')); ?></th><th><?php echo e(__('Asset')); ?></th><th><?php echo e(__('Amount')); ?></th><th><?php echo e(__('NBV After')); ?></th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($entry->period_date?->format('Y-m')); ?></td>
                                <td><?php echo e($entry->asset?->asset_number); ?></td>
                                <td><?php echo e(number_format($entry->depreciation_amount, 2)); ?></td>
                                <td><?php echo e(number_format($entry->net_book_value_after, 2)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="text-slate-500"><?php echo e(__('No depreciation entries match the selected filters.')); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php elseif($report === 'maintenance'): ?>
                <table class="erp-table w-full text-sm">
                    <thead><tr><th><?php echo e(__('Work Order')); ?></th><th><?php echo e(__('Asset')); ?></th><th><?php echo e(__('Type')); ?></th><th><?php echo e(__('Status')); ?></th><th><?php echo e(__('Opened')); ?></th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($order->work_order_no); ?></td>
                                <td><?php echo e($order->asset?->asset_number); ?></td>
                                <td><?php echo e($order->maintenance_type?->label() ?? $order->maintenance_type); ?></td>
                                <td><?php echo e($order->status?->label() ?? $order->status); ?></td>
                                <td><?php echo e($order->opened_at?->format('Y-m-d')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="5" class="text-slate-500"><?php echo e(__('No maintenance work orders match the selected filters.')); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php elseif($report === 'custody'): ?>
                <table class="erp-table w-full text-sm">
                    <thead><tr><th><?php echo e(__('Asset')); ?></th><th><?php echo e(__('Branch')); ?></th><th><?php echo e(__('Custodian')); ?></th><th><?php echo e(__('Condition')); ?></th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($asset->asset_number); ?></td>
                                <td><?php echo e($asset->branch?->name); ?></td>
                                <td><?php echo e($asset->assignedUser?->name ?? $asset->assignedEmployee?->full_name ?? '—'); ?></td>
                                <td><?php echo e($asset->current_condition?->label() ?? '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="text-slate-500"><?php echo e(__('No assets match the selected filters.')); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php elseif($report === 'warranty_expiry'): ?>
                <table class="erp-table w-full text-sm">
                    <thead><tr><th><?php echo e(__('Asset')); ?></th><th><?php echo e(__('Vendor')); ?></th><th><?php echo e(__('Warranty End')); ?></th><th><?php echo e(__('Reference')); ?></th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warranty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($warranty->asset?->asset_number); ?></td>
                                <td><?php echo e($warranty->vendor?->vendor_name ?? '—'); ?></td>
                                <td><?php echo e($warranty->warranty_end?->format('Y-m-d')); ?></td>
                                <td><?php echo e($warranty->reference_number ?? '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="text-slate-500"><?php echo e(__('No warranties expiring in the next 90 days.')); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php elseif($report === 'replacement'): ?>
                <table class="erp-table w-full text-sm">
                    <thead><tr><th><?php echo e(__('Asset')); ?></th><th><?php echo e(__('Priority')); ?></th><th><?php echo e(__('Health')); ?></th><th><?php echo e(__('Reasons')); ?></th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($row['asset']->asset_number); ?> — <?php echo e($row['asset']->asset_name); ?></td>
                                <td><?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => $row['priority'] === 'high' ? 'danger' : ($row['priority'] === 'medium' ? 'warning' : 'neutral')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['priority'] === 'high' ? 'danger' : ($row['priority'] === 'medium' ? 'warning' : 'neutral'))]); ?><?php echo e(ucfirst($row['priority'])); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?></td>
                                <td><?php echo e($row['health_score'] ?? '—'); ?></td>
                                <td><?php echo e(implode(', ', $row['reasons'])); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="text-slate-500"><?php echo e(__('No replacement candidates identified.')); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <table class="erp-table w-full text-sm">
                    <thead><tr><th><?php echo e(__('Asset')); ?></th><th><?php echo e(__('Category')); ?></th><th><?php echo e(__('Cost')); ?></th><th><?php echo e(__('NBV')); ?></th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><a href="<?php echo e(route('admin.assets.finance.profile', $asset)); ?>" class="erp-link"><?php echo e($asset->asset_number); ?></a></td>
                                <td><?php echo e($asset->category?->name); ?></td>
                                <td><?php echo e(number_format($asset->acquisition_cost, 2)); ?></td>
                                <td><?php echo e(number_format($asset->netBookValue(), 2)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="text-slate-500"><?php echo e(__('No assets match the selected filters.')); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\assets\finance\reports\index.blade.php ENDPATH**/ ?>