<?php
    use App\Enums\SalesOrderStatus;
    use App\Models\Sales\CustomerInvoice;

    $canConfirm = ($workflow['can_confirm'] ?? false) && auth()->user()?->can('confirm', $salesOrder);
    $canRelease = ($workflow['can_release'] ?? false) && auth()->user()?->can('production', $salesOrder);
    $canClose = ($workflow['can_close'] ?? false) && auth()->user()?->can('close', $salesOrder);
    $canTransition = auth()->user()?->can('transition', $salesOrder);
    $canInvoice = auth()->user()?->can('create', CustomerInvoice::class)
        && $salesOrder->remainingInvoiceTotal() > 0
        && ! in_array($salesOrder->status, [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled], true);
    $canUpdate = auth()->user()?->can('update', $salesOrder);

    $onHold = $salesOrder->status === SalesOrderStatus::OnHold;
    $canHold = $canTransition && $salesOrder->status->canTransitionTo(SalesOrderStatus::OnHold);
    $canCancel = $canTransition && $salesOrder->status->canTransitionTo(SalesOrderStatus::Cancelled);

    $primary = null;
    if ($canConfirm) {
        $primary = ['type' => 'form', 'action' => route('admin.sales-orders.confirm', $salesOrder), 'label' => __('Confirm order'), 'method' => 'POST'];
    } elseif ($canRelease) {
        $primary = ['type' => 'form', 'action' => route('admin.sales-orders.release-to-production', $salesOrder), 'label' => __('Send to production'), 'method' => 'POST'];
    } elseif ($onHold && $canTransition) {
        $primary = ['type' => 'form', 'action' => route('admin.sales-orders.resume', $salesOrder), 'label' => __('Resume'), 'method' => 'POST'];
    } elseif ($canInvoice) {
        $primary = ['type' => 'link', 'url' => route('admin.invoices.from-sales-order', $salesOrder), 'label' => __('Generate invoice'), 'modal' => true];
    } elseif ($canClose) {
        $primary = ['type' => 'form', 'action' => route('admin.sales-orders.close', $salesOrder), 'label' => __('Close order'), 'method' => 'POST'];
    }

    $currentStage = collect($workflow['pipeline'] ?? [])->firstWhere('state', 'current')
        ?? collect($workflow['pipeline'] ?? [])->firstWhere('state', 'paused');
    $currentStageLabel = $currentStage['label'] ?? $statusLabel;
    $stageTone = match (true) {
        $salesOrder->status === SalesOrderStatus::Cancelled => 'danger',
        $salesOrder->status === SalesOrderStatus::OnHold => 'warning',
        $salesOrder->status === SalesOrderStatus::Closed => 'success',
        $salesOrder->status === SalesOrderStatus::Delivered => 'success',
        default => 'info',
    };
?>

