<?php if($customerContext): ?>
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
        <div class="mb-2 flex items-start justify-between gap-2">
            <div class="min-w-0">
                <h3 class="truncate text-sm font-semibold text-slate-900"><?php echo e($customerContext['name']); ?></h3>
                <?php if($customerContext['customer_type'] ?? null): ?>
                    <p class="text-xs text-slate-500"><?php echo e($customerContext['customer_type']); ?></p>
                <?php endif; ?>
            </div>
            <div class="flex shrink-0 gap-2">
                <?php if($deskUrls['customer_360'] ?? null): ?>
                    <a href="<?php echo e($deskUrls['customer_360']); ?>" class="text-xs text-erp-primary hover:underline" data-turbo-frame="_top"><?php echo e(__('360')); ?></a>
                <?php endif; ?>
                <?php if($deskUrls['edit_customer'] ?? null): ?>
                    <a href="<?php echo e($deskUrls['edit_customer']); ?>" class="text-xs text-erp-primary hover:underline" data-erp-modal-open><?php echo e(__('Edit')); ?></a>
                <?php endif; ?>
            </div>
        </div>

        <?php if(count($customerContext['warnings'] ?? []) > 0): ?>
            <ul class="mb-3 space-y-1 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs">
                <?php $__currentLoopData = $customerContext['warnings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
            <?php if($customerContext['outstanding_balance'] ?? null): ?>
                <div>
                    <dt class="text-xs text-slate-500"><?php echo e(__('Outstanding')); ?></dt>
                    <dd class="font-mono text-amber-800"><?php echo e($customerContext['outstanding_balance']); ?></dd>
                </div>
            <?php endif; ?>
            <?php if($customerContext['credit_limit'] ?? null): ?>
                <div>
                    <dt class="text-xs text-slate-500"><?php echo e(__('Credit limit')); ?></dt>
                    <dd class="font-mono text-slate-900"><?php echo e($customerContext['credit_limit']); ?></dd>
                </div>
            <?php endif; ?>
            <?php if($customerContext['overdue_amount'] ?? null): ?>
                <div>
                    <dt class="text-xs text-slate-500"><?php echo e(__('Overdue')); ?></dt>
                    <dd class="font-mono text-rose-700"><?php echo e($customerContext['overdue_amount']); ?></dd>
                </div>
            <?php endif; ?>
            <?php if(($customerContext['open_quotes_count'] ?? 0) > 0): ?>
                <div>
                    <dt class="text-xs text-slate-500"><?php echo e(__('Active quotes')); ?></dt>
                    <dd class="font-medium text-slate-900"><?php echo e($customerContext['open_quotes_count']); ?></dd>
                </div>
            <?php endif; ?>
            <?php if(($customerContext['open_jobs_count'] ?? 0) > 0): ?>
                <div>
                    <dt class="text-xs text-slate-500"><?php echo e(__('Open jobs')); ?></dt>
                    <dd class="font-medium text-slate-900"><?php echo e($customerContext['open_jobs_count']); ?></dd>
                </div>
            <?php endif; ?>
            <?php if(($customerContext['artwork_pending_count'] ?? 0) > 0): ?>
                <div>
                    <dt class="text-xs text-slate-500"><?php echo e(__('Artwork waiting')); ?></dt>
                    <dd class="font-medium text-violet-800"><?php echo e($customerContext['artwork_pending_count']); ?></dd>
                </div>
            <?php endif; ?>
            <div class="col-span-2">
                <dt class="text-xs text-slate-500"><?php echo e(__('Contact')); ?></dt>
                <dd class="text-slate-800"><?php echo e($customerContext['phone'] ?? '—'); ?> · <?php echo e($customerContext['email'] ?? '—'); ?></dd>
            </div>
            <?php if($customerContext['last_order'] ?? null): ?>
                <div class="col-span-2">
                    <dt class="text-xs text-slate-500"><?php echo e(__('Last order')); ?></dt>
                    <dd class="text-slate-900">
                        <?php echo e($customerContext['last_order']['product'] ?? $customerContext['last_order']['order_number']); ?>

                        <span class="text-xs text-slate-500">· <?php echo e($customerContext['last_order']['order_date'] ?? ''); ?></span>
                    </dd>
                </div>
            <?php endif; ?>
        </dl>

        <?php if(count($customerContext['frequent_products'] ?? []) > 0): ?>
            <div class="mt-3 border-t border-erp-border pt-3">
                <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e(__('Often ordered')); ?></p>
                <ul class="space-y-1 text-xs text-slate-700">
                    <?php $__currentLoopData = $customerContext['frequent_products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($product['item_name']); ?> <span class="text-slate-400">×<?php echo e($product['order_count']); ?></span></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if(count($customerContext['recent_orders'] ?? []) > 0): ?>
            <div class="mt-3 border-t border-erp-border pt-3">
                <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e(__('Recent orders')); ?></p>
                <ul class="space-y-1 text-xs">
                    <?php $__currentLoopData = $customerContext['recent_orders']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <a href="<?php echo e($recent['desk_url']); ?>" class="text-erp-primary hover:underline" data-turbo-frame="_top"><?php echo e($recent['order_number']); ?></a>
                            <span class="text-slate-500"><?php echo e($recent['total_amount']); ?></span>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Sales\SalesOrder::class)): ?>
                                <form
                                    method="POST"
                                    action="<?php echo e($recent['repeat_url']); ?>"
                                    class="inline"
                                    onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Create a repeat order from :number?', ['number' => $recent['order_number']]))->toHtml() ?>)"
                                >
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="text-[10px] font-semibold uppercase tracking-wide text-erp-accent hover:underline"><?php echo e(__('Quote again')); ?></button>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if(count($customerContext['open_quotations'] ?? []) > 0): ?>
            <div class="mt-3 border-t border-erp-border pt-3">
                <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e(__('Open quotes')); ?></p>
                <ul class="space-y-1 text-xs">
                    <?php $__currentLoopData = $customerContext['open_quotations']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <a href="<?php echo e($quote['create_url']); ?>" class="text-erp-primary hover:underline" data-erp-modal-open><?php echo e($quote['quotation_number']); ?></a>
                            <span class="text-slate-500"> · <?php echo e($quote['status']); ?></span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if(count($customerContext['timeline'] ?? []) > 0): ?>
            <div class="mt-3 border-t border-erp-border pt-3">
                <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e(__('Recent activity')); ?></p>
                <ul class="space-y-2 text-xs">
                    <?php $__currentLoopData = $customerContext['timeline']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <?php if($event['url'] ?? null): ?>
                                <a href="<?php echo e($event['url']); ?>" class="font-medium text-erp-primary hover:underline" <?php if(str_contains($event['url'], 'from=sales-desk')): ?> data-erp-modal-open <?php else: ?> data-turbo-frame="_top" <?php endif; ?>><?php echo e($event['title']); ?></a>
                            <?php else: ?>
                                <p class="font-medium text-slate-900"><?php echo e($event['title']); ?></p>
                            <?php endif; ?>
                            <?php if($event['description'] ?? null): ?>
                                <p class="text-slate-600"><?php echo e($event['description']); ?></p>
                            <?php endif; ?>
                            <p class="text-slate-400"><?php echo e($event['at']); ?></p>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
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
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\desk\partials\customer-context.blade.php ENDPATH**/ ?>