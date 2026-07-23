<?php
    $commercialCards = [
        ['key' => 'quotations', 'label' => __('Quotes'), 'permission' => 'quotations.view'],
        ['key' => 'orders', 'label' => __('Sales Orders'), 'permission' => 'sales_orders.view'],
        ['key' => 'print_specifications', 'label' => __('Print Specifications'), 'permission' => 'crm.customers.view'],
        ['key' => 'artwork', 'label' => __('Artwork Requests'), 'permission' => 'artwork.view'],
        ['key' => 'invoices', 'label' => __('Invoices'), 'permission' => 'invoices.view'],
        ['key' => 'payments', 'label' => __('Payments'), 'permission' => 'payments.view'],
        ['key' => 'receipts', 'label' => __('Receipts'), 'permission' => 'payments.view'],
    ];
?>

<div class="crm-360__tab-toolbar">
    <?php echo $__env->make('admin.crm.customers.360.partials.customer-actions-dropdown', [
        'customer' => $customer,
        'latestOrderForRepeat' => $latestOrderForRepeat ?? null,
        'buttonClass' => 'crm-360__btn crm-360__btn--outline min-h-[2.75rem]',
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>

<div class="crm-360__commercial-summary">
    <?php $__currentLoopData = $commercialCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check($card['permission'])): ?>
            <?php $count = $commercial['counts'][$card['key']] ?? null; ?>
            <div class="crm-360__commercial-card">
                <span class="crm-360__commercial-card-label"><?php echo e($card['label']); ?></span>
                <span class="crm-360__commercial-card-value"><?php echo e($count !== null ? $count : '—'); ?></span>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<?php if(! empty($commercial['intelligence'])): ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
        <h3 class="text-sm font-semibold mb-3"><?php echo e(__('Commercial summary')); ?></h3>
        <dl class="grid grid-cols-2 gap-3 text-sm md:grid-cols-3 lg:grid-cols-6">
            <div><dt class="text-slate-500"><?php echo e(__('Total orders')); ?></dt><dd class="font-semibold"><?php echo e($commercial['intelligence']['total_orders']); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Revenue')); ?></dt><dd class="font-mono"><?php echo e(number_format($commercial['intelligence']['total_revenue'], 2)); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Payments')); ?></dt><dd class="font-mono"><?php echo e(number_format($commercial['intelligence']['total_payments'], 2)); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Outstanding')); ?></dt><dd class="font-mono"><?php echo e(number_format($commercial['financial_summary']['outstanding'] ?? 0, 2)); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Est. profit')); ?></dt><dd class="font-mono"><?php echo e(number_format($commercial['intelligence']['estimated_profit'], 2)); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Margin')); ?></dt><dd><?php echo e(number_format($commercial['intelligence']['estimated_margin_percent'], 1)); ?>%</dd></div>
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
<?php endif; ?>

<?php if(! empty($commercial['recent_jobs'])): ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
        <h3 class="text-sm font-semibold mb-3"><?php echo e(__('Recent jobs')); ?></h3>
        <div class="crm-360__table-scroll">
            <table class="erp-table w-full text-sm">
                <thead><tr><th><?php echo e(__('Job')); ?></th><th><?php echo e(__('Status')); ?></th><th><?php echo e(__('Revenue')); ?></th><th><?php echo e(__('Profit')); ?></th><th><?php echo e(__('Margin')); ?></th></tr></thead>
                <tbody>
                    <?php $__currentLoopData = $commercial['recent_jobs']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($job['job_number']); ?></td>
                            <td><?php echo e($job['status']); ?></td>
                            <td class="font-mono"><?php echo e(number_format($job['revenue'], 2)); ?></td>
                            <td class="font-mono"><?php echo e(number_format($job['estimated_profit'], 2)); ?></td>
                            <td><?php echo e(number_format($job['estimated_margin_percent'], 1)); ?>%</td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
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
<?php endif; ?>

<div class="crm-360__grid crm-360__grid--commercial">
    <?php $__currentLoopData = [
        ['key' => 'quotations', 'title' => __('Quotations'), 'permission' => 'quotations.view', 'type' => 'standard'],
        ['key' => 'orders', 'title' => __('Sales orders'), 'permission' => 'sales_orders.view', 'type' => 'standard'],
        ['key' => 'artwork', 'title' => __('Artwork requests'), 'permission' => 'artwork.view', 'type' => 'standard'],
        ['key' => 'invoices', 'title' => __('Invoices'), 'permission' => 'invoices.view', 'type' => 'standard'],
        ['key' => 'payments', 'title' => __('Payments'), 'permission' => 'payments.view', 'type' => 'standard'],
        ['key' => 'receipts', 'title' => __('Receipts'), 'permission' => 'payments.view', 'type' => 'receipts'],
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check($section['permission'])): ?>
            <section class="crm-360__card">
                <h2 class="crm-360__card-title"><?php echo e($section['title']); ?></h2>
                <div class="crm-360__table-scroll">
                    <table class="crm-360__table">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Reference')); ?></th>
                                <?php if($section['type'] === 'receipts'): ?>
                                    <th><?php echo e(__('Payment')); ?></th>
                                    <th><?php echo e(__('Amount')); ?></th>
                                <?php endif; ?>
                                <th><?php echo e(__('Status')); ?></th>
                                <th><?php echo e(__('Date')); ?></th>
                                <?php if($section['key'] === 'orders'): ?>
                                    <th class="text-right"><?php echo e(__('Actions')); ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $commercial[$section['key']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <?php if($row['url']): ?>
                                            <a href="<?php echo e($row['url']); ?>" class="crm-360__row-link" data-turbo-frame="erp-main"><?php echo e($row['number']); ?></a>
                                        <?php else: ?>
                                            <?php echo e($row['number']); ?>

                                        <?php endif; ?>
                                    </td>
                                    <?php if($section['type'] === 'receipts'): ?>
                                        <td>
                                            <a href="<?php echo e($row['payment_url']); ?>" class="crm-360__row-link" data-turbo-frame="erp-main"><?php echo e($row['payment_number']); ?></a>
                                        </td>
                                        <td class="font-mono"><?php echo e(number_format($row['amount'], 2)); ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <?php if(! empty($row['status_value'])): ?>
                                            <?php if (isset($component)) { $__componentOriginal6b1e38fda99422b6943f33aba545ca7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.enum-status-badge','data' => ['status' => $row['status_value']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.enum-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['status_value'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $attributes = $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $component = $__componentOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?>
                                        <?php else: ?>
                                            <?php echo e($row['status']); ?>

                                        <?php endif; ?>
                                    </td>
                                    <td class="text-slate-500"><?php echo e($row['date']?->format('d M Y') ?? '—'); ?></td>
                                    <?php if($section['key'] === 'orders'): ?>
                                        <td class="text-right">
                                            <?php echo $__env->make('admin.crm.customers.360.partials.repeat-order-form', [
                                                'customer' => $customer,
                                                'orderId' => $row['id'],
                                                'orderNumber' => $row['number'],
                                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="<?php echo e($section['key'] === 'orders' ? 4 : ($section['type'] === 'receipts' ? 5 : 3)); ?>" class="crm-360__empty-inline"><?php echo e(__('No data yet')); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/crm/customers/360/tab-commercial.blade.php ENDPATH**/ ?>