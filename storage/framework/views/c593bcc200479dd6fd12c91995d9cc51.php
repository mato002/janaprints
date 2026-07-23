<?php
    $presentation = $dispatchPresentation ?? $tabData['dispatch_presentation'] ?? [];
    $summary = $presentation['summary'] ?? [];
    $timeline = $presentation['timeline'] ?? [];
    $actions = $presentation['actions'] ?? ['primary' => null, 'secondary' => [], 'danger' => []];
    $courierIcon = $presentation['courier_icon'] ?? '🚚';
    $history = $tabData['delivery_history'] ?? collect();
    $invoiceStatus = $tabData['invoice_status'] ?? ['label' => '—', 'state' => 'na'];
?>

<div class="mb-6">
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'job-360-dispatch-summary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'job-360-dispatch-summary']); ?>
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-erp-border pb-4">
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Dispatch summary')); ?></p>
                <div class="mt-1 flex flex-wrap items-center gap-2">
                    <span class="text-2xl" aria-hidden="true"><?php echo e($courierIcon); ?></span>
                    <h3 class="text-lg font-semibold text-slate-900"><?php echo e($summary['delivery_note_number'] ?? '—'); ?></h3>
                    <?php if (isset($component)) { $__componentOriginal6b1e38fda99422b6943f33aba545ca7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.enum-status-badge','data' => ['status' => $summary['status'] ?? 'draft']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.enum-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['status'] ?? 'draft')]); ?>
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
                </div>
                <p class="mt-1 text-sm text-slate-600"><?php echo e($presentation['next_action'] ?? ''); ?></p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <?php if($actions['primary'] ?? null): ?>
                    <a
                        href="<?php echo e($actions['primary']['url']); ?>"
                        class="erp-btn-primary text-sm"
                        data-turbo-frame="erp-main"
                    ><?php echo e($actions['primary']['label']); ?></a>
                <?php endif; ?>
                <?php $__currentLoopData = $actions['secondary'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a
                        href="<?php echo e($action['url']); ?>"
                        class="erp-btn-secondary text-sm"
                        <?php if(($action['target'] ?? null) === '_blank'): ?> target="_blank" rel="noopener" <?php else: ?> data-turbo-frame="erp-main" <?php endif; ?>
                    ><?php echo e($action['label']); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php $__currentLoopData = $actions['danger'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a
                        href="<?php echo e($action['url']); ?>"
                        class="text-sm font-medium text-red-600 hover:underline"
                        data-turbo-frame="erp-main"
                    ><?php echo e($action['label']); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <dt class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Dispatch status')); ?></dt>
                <dd class="mt-0.5 font-medium text-slate-900"><?php echo e($summary['status_label'] ?? '—'); ?></dd>
            </div>
            <div>
                <dt class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Dispatch date')); ?></dt>
                <dd class="mt-0.5 font-medium text-slate-900"><?php echo e($summary['dispatch_date'] ?? '—'); ?></dd>
            </div>
            <div>
                <dt class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Courier')); ?></dt>
                <dd class="mt-0.5 font-medium text-slate-900"><?php echo e($summary['courier'] ?? '—'); ?></dd>
            </div>
            <div>
                <dt class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Driver')); ?></dt>
                <dd class="mt-0.5 font-medium text-slate-900"><?php echo e($summary['driver'] ?? __('Not assigned')); ?></dd>
            </div>
            <div>
                <dt class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Tracking number')); ?></dt>
                <dd class="mt-0.5 font-mono text-sm font-medium text-slate-900">
                    <?php if(! empty($summary['track_url'])): ?>
                        <a href="<?php echo e($summary['track_url']); ?>" class="text-indigo-600 hover:underline" target="_blank" rel="noopener"><?php echo e($summary['tracking_number']); ?></a>
                    <?php else: ?>
                        <?php echo e($summary['tracking_number'] ?? '—'); ?>

                    <?php endif; ?>
                </dd>
            </div>
            <div>
                <dt class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Recipient')); ?></dt>
                <dd class="mt-0.5 font-medium text-slate-900">
                    <?php echo e($summary['recipient_name'] ?? '—'); ?>

                    <?php if(! empty($summary['recipient_phone'])): ?>
                        <span class="block text-xs text-slate-500"><?php echo e($summary['recipient_phone']); ?></span>
                    <?php endif; ?>
                </dd>
            </div>
            <div>
                <dt class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Package count')); ?></dt>
                <dd class="mt-0.5 font-medium tabular-nums text-slate-900"><?php echo e($summary['package_count'] ?? '—'); ?></dd>
            </div>
            <div>
                <dt class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Delivery date')); ?></dt>
                <dd class="mt-0.5 font-medium text-slate-900"><?php echo e($summary['delivery_date'] ?? '—'); ?></dd>
            </div>
        </dl>

        <?php if(! empty($summary['delivery_address'])): ?>
            <p class="mt-4 text-sm text-slate-600">
                <span class="font-medium text-slate-800"><?php echo e(__('Delivery address')); ?>:</span>
                <?php echo e($summary['delivery_address']); ?>

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
</div>

<nav class="job-360-stage-timeline mb-6" aria-label="<?php echo e(__('Dispatch timeline')); ?>">
    <ol class="job-360-stage-timeline__track">
        <?php $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'job-360-stage-timeline__step',
                'job-360-stage-timeline__step--'.match ($step['state']) {
                    'completed' => 'completed',
                    'current' => 'current',
                    default => 'future',
                },
            ]); ?>">
                <span class="job-360-stage-timeline__dot" aria-hidden="true"></span>
                <span class="job-360-stage-timeline__label"><?php echo e($step['label']); ?></span>
                <?php if(! empty($step['at'])): ?>
                    <span class="block text-[10px] text-slate-500"><?php echo e($step['at']); ?></span>
                <?php endif; ?>
                <?php if (! ($loop->last)): ?>
                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'job-360-stage-timeline__connector',
                        'job-360-stage-timeline__connector--'.($step['state'] === 'completed' ? 'completed' : 'future'),
                    ]); ?>" aria-hidden="true"></span>
                <?php endif; ?>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>
</nav>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
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
        <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
            <span class="font-semibold text-erp-primary"><?php echo e(__('Invoice status')); ?></span>
            <span class="erp-badge"><?php echo e($invoiceStatus['label'] ?? '—'); ?></span>
            <?php if(! empty($invoiceStatus['invoice'])): ?>
                <a href="<?php echo e(route('admin.accounting.invoices.show', $invoiceStatus['invoice'])); ?>" class="font-mono text-indigo-600"><?php echo e($invoiceStatus['invoice']->invoice_number); ?></a>
            <?php endif; ?>
        </div>
        <p class="mt-3 text-sm text-slate-600"><?php echo e(__('Billing and proof of delivery are managed on the delivery note.')); ?></p>
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
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Delivery history')); ?></h3>
        <ul class="divide-y divide-slate-100 text-sm">
            <?php $__empty_1 = true; $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li class="flex justify-between py-2">
                    <a href="<?php echo e(route('admin.dispatch.delivery-notes.show', $note)); ?>" class="font-mono text-indigo-600"><?php echo e($note->delivery_note_number); ?></a>
                    <?php if (isset($component)) { $__componentOriginal6b1e38fda99422b6943f33aba545ca7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.enum-status-badge','data' => ['status' => $note->status->value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.enum-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($note->status->value)]); ?>
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
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li class="py-4 text-slate-500"><?php echo e(__('No delivery notes for this job.')); ?></li>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\partials\dispatch-summary-dashboard.blade.php ENDPATH**/ ?>