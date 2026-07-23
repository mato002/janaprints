<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Session Details'),'breadcrumbs' => [
        ['label' => __('Administration')],
        ['label' => __('User Sessions'), 'url' => route('admin.security.sessions.index')],
        ['label' => __('Session #:id', ['id' => $session->id])],
    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Session #:id', ['id' => $session->id]),'description' => __('Detailed sign-in context and lifecycle for this session.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Session #:id', ['id' => $session->id])),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Detailed sign-in context and lifecycle for this session.'))]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <a href="<?php echo e(route('admin.security.sessions.index')); ?>" class="erp-btn-secondary"><?php echo e(__('Back to sessions')); ?></a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('terminate', $session)): ?>
                <?php if($session->status === \App\Enums\UserSessionStatus::Active): ?>
                    <form method="POST" action="<?php echo e(route('admin.security.sessions.terminate', $session)); ?>" class="inline" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Terminate this session?'))->toHtml() ?>)">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="erp-btn-primary"><?php echo e(__('Terminate Session')); ?></button>
                    </form>
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

    <div class="grid gap-4 lg:grid-cols-2">
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
            <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Identity')); ?></h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('User')); ?></dt><dd class="font-medium text-erp-primary"><?php echo e($session->user?->name); ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Email')); ?></dt><dd><?php echo e($session->user?->email); ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Role')); ?></dt><dd><?php echo e($session->role_snapshot ?? '—'); ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Company')); ?></dt><dd><?php echo e($session->company?->name ?? '—'); ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Branch')); ?></dt><dd><?php echo e($session->branch?->name ?? '—'); ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Status')); ?></dt>
                    <dd><?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
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
<?php endif; ?></dd>
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
            <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Device & Network')); ?></h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('IP Address')); ?></dt><dd class="font-mono text-xs"><?php echo e($session->ip_address ?? '—'); ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Device')); ?></dt><dd><?php echo e($session->device ?? '—'); ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Browser')); ?></dt><dd><?php echo e($session->browser ?? '—'); ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Platform')); ?></dt><dd><?php echo e($session->platform ?? '—'); ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Location')); ?></dt><dd><?php echo e($session->location ?: __('Available after geo enrichment')); ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Laravel Session')); ?></dt><dd class="font-mono text-xs break-all"><?php echo e($session->laravel_session_id ?? '—'); ?></dd></div>
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

        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'lg:col-span-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:col-span-2']); ?>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Timeline')); ?></h3>
            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-4">
                <div><dt class="text-slate-500"><?php echo e(__('Login Time')); ?></dt><dd class="mt-1 font-medium"><?php echo e($session->login_at?->format('M j, Y g:i A')); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Last Activity')); ?></dt><dd class="mt-1 font-medium"><?php echo e($session->last_activity_at?->format('M j, Y g:i A')); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Logged Out')); ?></dt><dd class="mt-1 font-medium"><?php echo e($session->logged_out_at?->format('M j, Y g:i A') ?? '—'); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Revoked')); ?></dt><dd class="mt-1 font-medium"><?php echo e($session->revoked_at?->format('M j, Y g:i A') ?? '—'); ?></dd></div>
            </dl>
            <?php if($session->revoked_by): ?>
                <p class="mt-4 text-sm text-slate-600">
                    <?php echo e(__('Revoked by :name', ['name' => $session->revokedByUser?->name ?? __('Unknown')])); ?>

                    <?php if($session->revoke_reason): ?>
                        · <?php echo e($session->revoke_reason); ?>

                    <?php endif; ?>
                </p>
            <?php endif; ?>
            <?php if($session->user_agent): ?>
                <p class="mt-4 rounded-lg bg-slate-50 p-3 font-mono text-xs text-slate-600 break-all"><?php echo e($session->user_agent); ?></p>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\user-sessions\show.blade.php ENDPATH**/ ?>