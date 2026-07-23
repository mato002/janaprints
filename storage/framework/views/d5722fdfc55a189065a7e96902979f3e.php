<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Email Identity'),'breadcrumbs' => [['label' => __('Administration')], ['label' => __('Integrations')], ['label' => __('Email Identity')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Email Identity'),'description' => __('Outbound sender addresses and production readiness for employee onboarding.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Email Identity')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Outbound sender addresses and production readiness for employee onboarding.'))]); ?>
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

    <?php if(config('email_local_testing.enabled') && config('email_local_testing.show_admin_banner')): ?>
        <div class="mb-4 rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
            <strong><?php echo e(__('Local email testing mode is ON.')); ?></strong>
            <?php echo e(__('Onboarding invitations send through :address via Gmail/dev SMTP. Set EMAIL_LOCAL_TESTING=false before production.', [
                'address' => config('email_local_testing.from_address'),
            ])); ?>

        </div>
    <?php endif; ?>

<?php
        $overall = $readinessSummary['overall'] ?? 'warning';
        $overallClasses = match ($overall) {
            'ready' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
            'missing' => 'border-red-200 bg-red-50 text-red-900',
            default => 'border-amber-200 bg-amber-50 text-amber-900',
        };
    ?>

    <div class="mb-6 rounded-lg border px-4 py-3 text-sm <?php echo e($overallClasses); ?>">
        <strong><?php echo e(__('Production readiness:')); ?></strong>
        <?php echo e(ucfirst($overall)); ?>

        · <?php echo e(__(':ready ready, :warning warnings, :missing missing', $readinessSummary)); ?>

    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <section class="rounded-lg border border-gray-200 bg-white p-5">
            <h2 class="text-base font-semibold text-gray-900"><?php echo e(__('Readiness checklist')); ?></h2>
            <p class="mt-1 text-sm text-gray-500"><?php echo e(__('Verify environment configuration before onboarding employees in production.')); ?></p>

            <ul class="mt-4 space-y-3">
                <?php $__currentLoopData = $readinessChecks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $badge = match ($check['status']) {
                            'ready' => 'bg-emerald-100 text-emerald-800',
                            'missing' => 'bg-red-100 text-red-800',
                            default => 'bg-amber-100 text-amber-800',
                        };
                    ?>
                    <li class="flex items-start justify-between gap-4 rounded-md border border-gray-100 px-3 py-2">
                        <div>
                            <div class="text-sm font-medium text-gray-900"><?php echo e($check['label']); ?></div>
                            <div class="text-xs text-gray-500"><?php echo e($check['detail']); ?></div>
                        </div>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium <?php echo e($badge); ?>"><?php echo e(ucfirst($check['status'])); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-5">
            <h2 class="text-base font-semibold text-gray-900"><?php echo e(__('Queue readiness')); ?></h2>
            <p class="mt-1 text-sm text-gray-500"><?php echo e(__('Onboarding emails are queued on the emails queue.')); ?></p>

            <dl class="mt-4 grid grid-cols-1 gap-3 text-sm">
                <div>
                    <dt class="text-gray-500"><?php echo e(__('Current queue connection')); ?></dt>
                    <dd class="font-medium text-gray-900"><?php echo e($queueGuidance['connection']); ?></dd>
                </div>
                <div>
                    <dt class="text-gray-500"><?php echo e(__('Required queue name')); ?></dt>
                    <dd class="font-medium text-gray-900"><?php echo e($queueGuidance['required_queue']); ?></dd>
                </div>
            </dl>

            <?php if($queueGuidance['sync_warning']): ?>
                <p class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                    <?php echo e(__('Warning: QUEUE_CONNECTION is sync in production. Onboarding emails will send synchronously and may block requests.')); ?>

                </p>
            <?php endif; ?>

            <div class="mt-4 rounded-md bg-gray-50 px-3 py-2 text-sm font-mono text-gray-800">
                <?php echo e($queueGuidance['worker_command']); ?>

            </div>
        </section>
    </div>

    <section class="mt-6 rounded-lg border border-gray-200 bg-white p-5">
        <h2 class="text-base font-semibold text-gray-900"><?php echo e(__('Department & system senders')); ?></h2>
        <p class="mt-1 text-sm text-gray-500"><?php echo e(__('Configured FROM addresses for outbound system mail.')); ?></p>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium text-gray-600"><?php echo e(__('Purpose')); ?></th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600"><?php echo e(__('Email address')); ?></th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600"><?php echo e(__('Configured')); ?></th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600"><?php echo e(__('Fallback')); ?></th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600"><?php echo e(__('Recommended use')); ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__currentLoopData = $mailboxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mailbox): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="px-3 py-2 font-medium text-gray-900"><?php echo e($mailbox['label']); ?></td>
                            <td class="px-3 py-2 text-gray-700"><?php echo e($mailbox['address'] ?: '—'); ?></td>
                            <td class="px-3 py-2">
                                <span class="rounded-full px-2 py-0.5 text-xs <?php echo e($mailbox['configured'] ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700'); ?>">
                                    <?php echo e($mailbox['configured'] ? __('Yes') : __('No')); ?>

                                </span>
                            </td>
                            <td class="px-3 py-2">
                                <?php if($mailbox['used_fallback']): ?>
                                    <span class="text-amber-700"><?php echo e(__('Yes')); ?></span>
                                <?php else: ?>
                                    <?php echo e(__('No')); ?>

                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-2 text-gray-600"><?php echo e($mailbox['recommended_use']); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </section>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\email-identity\index.blade.php ENDPATH**/ ?>