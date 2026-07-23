<?php
    $firstItem = $salesOrder->items->first();
    $itemCount = $salesOrder->items->count();
    $productLabel = $salesOrder->inventoryItem?->item_name
        ?? $firstItem?->item_name
        ?? __('No product linked');
?>


<section class="so-360__section so-360__section--primary">
    <div class="so-360__grid so-360__grid--summary">
        <article class="so-360__card so-360__card--hero">
            <h2 class="so-360__card-title"><?php echo e(__('Order summary')); ?></h2>
            <dl class="so-360__dl so-360__dl--compact">
                <div>
                    <dt><?php echo e(__('Customer')); ?></dt>
                    <dd>
                        <?php if($salesOrder->customer): ?>
                            <a href="<?php echo e(route('admin.crm.customers.show', $salesOrder->customer)); ?>" class="so-360__link" data-turbo-frame="erp-main">
                                <?php echo e($salesOrder->customer->company_name); ?>

                            </a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </dd>
                </div>
                <div>
                    <dt><?php echo e(__('Status')); ?></dt>
                    <dd class="capitalize"><?php echo e(str_replace('_', ' ', $salesOrder->status->value)); ?></dd>
                </div>
                <div>
                    <dt><?php echo e(__('Total')); ?></dt>
                    <dd class="font-mono text-base font-semibold text-slate-900"><?php echo e(number_format($salesOrder->total_amount, 2)); ?></dd>
                </div>
                <div>
                    <dt><?php echo e(__('Order date')); ?></dt>
                    <dd><?php echo e(optional($salesOrder->order_date)->format('M j, Y') ?? '—'); ?></dd>
                </div>
                <div>
                    <dt><?php echo e(__('Required date')); ?></dt>
                    <dd><?php echo e(optional($salesOrder->required_date)->format('M j, Y') ?? '—'); ?></dd>
                </div>
                <div>
                    <dt><?php echo e(__('Priority')); ?></dt>
                    <dd class="capitalize"><?php echo e($salesOrder->priority?->value ? str_replace('_', ' ', $salesOrder->priority->value) : '—'); ?></dd>
                </div>
            </dl>
        </article>

        <article class="so-360__card">
            <h2 class="so-360__card-title"><?php echo e(__('Workflow progress')); ?></h2>
            <ol class="so-360__mini-pipeline">
                <?php $__currentLoopData = $workflow['pipeline']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'so-360__mini-step',
                        'so-360__mini-step--complete' => $step['state'] === 'complete',
                        'so-360__mini-step--current' => $step['state'] === 'current',
                        'so-360__mini-step--paused' => $step['state'] === 'paused',
                        'so-360__mini-step--cancelled' => $step['state'] === 'cancelled',
                        'so-360__mini-step--upcoming' => in_array($step['state'], ['upcoming'], true),
                    ]); ?>"><?php echo e($step['label']); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ol>
            <?php if($workflow['hint'] ?? null): ?>
                <p class="mt-3 text-xs text-slate-600"><?php echo e($workflow['hint']); ?></p>
            <?php endif; ?>
        </article>
    </div>
</section>


