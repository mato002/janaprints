<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Communications certification')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Communications Certification Report'),'description' => __('Production readiness assessment for the email communications platform. Read-only.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Communications Certification Report')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Production readiness assessment for the email communications platform. Read-only.'))]); ?>
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

    <?php
        $score = $report['readiness_score'];
        $scoreColor = $score >= 90 ? 'text-emerald-700' : 'text-red-700';
        $verdictColor = ($report['verdict'] ?? '') === 'certified' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800';
    ?>

    <div class="mb-6 grid gap-4 lg:grid-cols-3">
        <div class="erp-card lg:col-span-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Readiness score')); ?></p>
            <p class="mt-2 text-4xl font-bold <?php echo e($scoreColor); ?>"><?php echo e($score); ?>/100</p>
            <p class="mt-3 inline-flex rounded-full px-3 py-1 text-sm font-semibold <?php echo e($verdictColor); ?>">
                <?php echo e($report['verdict_label']); ?>

            </p>
            <p class="mt-3 text-sm text-slate-500">
                <?php echo e(__(':passed of :total checks passed', ['passed' => $report['checks_passed'], 'total' => $report['checks_total']])); ?>

            </p>
        </div>

        <div class="erp-card lg:col-span-2">
            <h2 class="erp-card-title"><?php echo e(__('Certification checks')); ?></h2>
            <ul class="mt-3 space-y-2">
                <?php $__currentLoopData = $report['checks']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex items-start gap-2 text-sm">
                        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'mt-0.5 inline-block h-2 w-2 rounded-full',
                            'bg-emerald-500' => $check['passed'],
                            'bg-red-500' => ! $check['passed'],
                        ]); ?>"></span>
                        <span><?php echo e($check['label']); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    </div>

    <div class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="erp-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('SMTP readiness')); ?></p>
            <p class="mt-2 text-lg font-semibold"><?php echo e($report['smtp']['label']); ?></p>
            <p class="text-xs text-slate-500"><?php echo e($report['smtp']['ready'] ? __('Ready') : __('Not ready')); ?></p>
        </div>
        <div class="erp-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Queue readiness')); ?></p>
            <p class="mt-2 text-lg font-semibold"><?php echo e($report['queue']['ready'] ? __('Ready') : __('Attention required')); ?></p>
            <p class="text-xs text-slate-500"><?php echo e(__('Depth: :depth · Stuck: :stuck', ['depth' => $report['queue']['depth'], 'stuck' => $report['queue']['stuck_sending']])); ?></p>
        </div>
        <div class="erp-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Failure rate (7d)')); ?></p>
            <p class="mt-2 text-lg font-semibold"><?php echo e($report['failure_rate']); ?>%</p>
            <p class="text-xs text-slate-500"><?php echo e($report['health']['label'] ?? ''); ?></p>
        </div>
        <div class="erp-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Retention policy')); ?></p>
            <p class="mt-2 text-lg font-semibold"><?php echo e(number_format($report['retention']['days'])); ?> <?php echo e(__('days')); ?></p>
            <p class="text-xs text-slate-500"><?php echo e(__('No automatic deletion')); ?></p>
        </div>
    </div>

    <div class="mb-6 grid gap-4 lg:grid-cols-2">
        <div class="erp-card">
            <h2 class="erp-card-title"><?php echo e(__('Department readiness')); ?></h2>
            <table class="erp-table mt-3 w-full text-sm">
                <thead>
                    <tr>
                        <th><?php echo e(__('Department')); ?></th>
                        <th><?php echo e(__('Sent')); ?></th>
                        <th><?php echo e(__('Failed')); ?></th>
                        <th><?php echo e(__('Queued')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = ['sales' => __('Sales'), 'accounts' => __('Accounts'), 'hr' => __('HR'), 'production' => __('Production')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($label); ?></td>
                            <td><?php echo e($report['departments'][$key]['sent'] ?? 0); ?></td>
                            <td><?php echo e($report['departments'][$key]['failed'] ?? 0); ?></td>
                            <td><?php echo e($report['departments'][$key]['queued'] ?? 0); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div class="erp-card">
            <h2 class="erp-card-title"><?php echo e(__('Sender readiness (this month)')); ?></h2>
            <ul class="mt-3 space-y-2 text-sm">
                <?php $__currentLoopData = ['hr' => __('HR'), 'sales' => __('Sales'), 'accounts' => __('Accounts'), 'production' => __('Production'), 'notifications' => __('Notifications')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex justify-between gap-4 rounded border border-erp-border px-3 py-2">
                        <span><?php echo e($label); ?></span>
                        <span class="font-medium"><?php echo e($report['senders'][$key] ?? 0); ?> <?php echo e(__('sent')); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    </div>

    <div class="erp-card">
        <h2 class="erp-card-title"><?php echo e(__('Queue diagnostics')); ?></h2>
        <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
            <div><dt class="text-slate-500"><?php echo e(__('Queue')); ?></dt><dd class="font-medium"><?php echo e($report['queue']['name']); ?> (<?php echo e($report['queue']['driver']); ?>)</dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Queued messages')); ?></dt><dd class="font-medium"><?php echo e($report['queue']['queued']); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Stuck sending')); ?></dt><dd class="font-medium"><?php echo e($report['queue']['stuck_sending']); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Failed (all time)')); ?></dt><dd class="font-medium"><?php echo e($report['queue']['failed']); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Cancelled (all time)')); ?></dt><dd class="font-medium"><?php echo e($report['queue']['cancelled']); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Attachments')); ?></dt><dd class="font-medium"><?php echo e($report['attachments']['healthy'] ? __('Healthy') : __('Issues detected')); ?></dd></div>
        </dl>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\email\certification\index.blade.php ENDPATH**/ ?>