<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $indexUrl = route('admin.settings.company-email.index', $scopeQuery);
    $embedded = WorkspaceEmbed::isEmbedded();
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $mailbox['email'],'breadcrumbs' => $embedded ? [] : [
        ['label' => __('Administration')],
        ['label' => __('Configuration')],
        ['label' => __('Company Email')],
        ['label' => $mailbox['email']],
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
            'title' => $mailbox['email'],
            'description' => __('Manage password, storage quota, and lifecycle for this company mailbox.'),
            'backUrl' => $indexUrl,
            'backLabel' => __('Company Email'),
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
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
            <h2 class="text-base font-semibold text-erp-primary"><?php echo e(__('Mailbox details')); ?></h2>

            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500"><?php echo e(__('Email address')); ?></dt>
                    <dd class="font-medium text-erp-primary"><?php echo e($mailbox['email']); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e(__('Login')); ?></dt>
                    <dd class="font-medium text-erp-primary"><?php echo e($mailbox['login']); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e(__('Disk usage')); ?></dt>
                    <dd class="font-medium text-erp-primary">
                        <?php if($mailbox['disk_used_mb'] !== null): ?>
                            <?php echo e(number_format($mailbox['disk_used_mb'], 2)); ?> MB
                            <?php if($mailbox['disk_used_percent'] !== null): ?>
                                (<?php echo e($mailbox['disk_used_percent']); ?>%)
                            <?php endif; ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e(__('Quota')); ?></dt>
                    <dd class="font-medium text-erp-primary">
                        <?php if($mailbox['quota_unlimited'] ?? false): ?>
                            <?php echo e(__('Unlimited')); ?>

                        <?php elseif($mailbox['disk_quota_mb'] !== null): ?>
                            <?php echo e(number_format($mailbox['disk_quota_mb'], 0).' MB'); ?>

                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e(__('Status')); ?></dt>
                    <dd>
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
                    </dd>
                </div>
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

        <?php if($canManage): ?>
            <div class="space-y-6">
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
                    <h2 class="text-base font-semibold text-erp-primary"><?php echo e(__('Update password')); ?></h2>
                    <p class="mt-1 text-sm text-slate-500"><?php echo e(__('Set a new mailbox password in cPanel.')); ?></p>

                    <form method="POST" action="<?php echo e(route('admin.settings.company-email.update-password', $scopeQuery)); ?>" class="mt-4 space-y-4">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <input type="hidden" name="address" value="<?php echo e($mailbox['email']); ?>">

                        <?php if (isset($component)) { $__componentOriginalcdc2a210a45cd57105bfa5f0042ffa8f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcdc2a210a45cd57105bfa5f0042ffa8f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.password-input','data' => ['id' => 'password','name' => 'password','label' => __('New password'),'required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.password-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'password','name' => 'password','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('New password')),'required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcdc2a210a45cd57105bfa5f0042ffa8f)): ?>
<?php $attributes = $__attributesOriginalcdc2a210a45cd57105bfa5f0042ffa8f; ?>
<?php unset($__attributesOriginalcdc2a210a45cd57105bfa5f0042ffa8f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcdc2a210a45cd57105bfa5f0042ffa8f)): ?>
<?php $component = $__componentOriginalcdc2a210a45cd57105bfa5f0042ffa8f; ?>
<?php unset($__componentOriginalcdc2a210a45cd57105bfa5f0042ffa8f); ?>
<?php endif; ?>

                        <?php if (isset($component)) { $__componentOriginalcdc2a210a45cd57105bfa5f0042ffa8f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcdc2a210a45cd57105bfa5f0042ffa8f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.password-input','data' => ['id' => 'password_confirmation','name' => 'password_confirmation','label' => __('Confirm password'),'required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.password-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'password_confirmation','name' => 'password_confirmation','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Confirm password')),'required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcdc2a210a45cd57105bfa5f0042ffa8f)): ?>
