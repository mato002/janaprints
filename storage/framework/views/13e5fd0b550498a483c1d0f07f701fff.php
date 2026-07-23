<?php
    $statusValue = $customer->status->value;
    $statusLabel = ucfirst($statusValue);
    $typeLabel = ucfirst($customer->customer_type->value);
    $contactBits = collect([
        $customer->contact_person ? __('Contact').': '.$customer->contact_person : null,
        $customer->phone ?: null,
        $customer->email ?: null,
    ])->filter()->values();
?>

<header class="crm-360__header">
    <div class="crm-360__header-main">
        <div class="crm-360__identity">
            <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['variant' => 'ghost','size' => 'sm','href' => route('admin.crm.customers.index'),'class' => 'crm-360__back !px-2','dataTurboFrame' => 'erp-main']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','size' => 'sm','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.index')),'class' => 'crm-360__back !px-2','data-turbo-frame' => 'erp-main']); ?>← <?php echo e(__('Customers')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>

            <h1 class="crm-360__title"><?php echo e($customer->company_name); ?></h1>

            <p class="crm-360__subtitle">
                <span class="font-mono text-slate-600"><?php echo e($customer->customer_code); ?></span>
                <span class="crm-360__meta-sep" aria-hidden="true">·</span>
                <span><?php echo e($typeLabel); ?></span>
                <?php if($customer->branch): ?>
                    <span class="crm-360__meta-sep" aria-hidden="true">·</span>
                    <span><?php echo e($customer->branch->name); ?></span>
                <?php endif; ?>
            </p>

            <p class="crm-360__since">
                <span class="crm-360__status crm-360__status--<?php echo e($statusValue); ?> crm-360__status--inline"><?php echo e($statusLabel); ?></span>
                <span class="crm-360__meta-sep" aria-hidden="true">·</span>
                <span><?php echo e(__('Customer since')); ?> <?php echo e($customer->created_at?->format('M Y') ?? '—'); ?></span>
            </p>

            <?php if($contactBits->isNotEmpty()): ?>
                <p class="crm-360__contact-line">
                    <?php echo e($contactBits->join(' · ')); ?>

                </p>
            <?php endif; ?>
        </div>

        <?php echo $__env->make('admin.crm.customers.360.partials.primary-actions', [
            'customer' => $customer,
            'latestOrderForRepeat' => $latestOrderForRepeat ?? null,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</header>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/crm/customers/360/header.blade.php ENDPATH**/ ?>