<header class="so-360__header">
    <div class="so-360__header-main">
        <div class="so-360__identity">
            <p class="so-360__eyebrow">
                <span><?php echo e(__('Sales order')); ?></span>
                <?php if($salesOrder->branch?->name): ?>
                    <span class="so-360__dot" aria-hidden="true">·</span>
                    <span><?php echo e($salesOrder->branch->name); ?></span>
                <?php endif; ?>
            </p>
            <h1 class="so-360__title font-mono"><?php echo e($salesOrder->order_number); ?></h1>
            <p class="so-360__subtitle">
                <?php if($salesOrder->customer): ?>
                    <a href="<?php echo e(route('admin.crm.customers.show', $salesOrder->customer)); ?>" class="so-360__link" data-turbo-frame="erp-main">
                        <?php echo e($salesOrder->customer->company_name); ?>

                    </a>
                <?php else: ?>
                    <?php echo e(__('No customer')); ?>

                <?php endif; ?>
            </p>

            <?php if($salesOrder->jobCard): ?>
                <p class="so-360__linked-doc mt-1.5 text-sm text-slate-600">
                    <span class="text-slate-500"><?php echo e(__('Linked job card')); ?>:</span>
                    <a
                        href="<?php echo e(route('admin.production.job-cards.show', $salesOrder->jobCard)); ?>"
                        class="so-360__link font-mono underline decoration-erp-accent/40 underline-offset-2"
                        data-turbo-frame="erp-main"
                        data-turbo-action="advance"
                    ><?php echo e($salesOrder->jobCard->job_card_number); ?></a>
                </p>
            <?php endif; ?>

            <div class="so-360__badge-row">
                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['so-360__badge', 'so-360__badge--'.$stageTone]); ?>"><?php echo e($statusLabel); ?></span>
                <?php if($financialLabel): ?>
                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'so-360__badge',
                        'so-360__badge--success' => $financialVariant === 'success',
                        'so-360__badge--warning' => $financialVariant === 'warning',
                        'so-360__badge--neutral' => ! in_array($financialVariant, ['success', 'warning'], true),
                    ]); ?>"><?php echo e($financialLabel); ?></span>
                <?php endif; ?>
                <span class="so-360__total-chip">
                    <span class="so-360__total-label"><?php echo e(__('Total')); ?></span>
                    <span class="so-360__total-value font-mono"><?php echo e(number_format($salesOrder->total_amount, 2)); ?></span>
                </span>
            </div>
        </div>

        <div class="so-360__stage-panel">
            <p class="so-360__stage-label"><?php echo e(__('Current stage')); ?></p>
            <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['so-360__stage', 'so-360__stage--'.$stageTone]); ?>">
                <span class="so-360__stage-dot" aria-hidden="true"></span>
                <span><?php echo e($currentStageLabel); ?></span>
            </div>
            <?php if($workflow['hint'] ?? null): ?>
                <p class="so-360__hint"><?php echo e($workflow['hint']); ?></p>
            <?php endif; ?>
        </div>

        <div class="so-360__actions so-360__actions--desktop">
            <?php echo $__env->make('admin.sales.orders.workspace.partials.primary-action', ['primary' => $primary], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <?php if($canInvoice && (! $primary || ($primary['label'] ?? null) !== __('Generate invoice'))): ?>
                <a href="<?php echo e(route('admin.invoices.from-sales-order', $salesOrder)); ?>" class="erp-btn-secondary" data-erp-modal-open>
                    <?php echo e(__('Generate invoice')); ?>

                </a>
            <?php endif; ?>

            <?php if($salesOrder->jobCard): ?>
                <a href="<?php echo e(route('admin.production.job-cards.show', $salesOrder->jobCard)); ?>" class="erp-btn-secondary" data-turbo-frame="erp-main">
                    <?php echo e(__('Open job card')); ?>

                </a>
            <?php elseif($canRelease): ?>
                
            <?php endif; ?>

            <button type="button" class="erp-btn-secondary" onclick="window.print()"><?php echo e(__('Print')); ?></button>

            <?php if($canUpdate): ?>
                <a href="<?php echo e(route('admin.sales-orders.edit', $salesOrder)); ?>" class="erp-btn-secondary"><?php echo e(__('Edit')); ?></a>
            <?php endif; ?>

            <details class="so-360__more">
                <summary class="erp-btn-secondary so-360__more-summary"><?php echo e(__('More')); ?></summary>
                <div class="so-360__more-menu">
                    <?php if($canHold): ?>
                        <form method="POST" action="<?php echo e(route('admin.sales-orders.hold', $salesOrder)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="so-360__more-item"><?php echo e(__('On hold')); ?></button>
                        </form>
                    <?php endif; ?>
                    <?php if($onHold && $canTransition && (! $primary || ($primary['label'] ?? null) !== __('Resume'))): ?>
                        <form method="POST" action="<?php echo e(route('admin.sales-orders.resume', $salesOrder)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="so-360__more-item"><?php echo e(__('Resume')); ?></button>
                        </form>
                    <?php endif; ?>
                    <?php if($canClose && (! $primary || ($primary['label'] ?? null) !== __('Close order'))): ?>
                        <form method="POST" action="<?php echo e(route('admin.sales-orders.close', $salesOrder)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="so-360__more-item"><?php echo e(__('Close order')); ?></button>
                        </form>
                    <?php endif; ?>
                    <?php if($canCancel): ?>
                        <form method="POST" action="<?php echo e(route('admin.sales-orders.cancel', $salesOrder)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="so-360__more-item so-360__more-item--danger"><?php echo e(__('Cancel')); ?></button>
                        </form>
                    <?php endif; ?>
                    <?php if($salesOrder->jobCard): ?>
                        <a href="<?php echo e(route('admin.production.job-cards.show', $salesOrder->jobCard)); ?>?tab=dispatch" class="so-360__more-item" data-turbo-frame="erp-main">
                            <?php echo e(__('Delivery note')); ?>

                        </a>
                    <?php endif; ?>
                </div>
            </details>
        </div>
    </div>

    <?php if (isset($component)) { $__componentOriginalf5ffa9581a76bd6f6146407ee4444540 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf5ffa9581a76bd6f6146407ee4444540 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.workflow-error','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.workflow-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf5ffa9581a76bd6f6146407ee4444540)): ?>
<?php $attributes = $__attributesOriginalf5ffa9581a76bd6f6146407ee4444540; ?>
<?php unset($__attributesOriginalf5ffa9581a76bd6f6146407ee4444540); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf5ffa9581a76bd6f6146407ee4444540)): ?>
<?php $component = $__componentOriginalf5ffa9581a76bd6f6146407ee4444540; ?>
<?php unset($__componentOriginalf5ffa9581a76bd6f6146407ee4444540); ?>
<?php endif; ?>
</header>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\orders\workspace\header.blade.php ENDPATH**/ ?>