<?php $attributes = $__attributesOriginalcdc2a210a45cd57105bfa5f0042ffa8f; ?>
<?php unset($__attributesOriginalcdc2a210a45cd57105bfa5f0042ffa8f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcdc2a210a45cd57105bfa5f0042ffa8f)): ?>
<?php $component = $__componentOriginalcdc2a210a45cd57105bfa5f0042ffa8f; ?>
<?php unset($__componentOriginalcdc2a210a45cd57105bfa5f0042ffa8f); ?>
<?php endif; ?>

                        <button type="submit" class="erp-btn-primary"><?php echo e(__('Update password')); ?></button>
                    </form>
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
                    <h2 class="text-base font-semibold text-erp-primary"><?php echo e(__('Storage quota')); ?></h2>
                    <p class="mt-1 text-sm text-slate-500"><?php echo e(__('Adjust the mailbox storage limit in cPanel.')); ?></p>

                    <?php
                        $quotaUnlimited = (bool) old('unlimited_quota', $mailbox['quota_unlimited'] ?? false);
                        $currentQuotaMb = old('quota_mb', $mailbox['disk_quota_mb'] !== null ? (int) round($mailbox['disk_quota_mb']) : (int) config('mailboxes.cpanel.default_quota_mb', 250));
                    ?>

                    <form
                        method="POST"
                        action="<?php echo e(route('admin.settings.company-email.update-quota', $scopeQuery)); ?>"
                        class="mt-4 space-y-4"
                        x-data="{ unlimited: <?php echo \Illuminate\Support\Js::from($quotaUnlimited)->toHtml() ?> }"
                    >
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <input type="hidden" name="address" value="<?php echo e($mailbox['email']); ?>">

                        <div>
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input
                                    type="checkbox"
                                    name="unlimited_quota"
                                    value="1"
                                    class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                                    x-model="unlimited"
                                    <?php if($quotaUnlimited): echo 'checked'; endif; ?>
                                >
                                <?php echo e(__('Unlimited storage')); ?>

                            </label>
                            <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Matches cPanel unlimited quota (0 MB limit).')); ?></p>
                        </div>

                        <div x-show="! unlimited" x-cloak>
                            <label for="quota_mb" class="erp-label"><?php echo e(__('Quota (MB)')); ?></label>
                            <input
                                type="number"
                                name="quota_mb"
                                id="quota_mb"
                                value="<?php echo e($currentQuotaMb); ?>"
                                min="1"
                                max="10240"
                                class="erp-input mt-1 w-full"
                                :required="! unlimited"
                                :disabled="unlimited"
                            >
                            <?php $__errorArgs = ['quota_mb'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <p class="mt-1 text-xs text-slate-500">
                                <?php echo e(__('Current usage: :usage', [
                                    'usage' => $mailbox['disk_used_mb'] !== null
                                        ? number_format($mailbox['disk_used_mb'], 2).' MB'
                                        : __('Unknown'),
                                ])); ?>

                            </p>
                        </div>

                        <button type="submit" class="erp-btn-primary"><?php echo e(__('Update quota')); ?></button>
                    </form>
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
    </div>

    <?php if($canManage): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-6 border-red-200']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6 border-red-200']); ?>
            <h2 class="text-base font-semibold text-red-700"><?php echo e(__('Danger zone')); ?></h2>
            <p class="mt-1 text-sm text-slate-500"><?php echo e(__('Deleting a mailbox removes it permanently from cPanel.')); ?></p>

            <form
                method="POST"
                action="<?php echo e(route('admin.settings.company-email.destroy', $scopeQuery)); ?>"
                class="mt-4"
                onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Delete :email permanently?', ['email' => $mailbox['email']]))->toHtml() ?>)"
            >
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <input type="hidden" name="address" value="<?php echo e($mailbox['email']); ?>">
                <button type="submit" class="erp-btn-danger"><?php echo e(__('Delete mailbox')); ?></button>
            </form>
            <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\company-email\show.blade.php ENDPATH**/ ?>