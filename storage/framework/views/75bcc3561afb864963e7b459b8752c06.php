<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $turboFrame = WorkspaceEmbed::turboFrame();
    $linkClass = fn (bool $active) => [
        'block px-4 py-3 text-sm font-medium transition-colors',
        'bg-erp-accent/10 text-erp-accent' => $active,
        'text-slate-600 hover:bg-erp-page hover:text-erp-primary' => ! $active,
    ];
?>

<nav class="lg:col-span-1" aria-label="<?php echo e(__('Settings sections')); ?>">
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'p-0 overflow-hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'p-0 overflow-hidden']); ?>
        <ul class="divide-y divide-erp-border">
            <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slug => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li>
                    <a
                        href="<?php echo e(WorkspaceEmbed::url(route('admin.settings.show', ['section' => $slug, 'company_id' => $companyId, 'branch_id' => $branchId]))); ?>"
                        data-turbo-frame="<?php echo e($turboFrame); ?>"
                        data-turbo-action="advance"
                        class="<?php echo \Illuminate\Support\Arr::toCssClasses($linkClass(($current ?? null) === $slug)); ?>"
                    >
                        <?php echo e(__($meta['label'])); ?>

                    </a>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <li>
                <a
                    href="<?php echo e(WorkspaceEmbed::url(route('admin.settings.numbering.index', ['company_id' => $companyId, 'branch_id' => $branchId]))); ?>"
                    data-turbo-frame="<?php echo e($turboFrame); ?>"
                    data-turbo-action="advance"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses($linkClass(($current ?? null) === 'numbering')); ?>"
                >
                    <?php echo e(__('Numbering')); ?>

                </a>
            </li>
            <li>
                <a
                    href="<?php echo e(WorkspaceEmbed::url(route('admin.settings.approvals.index', ['company_id' => $companyId, 'branch_id' => $branchId]))); ?>"
                    data-turbo-frame="<?php echo e($turboFrame); ?>"
                    data-turbo-action="advance"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses($linkClass(($current ?? null) === 'approvals')); ?>"
                >
                    <?php echo e(__('Approvals')); ?>

                </a>
            </li>
            <li>
                <a
                    href="<?php echo e(WorkspaceEmbed::url(route('admin.settings.forms.index', ['company_id' => $companyId, 'branch_id' => $branchId]))); ?>"
                    data-turbo-frame="<?php echo e($turboFrame); ?>"
                    data-turbo-action="advance"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses($linkClass(($current ?? null) === 'forms')); ?>"
                >
                    <?php echo e(__('Forms')); ?>

                </a>
            </li>
        </ul>
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
</nav>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\partials\section-nav.blade.php ENDPATH**/ ?>