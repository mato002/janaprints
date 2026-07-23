<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['customer', 'emailTimeline', 'customerEmailMessages' => collect()]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['customer', 'emailTimeline', 'customerEmailMessages' => collect()]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Communications\EmailCampaign::class)): ?>
    <div class="crm-360__channel-card">
        <div class="crm-360__card-head">
            <h3 class="erp-card-title"><?php echo e(__('Email')); ?></h3>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Communications\EmailCampaign::class)): ?>
                <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['variant' => 'outline','size' => 'sm','href' => route('admin.communications.email.compose', ['to' => $customer->email, 'customer_id' => $customer->id]),'dataTurboFrame' => 'erp-main']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','size' => 'sm','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.communications.email.compose', ['to' => $customer->email, 'customer_id' => $customer->id])),'data-turbo-frame' => 'erp-main']); ?>
                     <?php $__env->slot('icon', null, []); ?> 
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                     <?php $__env->endSlot(); ?>
                    <?php echo e(__('Compose')); ?>

                 <?php echo $__env->renderComponent(); ?>
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
        </div>

        <?php if($customerEmailMessages->isNotEmpty()): ?>
            <ul class="mt-3 space-y-2 text-sm">
                <?php $__currentLoopData = $customerEmailMessages->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="rounded border border-erp-border px-3 py-2">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="font-medium"><?php echo e(Str::limit($message['subject'], 60)); ?></span>
                            <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase <?php echo e($message['status_badge']); ?>"><?php echo e($message['status_label']); ?></span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500"><?php echo e($message['type_label']); ?> · <?php echo e($message['sender'] ?? '—'); ?> · <?php echo e($message['date_formatted']); ?></p>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <a href="<?php echo e(route('admin.crm.customers.show', ['customer' => $customer, 'tab' => 'communications'])); ?>" class="mt-3 inline-flex text-sm text-erp-accent" data-turbo-frame="erp-main"><?php echo e(__('View all communications')); ?></a>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Communications\CommunicationLog::class)): ?>
            <div class="mt-3">
                <p class="text-xs font-semibold uppercase text-slate-500 mb-2"><?php echo e(__('Email timeline (COM-4)')); ?></p>
                <?php if (isset($component)) { $__componentOriginalf31cb82ccd763b22007007c4a1985569 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf31cb82ccd763b22007007c4a1985569 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.communication-timeline','data' => ['logs' => $emailTimeline,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.communication-timeline'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['logs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($emailTimeline),'compact' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf31cb82ccd763b22007007c4a1985569)): ?>
<?php $attributes = $__attributesOriginalf31cb82ccd763b22007007c4a1985569; ?>
<?php unset($__attributesOriginalf31cb82ccd763b22007007c4a1985569); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf31cb82ccd763b22007007c4a1985569)): ?>
<?php $component = $__componentOriginalf31cb82ccd763b22007007c4a1985569; ?>
<?php unset($__componentOriginalf31cb82ccd763b22007007c4a1985569); ?>
<?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/communications/email/partials/customer-panel.blade.php ENDPATH**/ ?>