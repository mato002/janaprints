<?php
    $collapsed = $collapsed ?? false;
    $sections = [
        'quotations' => __('Quotations'),
        'sales_orders' => __('Sales orders'),
        'artwork' => __('Artwork approvals'),
        'jobs' => __('Production jobs'),
        'invoices' => __('Invoices'),
        'payments' => __('Payments'),
        'credit_notes' => __('Credit notes'),
        'deliveries' => __('Deliveries'),
    ];
?>
<div class="space-y-1">
    <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $title): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(! empty($records[$key]) && count($records[$key]) > 0): ?>
            <?php if($collapsed): ?>
                <details class="rounded-lg border border-erp-border bg-white">
                    <summary class="cursor-pointer px-3 py-2 text-xs font-semibold text-slate-700">
                        <?php echo e($title); ?> <span class="font-normal text-slate-400">(<?php echo e(count($records[$key])); ?>)</span>
                    </summary>
                    <ul class="border-t border-erp-border px-3 py-2 space-y-1 text-xs">
                        <?php $__currentLoopData = $records[$key]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <a href="<?php echo e($row['view_url']); ?>" class="font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main"><?php echo e($row['number']); ?></a>
                                <span class="text-slate-400"> · <?php echo e($row['status']); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </details>
            <?php else: ?>
                <div class="erp-card p-3">
                    <h4 class="text-xs font-semibold uppercase text-slate-600"><?php echo e($title); ?></h4>
                    <ul class="mt-2 space-y-1 text-xs">
                        <?php $__currentLoopData = $records[$key]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <a href="<?php echo e($row['view_url']); ?>" class="font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main"><?php echo e($row['number']); ?></a>
                                <span class="text-slate-400"> · <?php echo e($row['status']); ?> · <?php echo e($row['date']); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\partials\linked-records.blade.php ENDPATH**/ ?>