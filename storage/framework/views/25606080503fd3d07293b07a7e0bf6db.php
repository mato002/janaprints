<?php if(! empty($panel['empty'])): ?>
    <p class="text-sm text-slate-600"><?php echo e($panel['empty']); ?></p>
<?php else: ?>
    <div class="mb-3">
        <p class="truncate text-sm font-semibold text-slate-900"><?php echo e($panel['name']); ?></p>
        <?php if(! empty($panel['customer_type'])): ?>
            <p class="text-xs text-slate-500"><?php echo e($panel['customer_type']); ?></p>
        <?php endif; ?>
    </div>

    <?php if(count($panel['warnings'] ?? []) > 0): ?>
        <ul class="mb-3 space-y-1 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs">
            <?php $__currentLoopData = $panel['warnings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'flex items-start gap-1.5',
                    'text-rose-800' => ($warning['severity'] ?? '') === 'danger',
                    'text-amber-900' => ($warning['severity'] ?? '') !== 'danger',
                ]); ?>">
                    <span aria-hidden="true">⚠</span>
                    <span><?php echo e($warning['message']); ?></span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    <?php endif; ?>

    <dl class="grid grid-cols-2 gap-x-3 gap-y-2 text-sm">
        <?php if($panel['outstanding_balance'] ?? null): ?>
            <div>
                <dt class="text-xs text-slate-500"><?php echo e(__('Outstanding')); ?></dt>
                <dd class="font-mono text-amber-800"><?php echo e($panel['outstanding_balance']); ?></dd>
            </div>
        <?php endif; ?>
        <?php if($panel['credit_limit'] ?? null): ?>
            <div>
                <dt class="text-xs text-slate-500"><?php echo e(__('Credit limit')); ?></dt>
                <dd class="font-mono text-slate-900"><?php echo e($panel['credit_limit']); ?></dd>
            </div>
        <?php endif; ?>
        <?php if($panel['overdue_amount'] ?? null): ?>
            <div>
                <dt class="text-xs text-slate-500"><?php echo e(__('Overdue')); ?></dt>
                <dd class="font-mono text-rose-700"><?php echo e($panel['overdue_amount']); ?></dd>
            </div>
        <?php endif; ?>
        <?php if(($panel['artwork_pending_count'] ?? 0) > 0): ?>
            <div>
                <dt class="text-xs text-slate-500"><?php echo e(__('Artwork waiting')); ?></dt>
                <dd class="font-medium text-violet-800"><?php echo e($panel['artwork_pending_count']); ?></dd>
            </div>
        <?php endif; ?>
        <div class="col-span-2">
            <dt class="text-xs text-slate-500"><?php echo e(__('Contact')); ?></dt>
            <dd class="text-slate-800">
                <?php if($panel['contact_person'] ?? null): ?>
                    <span class="block"><?php echo e($panel['contact_person']); ?></span>
                <?php endif; ?>
                <?php echo e($panel['phone'] ?? '—'); ?> · <?php echo e($panel['email'] ?? '—'); ?>

            </dd>
        </div>
        <?php if($panel['last_order'] ?? null): ?>
            <div class="col-span-2">
                <dt class="text-xs text-slate-500"><?php echo e(__('Last order')); ?></dt>
                <dd class="text-slate-900">
                    <?php echo e($panel['last_order']['product'] ?? $panel['last_order']['order_number']); ?>

                    <span class="text-xs text-slate-500">· <?php echo e($panel['last_order']['order_date'] ?? ''); ?></span>
                </dd>
            </div>
        <?php endif; ?>
    </dl>

    <?php if(count($panel['open_quotations'] ?? []) > 0): ?>
        <div class="mt-3 border-t border-erp-border pt-3">
            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e(__('Open quotes')); ?></p>
            <ul class="space-y-1 text-xs">
                <?php $__currentLoopData = $panel['open_quotations']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>
                        <a href="<?php echo e($quote['create_url']); ?>" class="text-erp-primary hover:underline" data-erp-modal-open><?php echo e($quote['quotation_number']); ?></a>
                        <span class="text-slate-500"> · <?php echo e($quote['status']); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\desk\partials\walk-in-panel\customer.blade.php ENDPATH**/ ?>