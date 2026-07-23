<?php
    use App\Enums\UserSessionStatus;
?>

<section>
    <?php if (isset($component)) { $__componentOriginalba421f08b6b43aecb09f8eebe577a4f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-section','data' => ['title' => __('Active devices & sessions'),'description' => __('Devices currently signed in to your account.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Active devices & sessions')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Devices currently signed in to your account.'))]); ?>
        <div class="md:col-span-2 space-y-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600">
                    <?php echo e(trans_choice(':count active session|:count active sessions', $activeSessionCount, ['count' => $activeSessionCount])); ?>

                </p>
                <div class="flex flex-wrap gap-2">
                    <a href="<?php echo e(route('profile.sessions.index')); ?>" class="erp-btn-secondary text-sm"><?php echo e(__('Manage all sessions')); ?></a>
                    <?php if($activeSessionCount > 1): ?>
                        <form method="POST" action="<?php echo e(route('profile.sessions.destroy-others')); ?>" class="inline" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Log out all other devices?'))->toHtml() ?>)">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Logout other devices')); ?></button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="space-y-2">
                <?php $__empty_1 = true; $__currentLoopData = $activeSessions->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $isCurrent = $session->isCurrentSession($currentSessionId); ?>
                    <div class="rounded-lg border border-erp-border px-4 py-3">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-semibold text-erp-primary"><?php echo e($session->device ?? __('Unknown device')); ?></p>
                                    <?php if($isCurrent): ?>
                                        <span class="rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-700"><?php echo e(__('This device')); ?></span>
                                    <?php endif; ?>
                                    <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => $session->status->badgeVariant()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($session->status->badgeVariant())]); ?><?php echo e($session->status->label()); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
                                </div>
                                <p class="mt-1 text-xs text-slate-600">
                                    <?php echo e($session->browser ?? __('Unknown browser')); ?>

                                    · <?php echo e($session->platform ?? __('Unknown platform')); ?>

                                    · <?php echo e($session->ip_address ?? '—'); ?>

                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    <?php echo e(__('Last activity')); ?>: <?php echo e($session->last_activity_at?->diffForHumans() ?? '—'); ?>

                                </p>
                            </div>
                            <?php if(! $isCurrent && $session->status === UserSessionStatus::Active): ?>
                                <form method="POST" action="<?php echo e(route('profile.sessions.destroy', $session)); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Terminate this session?'))->toHtml() ?>)">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="erp-btn-secondary text-xs"><?php echo e(__('Logout')); ?></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-slate-500"><?php echo e(__('No active sessions recorded yet. Sign out and back in to start session tracking.')); ?></p>
                <?php endif; ?>
            </div>

            <?php if($activeSessions->count() > 5): ?>
                <p class="text-xs text-slate-500">
                    <?php echo e(__('Showing 5 of :count active sessions.', ['count' => $activeSessions->count()])); ?>

                    <a href="<?php echo e(route('profile.sessions.index')); ?>" class="erp-link"><?php echo e(__('View all')); ?></a>
                </p>
            <?php endif; ?>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3)): ?>
<?php $attributes = $__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3; ?>
<?php unset($__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba421f08b6b43aecb09f8eebe577a4f3)): ?>
<?php $component = $__componentOriginalba421f08b6b43aecb09f8eebe577a4f3; ?>
<?php unset($__componentOriginalba421f08b6b43aecb09f8eebe577a4f3); ?>
<?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\profile\partials\sessions-summary.blade.php ENDPATH**/ ?>