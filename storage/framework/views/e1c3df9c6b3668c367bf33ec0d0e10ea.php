<?php
    $widgets = $insights['widgets'] ?? [];
?>

<?php if($section === 'general-ledger'): ?>
    <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
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
            <h3 class="text-sm font-medium text-erp-primary mb-2"><?php echo e(__('Period Status')); ?></h3>
            <?php $period = $widgets['period_status'] ?? []; ?>
            <dl class="grid grid-cols-2 gap-2 text-sm">
                <dt class="text-slate-500"><?php echo e(__('Period')); ?></dt>
                <dd class="font-mono"><?php echo e($period['code'] ?? '—'); ?> — <?php echo e($period['name'] ?? ''); ?></dd>
                <dt class="text-slate-500"><?php echo e(__('Status')); ?></dt>
                <dd><?php echo e($period['status_label'] ?? __('Open')); ?></dd>
                <dt class="text-slate-500"><?php echo e(__('Posting')); ?></dt>
                <dd><?php echo e(! empty($period['can_post']) ? __('Allowed') : __('Restricted')); ?></dd>
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
            <h3 class="text-sm font-medium text-erp-primary mb-2"><?php echo e(__('Recent Journals')); ?></h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase text-slate-400 border-b border-erp-border">
                        <th class="py-1"><?php echo e(__('Journal')); ?></th>
                        <th><?php echo e(__('Date')); ?></th>
                        <th><?php echo e(__('Status')); ?></th>
                        <th class="text-right"><?php echo e(__('Amount')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $widgets['recent_journals'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $journal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-t border-erp-border">
                            <td class="py-2">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Accounting\Journal::class)): ?>
                                    <a href="<?php echo e(route('admin.accounting.journals.show', $journal['id'])); ?>" class="text-erp-accent font-mono text-xs"><?php echo e($journal['journal_number']); ?></a>
                                <?php else: ?>
                                    <span class="font-mono text-xs"><?php echo e($journal['journal_number']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($journal['journal_date']); ?></td>
                            <td><?php echo e($journal['status_label']); ?></td>
                            <td class="text-right"><?php echo e(number_format($journal['amount'], 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="py-4 text-slate-500"><?php echo e(__('No journals yet.')); ?></td></tr>
                    <?php endif; ?>
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
    </div>
<?php endif; ?>

<?php if($section === 'receivables'): ?>
    <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
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
            <h3 class="text-sm font-medium text-erp-primary mb-2"><?php echo e(__('Recent Invoices')); ?></h3>
            <ul class="divide-y divide-erp-border text-sm">
                <?php $__empty_1 = true; $__currentLoopData = $widgets['recent_invoices'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li class="py-2 flex justify-between gap-2">
                        <div>
                            <a href="<?php echo e($row['route']); ?>" class="text-erp-accent font-medium"><?php echo e($row['label']); ?></a>
                            <p class="text-[11px] text-slate-500"><?php echo e($row['customer']); ?></p>
                        </div>
                        <div class="text-right shrink-0">
                            <div><?php echo e(number_format($row['amount'], 2)); ?></div>
                            <div class="text-[11px] text-slate-400"><?php echo e($row['date']); ?></div>
                        </div>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="py-4 text-slate-500"><?php echo e(__('No invoices yet.')); ?></li>
                <?php endif; ?>
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
            <h3 class="text-sm font-medium text-erp-primary mb-2"><?php echo e(__('Recent Payments')); ?></h3>
            <ul class="divide-y divide-erp-border text-sm">
                <?php $__empty_1 = true; $__currentLoopData = $widgets['recent_payments'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li class="py-2 flex justify-between gap-2">
                        <div>
                            <a href="<?php echo e($row['route']); ?>" class="text-erp-accent font-medium"><?php echo e($row['label']); ?></a>
                            <p class="text-[11px] text-slate-500"><?php echo e($row['customer']); ?></p>
                        </div>
                        <div class="text-right shrink-0">
                            <div><?php echo e(number_format($row['amount'], 2)); ?></div>
                            <div class="text-[11px] text-slate-400"><?php echo e($row['date']); ?></div>
                        </div>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="py-4 text-slate-500"><?php echo e(__('No payments yet.')); ?></li>
                <?php endif; ?>
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
    </div>
<?php endif; ?>

<?php if($section === 'payables'): ?>
    <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
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
            <h3 class="text-sm font-medium text-erp-primary mb-2"><?php echo e(__('Upcoming Supplier Payments')); ?></h3>
            <ul class="divide-y divide-erp-border text-sm">
                <?php $__empty_1 = true; $__currentLoopData = $widgets['upcoming_payments'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li class="py-2 flex justify-between gap-2">
                        <a href="<?php echo e($row['route']); ?>" class="text-erp-accent font-medium"><?php echo e($row['label']); ?></a>
                        <div class="text-right shrink-0">
                            <div><?php echo e(number_format($row['amount'], 2)); ?></div>
                            <div class="text-[11px] text-slate-400"><?php echo e($row['date']); ?></div>
                        </div>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="py-4 text-slate-500"><?php echo e(__('No draft payments scheduled.')); ?></li>
                <?php endif; ?>
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
            <h3 class="text-sm font-medium text-erp-primary mb-2"><?php echo e(__('Recent Supplier Transactions')); ?></h3>
            <ul class="divide-y divide-erp-border text-sm">
                <?php $__empty_1 = true; $__currentLoopData = $widgets['recent_transactions'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li class="py-2 flex justify-between gap-2">
                        <div>
                            <span class="text-[11px] uppercase text-slate-400"><?php echo e($row['type']); ?></span>
                            <a href="<?php echo e($row['route']); ?>" class="block text-erp-accent font-medium"><?php echo e($row['label']); ?></a>
                        </div>
                        <div class="text-right shrink-0">
                            <div><?php echo e(number_format($row['amount'], 2)); ?></div>
                            <div class="text-[11px] text-slate-400"><?php echo e($row['date']); ?></div>
                        </div>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="py-4 text-slate-500"><?php echo e(__('No supplier activity yet.')); ?></li>
                <?php endif; ?>
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
    </div>
<?php endif; ?>

<?php if($section === 'tax'): ?>
    <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
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
            <h3 class="text-sm font-medium text-erp-primary mb-2"><?php echo e(__('Recent Tax Activity')); ?></h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase text-slate-400 border-b border-erp-border">
                        <th class="py-1"><?php echo e(__('Document')); ?></th>
                        <th><?php echo e(__('Date')); ?></th>
                        <th><?php echo e(__('Direction')); ?></th>
                        <th class="text-right"><?php echo e(__('Tax')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $widgets['recent_activity'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-t border-erp-border">
                            <td class="py-2 font-mono text-xs"><?php echo e($row['label']); ?></td>
                            <td><?php echo e($row['date']); ?></td>
                            <td><?php echo e($row['direction']); ?></td>
                            <td class="text-right"><?php echo e(number_format($row['amount'], 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="py-4 text-slate-500"><?php echo e(__('No tax transactions yet.')); ?></td></tr>
                    <?php endif; ?>
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
            <h3 class="text-sm font-medium text-erp-primary mb-2"><?php echo e(__('Upcoming Filing Deadlines')); ?></h3>
            <ul class="divide-y divide-erp-border text-sm">
                <?php $__empty_1 = true; $__currentLoopData = $widgets['upcoming_filings'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li class="py-2 flex justify-between gap-2">
                        <div>
                            <a href="<?php echo e($row['route']); ?>" class="text-erp-accent font-medium"><?php echo e($row['label']); ?></a>
                            <p class="text-[11px] text-slate-500"><?php echo e($row['status']); ?></p>
                        </div>
                        <div class="text-right shrink-0 font-mono"><?php echo e(number_format($row['amount'], 2)); ?></div>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="py-4 text-slate-500"><?php echo e(__('No draft returns pending.')); ?></li>
                <?php endif; ?>
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
    </div>
<?php endif; ?>

<?php if($section === 'setup'): ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
        <h3 class="text-sm font-medium text-erp-primary mb-2"><?php echo e(__('System Configuration Overview')); ?></h3>
        <?php $chart = $widgets['chart_status'] ?? []; ?>
        <dl class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-3">
            <div>
                <dt class="text-slate-500"><?php echo e(__('Chart of Accounts')); ?></dt>
                <dd><?php echo e(($chart['active'] ?? 0).' / '.($chart['total'] ?? 0).' '.__('active')); ?></dd>
            </div>
            <div>
                <dt class="text-slate-500"><?php echo e(__('Posting Templates')); ?></dt>
                <dd><?php echo e($chart['templates'] ?? 0); ?></dd>
            </div>
            <div>
                <dt class="text-slate-500"><?php echo e(__('Active Posting Rules')); ?></dt>
                <dd><?php echo e($widgets['posting_rule_count'] ?? 0); ?></dd>
            </div>
        </dl>
        <?php if(! empty($widgets['period'])): ?>
            <p class="mt-3 text-[11px] text-slate-500">
                <?php echo e(__('Current period')); ?>:
                <a href="<?php echo e($widgets['period']['route']); ?>" class="text-erp-accent"><?php echo e($widgets['period']['code']); ?> — <?php echo e($widgets['period']['name']); ?></a>
                (<?php echo e($widgets['period']['status']); ?>)
            </p>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\accounting\workspaces\partials\section-widgets.blade.php ENDPATH**/ ?>