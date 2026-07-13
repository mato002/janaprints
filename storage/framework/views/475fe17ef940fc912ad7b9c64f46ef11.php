<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('SMS Center'),'breadcrumbs' => [['label' => __('Communications'), 'url' => route('admin.workspaces.communications')], ['label' => __('SMS Center')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php echo $__env->make('admin.communications.sms.partials.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('SMS Center'),'description' => __('Credits, delivery health, and what needs attention — campaigns and queue live on their own pages.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('SMS Center')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Credits, delivery health, and what needs attention — campaigns and queue live on their own pages.'))]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Communications\SmsCampaign::class)): ?>
                <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['variant' => 'primary','href' => route('admin.communications.sms.campaigns.create'),'dataTurboFrame' => 'erp-main']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.communications.sms.campaigns.create')),'data-turbo-frame' => 'erp-main']); ?><?php echo e(__('Send SMS')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
            <?php endif; ?>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $attributes = $__attributesOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__attributesOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $component = $__componentOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__componentOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>

    <?php if(session('status')): ?>
        <?php if (isset($component)) { $__componentOriginald888329b8246e32afd68d2decbd25cf1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald888329b8246e32afd68d2decbd25cf1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.alert','data' => ['variant' => 'success','class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'success','class' => 'mb-4']); ?><?php echo e(session('status')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald888329b8246e32afd68d2decbd25cf1)): ?>
<?php $attributes = $__attributesOriginald888329b8246e32afd68d2decbd25cf1; ?>
<?php unset($__attributesOriginald888329b8246e32afd68d2decbd25cf1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald888329b8246e32afd68d2decbd25cf1)): ?>
<?php $component = $__componentOriginald888329b8246e32afd68d2decbd25cf1; ?>
<?php unset($__componentOriginald888329b8246e32afd68d2decbd25cf1); ?>
<?php endif; ?>
    <?php endif; ?>

    <div class="mb-4 grid gap-4 xl:grid-cols-12">
        
        <div id="sms-topup" class="erp-card xl:col-span-5 <?php echo e($stats['low_credit'] ? 'ring-1 ring-amber-300' : ''); ?>">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Credits remaining')); ?></p>
                    <p class="mt-1 text-3xl font-semibold tabular-nums text-erp-primary"><?php echo e(number_format($stats['credits_remaining'], 0)); ?></p>
                    <p class="mt-1 text-xs text-slate-500">
                        <?php echo e(__('≈ :count segments left', ['count' => number_format($stats['approx_messages_left'])])); ?>

                        · <?php echo e(__('Cost')); ?> <?php echo e(number_format($stats['cost_per_sms'], 2)); ?> <?php echo e($stats['credit_currency'] ?? 'KES'); ?>/<?php echo e(__('segment')); ?>

                        · <?php echo e(($stats['credit_source'] ?? 'local') === 'crm' ? __('CRM wallet') : __('Local ledger')); ?>

                    </p>
                </div>
                <a
                    href="<?php echo e(route('admin.communications.sms.credits.index')); ?>"
                    data-turbo-frame="erp-main"
                    class="text-xs font-medium text-erp-accent hover:underline"
                ><?php echo e(__('Full ledger')); ?></a>
            </div>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('audit', App\Models\Communications\SmsCampaign::class)): ?>
                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-erp-border pt-4">
                    <button type="button" class="erp-btn-primary" onclick="window.dispatchEvent(new CustomEvent('open-sms-crm-topup'))"><?php echo e(__('Top up with M-Pesa')); ?></button>
                    <p class="text-xs text-slate-500"><?php echo e(__('Pays Pradytec CRM — credits appear after you approve the STK prompt.')); ?></p>
                </div>
            <?php else: ?>
                <p class="mt-4 border-t border-erp-border pt-4 text-xs text-slate-500">
                    <?php echo e(__('Ask an admin with SMS audit permission to top up credits.')); ?>

                </p>
            <?php endif; ?>
        </div>

        
        <div class="grid gap-4 sm:grid-cols-2 xl:col-span-7 xl:grid-cols-2">
            <div class="erp-card">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Sent today')); ?></p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-erp-primary"><?php echo e(number_format($stats['sent_today'])); ?></p>
                <p class="mt-1 text-xs text-slate-500"><?php echo e(__('This month')); ?>: <?php echo e(number_format($stats['sent_month'])); ?></p>
            </div>
            <div class="erp-card">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Delivery success')); ?></p>
                <p class="mt-1 text-2xl font-semibold tabular-nums <?php echo e(($stats['delivery_success_rate'] ?? 100) >= 90 ? 'text-emerald-700' : 'text-amber-700'); ?>">
                    <?php echo e($stats['delivery_success_rate'] === null ? '—' : $stats['delivery_success_rate'].'%'); ?>

                </p>
                <p class="mt-1 text-xs text-slate-500">
                    <?php echo e(__('Queue')); ?>: <?php echo e(number_format($stats['queued_messages'] + $stats['processing_messages'])); ?>

                    · <?php echo e(__('Failed')); ?>: <?php echo e(number_format($stats['failed_messages'])); ?>

                </p>
            </div>
            <div class="erp-card sm:col-span-2">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="erp-card-title"><?php echo e(__('Needs attention')); ?></h2>
                    <?php if($stats['attention'] === []): ?>
                        <span class="text-xs font-medium text-emerald-700"><?php echo e(__('All clear')); ?></span>
                    <?php endif; ?>
                </div>
                <?php if($stats['attention'] === []): ?>
                    <p class="mt-2 text-sm text-slate-500"><?php echo e(__('No credit, queue, or provider issues right now.')); ?></p>
                <?php else: ?>
                    <ul class="mt-3 space-y-2">
                        <?php $__currentLoopData = $stats['attention']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'rounded-lg border px-3 py-2',
                                'border-red-200 bg-red-50' => ($item['tone'] ?? '') === 'danger',
                                'border-amber-200 bg-amber-50' => ($item['tone'] ?? '') !== 'danger',
                            ]); ?>">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-semibold text-erp-primary"><?php echo e($item['title']); ?></p>
                                        <p class="mt-0.5 text-xs text-slate-600"><?php echo e($item['detail']); ?></p>
                                    </div>
                                    <?php if(! empty($item['action_url'])): ?>
                                        <a href="<?php echo e($item['action_url']); ?>" data-turbo-frame="erp-main" class="shrink-0 text-xs font-semibold text-erp-accent hover:underline"><?php echo e($item['action_label']); ?></a>
                                    <?php elseif(($item['action_anchor'] ?? null) === 'sms-topup'): ?>
                                        <button type="button" class="shrink-0 text-xs font-semibold text-erp-accent hover:underline" onclick="window.dispatchEvent(new CustomEvent('open-sms-crm-topup'))"><?php echo e($item['action_label']); ?></button>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="mb-4 grid gap-4 lg:grid-cols-3">
        <a href="<?php echo e(route('admin.communications.sms.campaigns.index')); ?>" data-turbo-frame="erp-main" class="erp-card transition hover:border-erp-accent/40">
            <p class="text-sm font-semibold text-erp-primary"><?php echo e(__('Campaigns')); ?></p>
            <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Create, schedule, and track bulk sends.')); ?></p>
        </a>
        <a href="<?php echo e(route('admin.communications.sms.queues.index')); ?>" data-turbo-frame="erp-main" class="erp-card transition hover:border-erp-accent/40">
            <p class="text-sm font-semibold text-erp-primary"><?php echo e(__('Message queue')); ?></p>
            <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Queued, processing, sent, and failed messages.')); ?></p>
        </a>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('audit', App\Models\Communications\SmsCampaign::class)): ?>
            <a href="<?php echo e(route('admin.communications.sms.provider-logs.index')); ?>" data-turbo-frame="erp-main" class="erp-card transition hover:border-erp-accent/40">
                <p class="text-sm font-semibold text-erp-primary"><?php echo e(__('Provider logs')); ?></p>
                <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Request/response audit for the SMS gateway.')); ?></p>
            </a>
        <?php else: ?>
            <a href="<?php echo e(route('admin.communications.sms.credits.index')); ?>" data-turbo-frame="erp-main" class="erp-card transition hover:border-erp-accent/40">
                <p class="text-sm font-semibold text-erp-primary"><?php echo e(__('Credit ledger')); ?></p>
                <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Purchases, usage, and running balance.')); ?></p>
            </a>
        <?php endif; ?>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="erp-card">
            <h2 class="erp-card-title"><?php echo e(__('Daily usage (14 days)')); ?></h2>
            <ul class="mt-2 max-h-48 space-y-1 overflow-y-auto text-xs">
                <?php $__empty_1 = true; $__currentLoopData = $stats['daily_usage']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day => $total): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li class="flex justify-between gap-3">
                        <span class="text-slate-500"><?php echo e($day); ?></span>
                        <span class="font-semibold tabular-nums text-erp-primary"><?php echo e($total); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="text-slate-500"><?php echo e(__('No sends yet.')); ?></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="erp-card">
            <div class="flex items-center justify-between gap-2">
                <h2 class="erp-card-title"><?php echo e(__('Recent credit activity')); ?></h2>
                <a href="<?php echo e(route('admin.communications.sms.credits.index')); ?>" data-turbo-frame="erp-main" class="text-xs font-medium text-erp-accent hover:underline"><?php echo e(__('Ledger')); ?></a>
            </div>
            <ul class="mt-2 max-h-48 space-y-2 overflow-y-auto text-xs">
                <?php $__empty_1 = true; $__currentLoopData = $stats['recent_transactions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li class="flex items-start justify-between gap-3 border-b border-erp-border/70 pb-2 last:border-0 last:pb-0">
                        <div>
                            <p class="font-medium text-erp-primary"><?php echo e($tx->transaction_type->label()); ?></p>
                            <p class="text-slate-500">
                                <?php echo e($tx->created_at?->format('d M Y H:i')); ?>

                                <?php if($tx->campaign): ?>
                                    · <?php echo e($tx->campaign->name); ?>

                                <?php endif; ?>
                            </p>
                        </div>
                        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'font-semibold tabular-nums',
                            'text-red-700' => $tx->amount < 0,
                            'text-emerald-700' => $tx->amount >= 0,
                        ]); ?>"><?php echo e(number_format($tx->amount, 0)); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="text-slate-500"><?php echo e(__('No credit movements yet. Top up above to get started.')); ?></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <?php echo $__env->make('admin.communications.sms.partials.topup-modal', ['topupConfig' => $topupConfig ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/communications/sms/dashboard.blade.php ENDPATH**/ ?>