<section class="so-360__section">
    <div class="so-360__grid so-360__grid--two">
        <article class="so-360__card">
            <div class="so-360__card-head">
                <h2 class="so-360__card-title"><?php echo e(__('Commercial summary')); ?></h2>
                <button type="button" class="so-360__text-btn" @click="setTab('commercial')"><?php echo e(__('Open')); ?></button>
            </div>
            <dl class="so-360__dl so-360__dl--compact">
                <div>
                    <dt><?php echo e(__('Line items')); ?></dt>
                    <dd><?php echo e($itemCount); ?></dd>
                </div>
                <div>
                    <dt><?php echo e(__('Subtotal')); ?></dt>
                    <dd class="font-mono"><?php echo e(number_format($salesOrder->subtotal, 2)); ?></dd>
                </div>
                <div>
                    <dt><?php echo e(__('Tax')); ?></dt>
                    <dd class="font-mono"><?php echo e(number_format($salesOrder->tax_amount, 2)); ?></dd>
                </div>
                <div>
                    <dt><?php echo e(__('Quotation')); ?></dt>
                    <dd>
                        <?php if($salesOrder->quotation): ?>
                            <a href="<?php echo e(route('admin.quotations.show', $salesOrder->quotation)); ?>" class="so-360__link" data-turbo-frame="erp-main">
                                <?php echo e($salesOrder->quotation->quotation_number); ?>

                            </a>
                        <?php else: ?>
                            <?php echo e($salesOrder->is_direct_order ? __('Direct order') : '—'); ?>

                        <?php endif; ?>
                    </dd>
                </div>
            </dl>
        </article>

        <article class="so-360__card">
            <div class="so-360__card-head">
                <h2 class="so-360__card-title"><?php echo e(__('Production summary')); ?></h2>
                <button type="button" class="so-360__text-btn" @click="setTab('production')"><?php echo e(__('Open')); ?></button>
            </div>
            <dl class="so-360__dl so-360__dl--compact">
                <div>
                    <dt><?php echo e(__('Product')); ?></dt>
                    <dd><?php echo e($productLabel); ?></dd>
                </div>
                <div>
                    <dt><?php echo e(__('Artwork')); ?></dt>
                    <dd>
                        <?php if($salesOrder->artworkRequest): ?>
                            <a href="<?php echo e(route('admin.artwork.show', $salesOrder->artworkRequest)); ?>" class="so-360__link" data-turbo-frame="erp-main">
                                <?php echo e($salesOrder->artworkRequest->request_number); ?>

                            </a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </dd>
                </div>
                <div>
                    <dt><?php echo e(__('Linked job card')); ?></dt>
                    <dd>
                        <?php if($salesOrder->jobCard): ?>
                            <a href="<?php echo e(route('admin.production.job-cards.show', $salesOrder->jobCard)); ?>" class="so-360__link font-mono" data-turbo-frame="erp-main">
                                <?php echo e($salesOrder->jobCard->job_card_number); ?>

                            </a>
                        <?php else: ?>
                            <?php echo e(__('Not created')); ?>

                        <?php endif; ?>
                    </dd>
                </div>
                <div>
                    <dt><?php echo e(__('Catalogue SKU')); ?></dt>
                    <dd class="font-mono"><?php echo e($salesOrder->inventoryItem?->sku ?? '—'); ?></dd>
                </div>
            </dl>
        </article>
    </div>
</section>


