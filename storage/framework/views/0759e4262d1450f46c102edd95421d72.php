<div class="space-y-6">
    <section>
        <h3 class="mb-2 text-sm font-semibold text-slate-900"><?php echo e(__('Sales orders')); ?></h3>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead><tr><th><?php echo e(__('Order')); ?></th><th><?php echo e(__('Date')); ?></th><th><?php echo e(__('Total')); ?></th><th><?php echo e(__('Status')); ?></th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $tabData['salesOrders']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><a class="text-erp-accent hover:underline" href="<?php echo e(route('admin.sales-orders.show', $order)); ?>"><?php echo e($order->order_number); ?></a></td>
                            <td><?php echo e($order->order_date?->format('Y-m-d')); ?></td>
                            <td class="tabular-nums"><?php echo e(number_format((float) $order->total_amount, 2)); ?></td>
                            <td><?php echo e($order->status->label()); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center text-slate-500"><?php echo e(__('No orders yet.')); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($tabData['salesOrders']->links()); ?>

    </section>

    <section>
        <h3 class="mb-2 text-sm font-semibold text-slate-900"><?php echo e(__('Job cards')); ?></h3>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead><tr><th><?php echo e(__('Job')); ?></th><th><?php echo e(__('Order')); ?></th><th><?php echo e(__('Status')); ?></th><th><?php echo e(__('Produced')); ?></th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $tabData['jobCards']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><a class="text-erp-accent hover:underline" href="<?php echo e(route('admin.production.job-cards.show', $job)); ?>"><?php echo e($job->job_card_number); ?></a></td>
                            <td><?php echo e($job->salesOrder?->order_number ?? '—'); ?></td>
                            <td><?php echo e($job->status->label()); ?></td>
                            <td><?php echo e($job->actual_end_date?->format('Y-m-d') ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center text-slate-500"><?php echo e(__('No job cards yet.')); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($tabData['jobCards']->links()); ?>

    </section>

    <section>
        <h3 class="mb-2 text-sm font-semibold text-slate-900"><?php echo e(__('Invoices')); ?></h3>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead><tr><th><?php echo e(__('Invoice')); ?></th><th><?php echo e(__('Date')); ?></th><th><?php echo e(__('Total')); ?></th><th><?php echo e(__('Status')); ?></th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $tabData['invoices']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><a class="text-erp-accent hover:underline" href="<?php echo e(route('admin.invoices.show', $invoice)); ?>"><?php echo e($invoice->invoice_number); ?></a></td>
                            <td><?php echo e($invoice->invoice_date?->format('Y-m-d')); ?></td>
                            <td class="tabular-nums"><?php echo e(number_format((float) $invoice->total_amount, 2)); ?></td>
                            <td><?php echo e($invoice->status->label()); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center text-slate-500"><?php echo e(__('No invoices yet.')); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($tabData['invoices']->links()); ?>

    </section>

    <section>
        <h3 class="mb-2 text-sm font-semibold text-slate-900"><?php echo e(__('Repeat orders')); ?></h3>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead><tr><th><?php echo e(__('Order')); ?></th><th><?php echo e(__('Source')); ?></th><th><?php echo e(__('Date')); ?></th><th><?php echo e(__('Total')); ?></th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $tabData['repeatOrders']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><a class="text-erp-accent hover:underline" href="<?php echo e(route('admin.sales-orders.show', $order)); ?>"><?php echo e($order->order_number); ?></a></td>
                            <td><?php echo e($order->repeatSource?->order_number ?? '—'); ?></td>
                            <td><?php echo e($order->order_date?->format('Y-m-d')); ?></td>
                            <td class="tabular-nums"><?php echo e(number_format((float) $order->total_amount, 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center text-slate-500"><?php echo e(__('No repeat orders yet.')); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($tabData['repeatOrders']->links()); ?>

    </section>

    <section>
        <h3 class="mb-2 text-sm font-semibold text-slate-900"><?php echo e(__('Production sessions')); ?></h3>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead><tr><th><?php echo e(__('Job')); ?></th><th><?php echo e(__('Started')); ?></th><th><?php echo e(__('Good qty')); ?></th><th><?php echo e(__('Operator')); ?></th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $tabData['sessions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($session->jobCard?->job_card_number ?? '—'); ?></td>
                            <td><?php echo e($session->started_at?->format('Y-m-d H:i')); ?></td>
                            <td class="tabular-nums"><?php echo e($session->good_quantity); ?></td>
                            <td><?php echo e($session->operator?->name ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center text-slate-500"><?php echo e(__('No production sessions yet.')); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($tabData['sessions']->links()); ?>

    </section>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\print-specifications\workspace\tabs\usage_history.blade.php ENDPATH**/ ?>