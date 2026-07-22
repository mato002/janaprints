<?php if (isset($component)) { $__componentOriginald3ad0f200dc20b794011e332a16c068d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3ad0f200dc20b794011e332a16c068d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal-form','data' => ['title' => $salesOrder->order_number,'maxWidth' => '4xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($salesOrder->order_number),'maxWidth' => '4xl']); ?>
    <div class="space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="erp-badge"><?php echo e(str_replace('_', ' ', $salesOrder->status->value)); ?></span>
            <span class="text-sm text-slate-600"><?php echo e($salesOrder->customer?->company_name ?? '—'); ?></span>
        </div>

        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div><dt class="text-slate-500"><?php echo e(__('Subtotal')); ?></dt><dd class="font-mono"><?php echo e(number_format((float) $salesOrder->subtotal, 2)); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Total')); ?></dt><dd class="font-mono font-medium"><?php echo e(number_format((float) $salesOrder->total_amount, 2)); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Required')); ?></dt><dd><?php echo e($salesOrder->required_date?->format('d M Y') ?? '—'); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Fulfilment')); ?></dt><dd><?php echo e($salesOrder->fulfilment_method?->label() ?? '—'); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Billing')); ?></dt><dd><?php echo e($salesOrder->billing_type?->label() ?? '—'); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Job')); ?></dt><dd><?php echo e($salesOrder->jobCard?->job_card_number ?? '—'); ?></dd></div>
        </dl>

        <?php $financial = app(\App\Support\Sales\SalesOrderFinancialStatusService::class)->snapshot($salesOrder); ?>
        <p class="text-sm text-slate-600">
            <?php echo e(__('Payment status')); ?>: <span class="font-medium"><?php echo e($financial['financial_status_label'] ?? '—'); ?></span>
        </p>

        <?php if($salesOrder->items->isNotEmpty()): ?>
            <div class="rounded-lg border border-erp-border bg-white p-4">
                <h3 class="mb-2 text-sm font-semibold text-slate-900"><?php echo e(__('Order lines')); ?></h3>
                <?php $__currentLoopData = $salesOrder->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="border-b border-slate-100 py-2 text-sm last:border-0">
                        <span class="font-medium"><?php echo e($item->item_name); ?></span>
                        <span class="text-slate-500"> — <?php echo e(number_format((float) $item->quantity, 2)); ?> × <?php echo e(number_format((float) $item->unit_price, 2)); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <?php if(request('from') === 'sales-desk'): ?>
            <div class="flex flex-wrap gap-2">
                <a href="<?php echo e(route('admin.sales-orders.edit', [$salesOrder, 'from' => 'sales-desk'])); ?>" class="erp-btn-secondary text-sm" data-erp-modal-open><?php echo e(__('Edit order')); ?></a>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Sales\CustomerPayment::class)): ?>
                    <a href="<?php echo e(route('admin.payments.create', ['from' => 'sales-desk', 'customer_id' => $salesOrder->customer_id, 'sales_order_id' => $salesOrder->id])); ?>" class="erp-btn-secondary text-sm" data-erp-modal-open><?php echo e(__('Record payment')); ?></a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Sales\CustomerInvoice::class)): ?>
                    <a href="<?php echo e(route('admin.invoices.from-sales-order', [$salesOrder, 'from' => 'sales-desk'])); ?>" class="erp-btn-secondary text-sm" data-erp-modal-open><?php echo e(__('Create invoice')); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald3ad0f200dc20b794011e332a16c068d)): ?>
<?php $attributes = $__attributesOriginald3ad0f200dc20b794011e332a16c068d; ?>
<?php unset($__attributesOriginald3ad0f200dc20b794011e332a16c068d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald3ad0f200dc20b794011e332a16c068d)): ?>
<?php $component = $__componentOriginald3ad0f200dc20b794011e332a16c068d; ?>
<?php unset($__componentOriginald3ad0f200dc20b794011e332a16c068d); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/desk/order-modal.blade.php ENDPATH**/ ?>