<section class="so-360__section">
    <div class="so-360__grid so-360__grid--two">
        <article class="so-360__card">
            <div class="so-360__card-head">
                <h2 class="so-360__card-title"><?php echo e(__('Financial summary')); ?></h2>
                <button type="button" class="so-360__text-btn" @click="setTab('financial')"><?php echo e(__('Details')); ?></button>
            </div>
            <div class="so-360__kpi-row">
                <div class="so-360__kpi">
                    <span class="so-360__kpi-label"><?php echo e(__('Subtotal')); ?></span>
                    <span class="so-360__kpi-value font-mono"><?php echo e(number_format($salesOrder->subtotal, 2)); ?></span>
                </div>
                <div class="so-360__kpi">
                    <span class="so-360__kpi-label"><?php echo e(__('Tax')); ?></span>
                    <span class="so-360__kpi-value font-mono"><?php echo e(number_format($salesOrder->tax_amount, 2)); ?></span>
                </div>
                <div class="so-360__kpi so-360__kpi--emphasis">
                    <span class="so-360__kpi-label"><?php echo e(__('Total')); ?></span>
                    <span class="so-360__kpi-value font-mono"><?php echo e(number_format($salesOrder->total_amount, 2)); ?></span>
                </div>
            </div>
            <?php if(! empty($profitability)): ?>
                <details class="so-360__collapse mt-3">
                    <summary><?php echo e(__('Estimated profitability')); ?></summary>
                    <dl class="so-360__dl so-360__dl--compact mt-2">
                        <div><dt><?php echo e(__('Profit')); ?></dt><dd class="font-mono"><?php echo e(number_format($profitability['estimated_profit'], 2)); ?></dd></div>
                        <div><dt><?php echo e(__('Margin')); ?></dt><dd><?php echo e(number_format($profitability['estimated_margin_percent'], 1)); ?>%</dd></div>
                        <div><dt><?php echo e(__('Material')); ?></dt><dd class="font-mono"><?php echo e(number_format($profitability['material_cost'], 2)); ?></dd></div>
                        <div><dt><?php echo e(__('Waste')); ?></dt><dd class="font-mono"><?php echo e(number_format($profitability['wastage_cost'], 2)); ?></dd></div>
                    </dl>
                </details>
            <?php endif; ?>
        </article>

        <article class="so-360__card">
            <div class="so-360__card-head">
                <h2 class="so-360__card-title"><?php echo e(__('Billing summary')); ?></h2>
                <button type="button" class="so-360__text-btn" @click="setTab('financial')"><?php echo e(__('Invoices')); ?></button>
            </div>
            <dl class="so-360__dl so-360__dl--compact">
                <div>
                    <dt><?php echo e(__('Invoiced')); ?></dt>
                    <dd class="font-mono"><?php echo e(number_format($salesOrder->invoiced_total, 2)); ?></dd>
                </div>
                <div>
                    <dt><?php echo e(__('Remaining')); ?></dt>
                    <dd class="font-mono"><?php echo e(number_format($salesOrder->remainingInvoiceTotal(), 2)); ?></dd>
                </div>
                <?php if(! empty($financial['payment'])): ?>
                    <div>
                        <dt><?php echo e(__('Paid')); ?></dt>
                        <dd class="font-mono"><?php echo e(number_format($financial['payment']['amount_paid'], 2)); ?></dd>
                    </div>
                    <div>
                        <dt><?php echo e(__('Outstanding')); ?></dt>
                        <dd class="font-mono"><?php echo e(number_format($financial['payment']['amount_outstanding'], 2)); ?></dd>
                    </div>
                <?php endif; ?>
                <?php if(! empty($financial['deposit'])): ?>
                    <div>
                        <dt><?php echo e(__('Billing type')); ?></dt>
                        <dd><?php echo e($financial['deposit']['billing_type']); ?></dd>
                    </div>
                    <div>
                        <dt><?php echo e(__('Deposit paid')); ?></dt>
                        <dd class="font-mono"><?php echo e(number_format($financial['deposit']['paid'], 2)); ?></dd>
                    </div>
                <?php endif; ?>
            </dl>
        </article>
    </div>
</section>


<details class="so-360__collapse so-360__collapse--block">
    <summary><?php echo e(__('Traceability details')); ?></summary>
    <div class="so-360__collapse-body">
        <dl class="so-360__dl so-360__dl--compact">
            <div>
                <dt><?php echo e(__('Quotation')); ?></dt>
                <dd><?php echo e($salesOrder->quotation?->quotation_number ?? '—'); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Artwork')); ?></dt>
                <dd><?php echo e($salesOrder->artworkRequest?->request_number ?? '—'); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Job card')); ?></dt>
                <dd>
                    <?php if($salesOrder->jobCard): ?>
                        <a href="<?php echo e(route('admin.production.job-cards.show', $salesOrder->jobCard)); ?>" class="so-360__link" data-turbo-frame="erp-main">
                            <?php echo e($salesOrder->jobCard->job_card_number); ?>

                        </a>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </dd>
            </div>
            <?php if($salesOrder->conversion): ?>
                <div class="sm:col-span-2">
                    <dt><?php echo e(__('Converted')); ?></dt>
                    <dd>
                        <?php echo e($salesOrder->conversion->created_at?->format('Y-m-d H:i')); ?>

                        — <?php echo e($salesOrder->conversion->converter?->name); ?>

                        (<?php echo e(__('Quotation rev')); ?> <?php echo e($salesOrder->conversion->quotation_revision_number); ?>,
                        <?php echo e(__('Artwork v')); ?><?php echo e($salesOrder->conversion->artwork_version_number); ?>)
                    </dd>
                </div>
            <?php endif; ?>
        </dl>
    </div>
</details>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/orders/workspace/tabs/overview.blade.php ENDPATH**/ ?>