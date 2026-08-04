<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $hubBackUrl = route('admin.settings.show', ['section' => 'hub'] + $scopeQuery);
    $embedded = WorkspaceEmbed::isEmbedded();
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Company Email'),'breadcrumbs' => $embedded ? [] : [
        ['label' => __('Administration')],
        ['label' => __('Configuration')],
        ['label' => __('Company Email')],
    ],'useWorkspaceNavigation' => ! $embedded] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (! ($embedded)): ?>
        <?php echo $__env->make('admin.settings.partials.hub-toolbar', [
            'title' => __('Company Email'),
            'description' => __('Create and manage company mailboxes through cPanel without leaving the dashboard.'),
            'backUrl' => $hubBackUrl,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <?php echo $__env->make('admin.settings.partials.scope-selector', [
        'action' => route('admin.settings.company-email.index'),
        'companyId' => $companyId,
        'branchId' => $branchId,
        'companies' => $companies,
        'branches' => $branches,
        'branchEmptyLabel' => __('Company-wide default'),
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'xl:col-span-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'xl:col-span-2']); ?>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-erp-primary"><?php echo e(__('cPanel connection')); ?></h2>
                    <p class="mt-1 text-sm text-slate-500"><?php echo e(__('Mailbox operations use the server credentials configured in your environment.')); ?></p>
                </div>
                <form method="POST" action="<?php echo e(route('admin.settings.company-email.test-connection', $scopeQuery)); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="erp-btn-secondary text-sm"><?php echo e(__('Test connection')); ?></button>
                </form>
            </div>

            <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-slate-500"><?php echo e(__('Host')); ?></dt>
                    <dd class="font-medium text-erp-primary"><?php echo e($connection['host'] ?? '—'); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e(__('Domain')); ?></dt>
                    <dd class="font-medium text-erp-primary"><?php echo e($connection['domain'] ?? '—'); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e(__('Username')); ?></dt>
                    <dd class="font-medium text-erp-primary"><?php echo e($connection['username'] ?? '—'); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e(__('API token')); ?></dt>
                    <dd class="font-medium text-erp-primary">
                        <?php echo e($connection['token_configured'] ? __('Configured') : __('Missing')); ?>

                    </dd>
                </div>
            </dl>

            <?php if (! ($connection['configured'])): ?>
                <p class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                    <?php echo e(__('Set CPANEL_HOST, CPANEL_USERNAME, CPANEL_API_TOKEN, and MAILBOX_DOMAIN in your environment to enable mailbox management.')); ?>

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
            <h2 class="text-base font-semibold text-erp-primary"><?php echo e(__('Quick actions')); ?></h2>
            <p class="mt-1 text-sm text-slate-500"><?php echo e(__('Default mailbox quota: :quota MB', ['quota' => number_format($defaultQuotaMb)])); ?></p>

            <?php if($canManage && $connection['configured']): ?>
                <a href="<?php echo e(route('admin.settings.company-email.create', $scopeQuery)); ?>" class="erp-btn-primary mt-4 inline-flex w-full justify-center">
                    <?php echo e(__('Create mailbox')); ?>

                </a>
            <?php else: ?>
                <p class="mt-4 text-sm text-slate-500">
                    <?php echo e($canManage ? __('Complete cPanel configuration before creating mailboxes.') : __('You have view-only access.')); ?>

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
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-erp-primary"><?php echo e(__('Company mailboxes')); ?></h2>
                <p class="mt-1 text-sm text-slate-500"><?php echo e(__('Mailboxes provisioned on :domain', ['domain' => $connection['domain'] ?? __('your domain')])); ?></p>
            </div>
            <span class="rounded-full bg-erp-page px-2.5 py-1 text-xs font-medium text-slate-600">
                <?php echo e(trans_choice(':count mailbox|:count mailboxes', count($mailboxes), ['count' => count($mailboxes)])); ?>

            </span>
        </div>

        <?php if($loadError): ?>
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                <?php echo e(__('Unable to load mailboxes: :message', ['message' => $loadError])); ?>

            </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="erp-table erp-table--grid min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="py-3 pr-3"><?php echo e(__('Email address')); ?></th>
                        <th class="py-3 px-2"><?php echo e(__('Usage')); ?></th>
                        <th class="py-3 px-2"><?php echo e(__('Quota')); ?></th>
                        <th class="py-3 px-2"><?php echo e(__('Status')); ?></th>
                        <th class="erp-table-actions-col"><?php echo e(__('Actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $mailboxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mailbox): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="py-3 pr-3 font-medium text-erp-primary"><?php echo e($mailbox['email']); ?></td>
                            <td class="py-3 px-2 text-slate-600">
                                <?php if($mailbox['disk_used_mb'] !== null): ?>
                                    <?php echo e(number_format($mailbox['disk_used_mb'], 2)); ?> MB
                                    <?php if($mailbox['disk_used_percent'] !== null): ?>
                                        (<?php echo e($mailbox['disk_used_percent']); ?>%)
                                    <?php endif; ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-2 text-slate-600">
                                <?php if($mailbox['quota_unlimited'] ?? false): ?>
                                    <?php echo e(__('Unlimited')); ?>

                                <?php elseif($mailbox['disk_quota_mb'] !== null): ?>
                                    <?php echo e(number_format($mailbox['disk_quota_mb'], 0).' MB'); ?>

                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-2">
                                <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => $mailbox['suspended'] ? 'danger' : 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($mailbox['suspended'] ? 'danger' : 'success')]); ?>
                                    <?php echo e($mailbox['suspended'] ? __('Suspended') : __('Active')); ?>

                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
                            </td>
                            <td class="erp-table-actions-col">
                                <?php if (isset($component)) { $__componentOriginalb5a89013017505cf4d4d69115d724d07 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb5a89013017505cf4d4d69115d724d07 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-row-actions','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-row-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                                    <?php if (isset($component)) { $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-row-action','data' => ['href' => route('admin.settings.company-email.show', ['address' => $mailbox['email']] + $scopeQuery)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.settings.company-email.show', ['address' => $mailbox['email']] + $scopeQuery))]); ?>
                                        <?php echo e(__('Manage')); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0)): ?>
<?php $attributes = $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0; ?>
<?php unset($__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0)): ?>
<?php $component = $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0; ?>
<?php unset($__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0); ?>
<?php endif; ?>
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb5a89013017505cf4d4d69115d724d07)): ?>
<?php $attributes = $__attributesOriginalb5a89013017505cf4d4d69115d724d07; ?>
<?php unset($__attributesOriginalb5a89013017505cf4d4d69115d724d07); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb5a89013017505cf4d4d69115d724d07)): ?>
<?php $component = $__componentOriginalb5a89013017505cf4d4d69115d724d07; ?>
<?php unset($__componentOriginalb5a89013017505cf4d4d69115d724d07); ?>
<?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500">
                                <?php if($connection['configured']): ?>
                                    <?php echo e(__('No mailboxes found on this domain.')); ?>

                                <?php else: ?>
                                    <?php echo e(__('Configure cPanel credentials to list company mailboxes.')); ?>

                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\company-email\index.blade.php ENDPATH**/ ?>