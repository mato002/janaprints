<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Financial Profile'),'breadcrumbs' => [['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => $asset->asset_number, 'url' => route('admin.assets.show', $asset)], ['label' => __('Financial Profile')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $asset->asset_name,'description' => __('Financial profile and depreciation history.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($asset->asset_name),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Financial profile and depreciation history.'))]); ?>
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

    <div class="mb-4 flex gap-2 border-b border-erp-border text-sm">
        <span class="border-b-2 border-erp-primary px-3 py-2 font-medium"><?php echo e(__('Acquisition')); ?></span>
        <span class="px-3 py-2 text-slate-500"><?php echo e(__('Valuation')); ?></span>
        <span class="px-3 py-2 text-slate-500"><?php echo e(__('Depreciation')); ?></span>
        <span class="px-3 py-2 text-slate-500"><?php echo e(__('Accounting')); ?></span>
        <span class="px-3 py-2 text-slate-500"><?php echo e(__('Reconciliation')); ?></span>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
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
            <h3 class="mb-3 font-semibold"><?php echo e(__('Financial Summary')); ?></h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-slate-500"><?php echo e(__('Acquisition Cost')); ?></dt><dd><?php echo e(number_format($profile['acquisition_cost'], 2)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Capitalization Date')); ?></dt><dd><?php echo e($profile['capitalization_date']?->format('Y-m-d')); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Residual Value')); ?></dt><dd><?php echo e(number_format($profile['residual_value'], 2)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Useful Life')); ?></dt><dd><?php echo e($profile['useful_life_years']); ?> <?php echo e(__('years')); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Depreciation Method')); ?></dt><dd><?php echo e($profile['depreciation_method']->label()); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Depreciation Start')); ?></dt><dd><?php echo e($profile['depreciation_start_date']?->format('Y-m-d')); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Accumulated Depreciation')); ?></dt><dd><?php echo e(number_format($profile['accumulated_depreciation'], 2)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Net Book Value')); ?></dt><dd class="font-semibold"><?php echo e(number_format($profile['net_book_value'], 2)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Monthly Depreciation')); ?></dt><dd><?php echo e(number_format($profile['monthly_depreciation'], 2)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Annual Depreciation')); ?></dt><dd><?php echo e(number_format($profile['annual_depreciation'], 2)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Remaining Life')); ?></dt><dd><?php echo e($profile['remaining_months']); ?> <?php echo e(__('months')); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Fully Depreciated')); ?></dt><dd><?php echo e($profile['is_fully_depreciated'] ? __('Yes') : __('No')); ?></dd></div>
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
            <h3 class="mb-3 font-semibold"><?php echo e(__('Category GL Mapping')); ?></h3>
            <dl class="space-y-2 text-sm">
                <div><dt class="text-slate-500"><?php echo e(__('Asset Account')); ?></dt><dd><?php echo e($asset->category?->default_gl_code ?? '—'); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Accumulated Depreciation')); ?></dt><dd><?php echo e($asset->category?->accumulated_depreciation_gl_code ?? __('System default')); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Depreciation Expense')); ?></dt><dd><?php echo e($asset->category?->depreciation_expense_gl_code ?? __('System default')); ?></dd></div>
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
    </div>

    <?php if($asset->depreciationEntries->isNotEmpty()): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-4']); ?>
            <h3 class="mb-3 font-semibold"><?php echo e(__('Depreciation History')); ?></h3>
            <table class="erp-table w-full text-sm">
                <thead><tr><th><?php echo e(__('Period')); ?></th><th><?php echo e(__('Amount')); ?></th><th><?php echo e(__('Accumulated')); ?></th><th><?php echo e(__('NBV')); ?></th><th><?php echo e(__('Journal')); ?></th></tr></thead>
                <tbody>
                    <?php $__currentLoopData = $asset->depreciationEntries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($entry->period_date?->format('Y-m')); ?></td>
                            <td><?php echo e(number_format($entry->depreciation_amount, 2)); ?></td>
                            <td><?php echo e(number_format($entry->accumulated_after, 2)); ?></td>
                            <td><?php echo e(number_format($entry->net_book_value_after, 2)); ?></td>
                            <td><?php echo e($entry->journal?->reference ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
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

    <?php if($asset->financeTimelineEntries->isNotEmpty()): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-4']); ?>
            <h3 class="mb-3 font-semibold"><?php echo e(__('Finance Timeline')); ?></h3>
            <ul class="space-y-2 text-sm">
                <?php $__currentLoopData = $asset->financeTimelineEntries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex justify-between border-b border-erp-border pb-2">
                        <span><?php echo e($entry->title); ?> — <?php echo e($entry->user?->name ?? __('System')); ?></span>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\assets\finance\profile.blade.php ENDPATH**/ ?>