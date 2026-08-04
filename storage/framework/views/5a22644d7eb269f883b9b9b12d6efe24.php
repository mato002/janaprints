<?php
    $profile = $tabData['profile'] ?? [];
    $recentQuotations = $tabData['recent_quotations'] ?? collect();
    $recentOrders = $tabData['recent_orders'] ?? collect();
    $recentArtwork = $tabData['recent_artwork'] ?? collect();
    $recentJobs = $tabData['recent_jobs'] ?? collect();
?>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
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
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Profile summary')); ?></h3>
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('Type')); ?></dt><dd><?php echo e($profile['type']?->value ?? '—'); ?></dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('City')); ?></dt><dd><?php echo e($profile['city'] ?? '—'); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Address')); ?></dt><dd class="mt-0.5"><?php echo e($profile['address'] ?? '—'); ?></dd></div>
            <?php if(! empty($profile['website'])): ?>
                <div><dt class="text-slate-500"><?php echo e(__('Website')); ?></dt><dd class="mt-0.5"><a href="<?php echo e($profile['website']); ?>" class="text-erp-accent hover:text-erp-accent-hover" target="_blank" rel="noopener"><?php echo e($profile['website']); ?></a></dd></div>
            <?php endif; ?>
            <?php if(! empty($profile['kra_pin'])): ?>
                <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('KRA PIN')); ?></dt><dd><?php echo e($profile['kra_pin']); ?></dd></div>
            <?php endif; ?>
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

    <div class="lg:col-span-2 grid grid-cols-1 gap-4 md:grid-cols-2">
        <?php echo $__env->make('admin.crm.customers.workspace.partials.recent-list', [
            'customer' => $customer,
            'title' => __('Recent quotations'),
            'empty' => __('No quotations yet.'),
            'items' => $recentQuotations,
            'permission' => 'quotations.view',
            'rowView' => 'admin.crm.customers.workspace.partials.recent-quotation-row',
            'tab' => 'quotations',
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.crm.customers.workspace.partials.recent-list', [
            'customer' => $customer,
            'title' => __('Recent orders'),
            'empty' => __('No sales orders yet.'),
            'items' => $recentOrders,
            'permission' => 'sales_orders.view',
            'rowView' => 'admin.crm.customers.workspace.partials.recent-order-row',
            'tab' => 'sales-orders',
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.crm.customers.workspace.partials.recent-list', [
            'customer' => $customer,
            'title' => __('Recent artwork'),
            'empty' => __('No artwork requests yet.'),
            'items' => $recentArtwork,
            'permission' => 'artwork.view',
            'rowView' => 'admin.crm.customers.workspace.partials.recent-artwork-row',
            'tab' => 'artwork',
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.crm.customers.workspace.partials.recent-list', [
            'customer' => $customer,
            'title' => __('Recent jobs'),
            'empty' => __('No production jobs yet.'),
            'items' => $recentJobs,
            'permission' => 'production.view',
            'rowView' => 'admin.crm.customers.workspace.partials.recent-job-row',
            'tab' => 'production',
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\workspace\tabs\overview.blade.php ENDPATH**/ ?>