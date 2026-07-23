<?php
    use App\Enums\ArtworkRequestStatus;
    use App\Enums\SalesOrderStatus;

    $artworkApproved = ($salesOrder->artworkRequest
            && $salesOrder->artworkRequest->status === ArtworkRequestStatus::Approved)
        || ($salesOrder->uses_existing_artwork && $salesOrder->customer_artwork_id);

    $invoiced = (float) $salesOrder->invoiced_total > 0;
    $paidFully = (($financial['payment']['amount_outstanding'] ?? null) !== null)
        && (float) $financial['payment']['amount_outstanding'] <= 0
        && $invoiced;
    $paidPartial = $invoiced && ! $paidFully && (float) ($financial['payment']['amount_paid'] ?? 0) > 0;

    $status = $salesOrder->status;
    $rank = match ($status) {
        SalesOrderStatus::Draft => 1,
        SalesOrderStatus::Confirmed, SalesOrderStatus::ReadyForProduction => 3,
        SalesOrderStatus::InProduction, SalesOrderStatus::Completed, SalesOrderStatus::ReadyForDispatch => 4,
        SalesOrderStatus::Delivered => 5,
        SalesOrderStatus::Closed => 8,
        SalesOrderStatus::Cancelled, SalesOrderStatus::OnHold => 0,
        default => 1,
    };

    // Presentation-only lifecycle (does not change workflow service).
    $lifecycle = [
        [
            'key' => 'quotation',
            'label' => __('Quotation'),
            'icon' => 'document-text',
            'complete' => (bool) $salesOrder->quotation_id,
            'current' => ! $salesOrder->quotation_id && ! $salesOrder->is_direct_order && $status === SalesOrderStatus::Draft,
            'url' => $salesOrder->quotation ? route('admin.quotations.show', $salesOrder->quotation) : null,
        ],
        [
            'key' => 'sales_order',
            'label' => __('Sales order'),
            'icon' => 'clipboard-list',
            'complete' => $rank >= 3 || in_array($status, [SalesOrderStatus::Confirmed, SalesOrderStatus::ReadyForProduction, SalesOrderStatus::InProduction, SalesOrderStatus::Completed, SalesOrderStatus::ReadyForDispatch, SalesOrderStatus::Delivered, SalesOrderStatus::Closed], true),
            'current' => in_array($status, [SalesOrderStatus::Draft, SalesOrderStatus::Confirmed], true) && ! $artworkApproved,
            'url' => null,
        ],
        [
            'key' => 'artwork',
            'label' => __('Artwork'),
            'icon' => 'photograph',
            'complete' => $artworkApproved,
            'current' => $status === SalesOrderStatus::Confirmed && ! $artworkApproved,
            'url' => $salesOrder->artworkRequest ? route('admin.artwork.show', $salesOrder->artworkRequest) : null,
        ],
        [
            'key' => 'production',
            'label' => __('Production'),
            'icon' => 'cog',
            'complete' => $rank >= 4 || (bool) $salesOrder->jobCard,
            'current' => in_array($status, [SalesOrderStatus::ReadyForProduction, SalesOrderStatus::InProduction, SalesOrderStatus::Completed, SalesOrderStatus::ReadyForDispatch], true),
            'url' => $salesOrder->jobCard ? route('admin.production.job-cards.show', $salesOrder->jobCard) : null,
        ],
        [
            'key' => 'delivery',
            'label' => __('Delivery'),
            'icon' => 'truck',
            'complete' => $rank >= 5 || $status === SalesOrderStatus::Delivered,
            'current' => $status === SalesOrderStatus::Delivered && ! $invoiced,
            'url' => $salesOrder->jobCard
                ? route('admin.production.job-cards.show', $salesOrder->jobCard).'?tab=dispatch'
                : null,
        ],
        [
            'key' => 'invoice',
            'label' => __('Invoice'),
            'icon' => 'receipt-tax',
            'complete' => $invoiced,
            'current' => $status === SalesOrderStatus::Delivered && ! $invoiced,
            'url' => $salesOrder->invoices->isNotEmpty()
                ? route('admin.invoices.show', $salesOrder->invoices->first())
                : (Route::has('admin.invoices.from-sales-order') ? route('admin.invoices.from-sales-order', $salesOrder) : null),
        ],
        [
            'key' => 'payment',
            'label' => __('Payment'),
            'icon' => 'cash',
            'complete' => $paidFully,
            'current' => $paidPartial || ($invoiced && ! $paidFully && $status !== SalesOrderStatus::Closed),
            'url' => $salesOrder->invoices->isNotEmpty()
                ? route('admin.invoices.show', $salesOrder->invoices->first())
                : null,
        ],
        [
            'key' => 'closed',
            'label' => __('Closed'),
            'icon' => 'badge-check',
            'complete' => $status === SalesOrderStatus::Closed,
            'current' => $status === SalesOrderStatus::Closed,
            'url' => null,
        ],
    ];

    // Ensure one current highlight when none matched.
    if (! collect($lifecycle)->contains(fn ($s) => $s['current'])) {
        foreach ($lifecycle as $i => $step) {
            if (! $step['complete']) {
                $lifecycle[$i]['current'] = true;
                break;
            }
        }
    }
?>

<section class="so-360__lifecycle" aria-label="<?php echo e(__('Order lifecycle')); ?>">
    <ol class="so-360__rail">
        <?php $__currentLoopData = $lifecycle; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $state = $step['complete'] ? 'complete' : ($step['current'] ? 'current' : 'upcoming');
                if ($status === SalesOrderStatus::Cancelled) {
                    $state = 'cancelled';
                } elseif ($status === SalesOrderStatus::OnHold && $step['complete']) {
                    $state = 'paused';
                }
                $clickable = $step['url'] && in_array($state, ['complete', 'current', 'paused'], true);
            ?>
            <li class="<?php echo \Illuminate\Support\Arr::toCssClasses(['so-360__rail-step', 'so-360__rail-step--'.$state]); ?>">
                <?php if($index > 0): ?>
                    <span class="so-360__rail-connector" aria-hidden="true"></span>
                <?php endif; ?>
                <?php if($clickable): ?>
                    <a
                        href="<?php echo e($step['url']); ?>"
                        class="so-360__rail-node"
                        <?php if(str_contains($step['url'], '/invoices/from/sales-order')): ?>
                            data-erp-modal-open
                        <?php else: ?>
                            data-turbo-frame="erp-main"
                        <?php endif; ?>
                        title="<?php echo e($step['label']); ?>"
                    >
                        <span class="so-360__rail-icon">
                            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $step['icon'],'class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($step['icon']),'class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                        </span>
                        <span class="so-360__rail-label"><?php echo e($step['label']); ?></span>
                    </a>
                <?php else: ?>
                    <div class="so-360__rail-node" title="<?php echo e($step['label']); ?>">
                        <span class="so-360__rail-icon">
                            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $step['icon'],'class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($step['icon']),'class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                        </span>
                        <span class="so-360__rail-label"><?php echo e($step['label']); ?></span>
                    </div>
                <?php endif; ?>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/orders/workspace/lifecycle-rail.blade.php ENDPATH**/ ?>