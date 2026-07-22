<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Email settings')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php echo $__env->make('admin.communications.email.partials.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Email accounts'),'description' => __('Company, branch, and department senders — SMTP/provider config stored per account (not sent until provider connected).')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Email accounts')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Company, branch, and department senders — SMTP/provider config stored per account (not sent until provider connected).'))]); ?>
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

    <div class="mb-4 grid gap-4 lg:grid-cols-3">
        <div class="erp-card">
            <h2 class="erp-card-title"><?php echo e(__('Delivery diagnostics')); ?></h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500"><?php echo e(__('Delivery engine')); ?></dt>
                    <dd class="<?php echo \Illuminate\Support\Arr::toCssClasses(['font-medium', 'text-emerald-700' => $diagnostics['delivery_engine']['active'], 'text-amber-700' => ! $diagnostics['delivery_engine']['active']]); ?>">
                        <?php echo e($diagnostics['delivery_engine']['label']); ?>

                    </dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500"><?php echo e(__('SMTP')); ?></dt>
                    <dd class="font-medium"><?php echo e($diagnostics['smtp']['label']); ?></dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500"><?php echo e(__('Queue')); ?> (<?php echo e($diagnostics['queue']['name']); ?>)</dt>
                    <dd class="<?php echo \Illuminate\Support\Arr::toCssClasses(['font-medium', 'text-emerald-700' => $diagnostics['queue']['active'], 'text-amber-700' => ! $diagnostics['queue']['active']]); ?>">
                        <?php echo e($diagnostics['queue']['label']); ?>

                    </dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500"><?php echo e(__('Integration')); ?></dt>
                    <dd class="font-medium"><?php echo e($diagnostics['integration']['label']); ?></dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500"><?php echo e(__('Retention policy')); ?></dt>
                    <dd class="font-medium"><?php echo e($diagnostics['retention']['label'] ?? '—'); ?></dd>
                </div>
            </dl>
        </div>

        <div class="erp-card">
            <h2 class="erp-card-title"><?php echo e(__('Queue diagnostics')); ?></h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500"><?php echo e(__('Queue depth')); ?></dt>
                    <dd class="font-medium"><?php echo e($diagnostics['queue']['depth'] ?? 0); ?></dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500"><?php echo e(__('Queued messages')); ?></dt>
                    <dd class="font-medium"><?php echo e($diagnostics['queue']['queued_count'] ?? 0); ?></dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500"><?php echo e(__('Stuck sending')); ?></dt>
                    <dd class="<?php echo \Illuminate\Support\Arr::toCssClasses(['font-medium', 'text-amber-700' => ($diagnostics['queue']['stuck_sending'] ?? 0) > 0]); ?>">
                        <?php echo e($diagnostics['queue']['stuck_sending'] ?? 0); ?>

                        <span class="text-xs text-slate-500">(<?php echo e(__('>:min min', ['min' => $diagnostics['queue']['stuck_threshold_minutes'] ?? 15])); ?>)</span>
                    </dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500"><?php echo e(__('Failed (all time)')); ?></dt>
                    <dd class="font-medium"><?php echo e($diagnostics['queue']['failed_count'] ?? 0); ?></dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500"><?php echo e(__('Cancelled (all time)')); ?></dt>
                    <dd class="font-medium"><?php echo e($diagnostics['queue']['cancelled_count'] ?? 0); ?></dd>
                </div>
            </dl>
        </div>

        <div class="erp-card">
            <h2 class="erp-card-title"><?php echo e(__('Recent failures')); ?></h2>
            <ul class="mt-3 space-y-2 text-sm">
                <?php $__empty_1 = true; $__currentLoopData = $diagnostics['recent_failures']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li class="rounded border border-erp-border px-3 py-2">
                        <p class="font-medium"><?php echo e(Str::limit($item['subject'], 50)); ?></p>
                        <p class="text-xs text-slate-500"><?php echo e($item['recipient'] ?? '—'); ?> · <?php echo e($item['failed_at'] ?? $item['created_at']); ?></p>
                        <?php if($item['failure_reason']): ?>
                            <p class="text-xs text-red-600"><?php echo e(Str::limit($item['failure_reason'], 80)); ?></p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="text-slate-500"><?php echo e(__('No recent failures.')); ?></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="mb-4 erp-card">
        <h2 class="erp-card-title"><?php echo e(__('Recent success')); ?></h2>
        <ul class="mt-3 space-y-2 text-sm">
            <?php $__empty_1 = true; $__currentLoopData = $diagnostics['recent_successes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li class="rounded border border-erp-border px-3 py-2">
                    <p class="font-medium"><?php echo e(Str::limit($item['subject'], 50)); ?></p>
                    <p class="text-xs text-slate-500"><?php echo e($item['sender'] ?? '—'); ?> → <?php echo e($item['recipient'] ?? '—'); ?> · <?php echo e($item['sent_at'] ?? $item['created_at']); ?></p>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li class="text-slate-500"><?php echo e(__('No recent deliveries.')); ?></li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="erp-card overflow-x-auto">
        <table class="erp-table w-full">
            <thead><tr><th><?php echo e(__('Name')); ?></th><th><?php echo e(__('From')); ?></th><th><?php echo e(__('Reply-To')); ?></th><th><?php echo e(__('Provider')); ?></th><th><?php echo e(__('Status')); ?></th></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($account->name); ?> <?php if($account->is_default): ?><span class="text-xs text-erp-accent">(<?php echo e(__('Default')); ?>)</span><?php endif; ?></td>
                        <td><?php echo e($account->from_email); ?></td>
                        <td><?php echo e($account->reply_to_email ?? '—'); ?></td>
                        <td><?php echo e($account->provider->label()); ?></td>
                        <td><?php echo e($account->status->label()); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="py-6 text-center text-slate-500"><?php echo e(__('No accounts — a default account is created on first send.')); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\email\settings.blade.php ENDPATH**/ ?>