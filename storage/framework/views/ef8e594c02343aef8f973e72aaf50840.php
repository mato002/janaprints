<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['filters', 'branches', 'cashiers', 'report_views']));

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

foreach (array_filter((['filters', 'branches', 'cashiers', 'report_views']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use App\Enums\PosPaymentMethod;
    use App\Enums\PosSaleStatus;
?>

<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false,'class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'class' => 'mb-4']); ?>
    <?php if (isset($component)) { $__componentOriginal9577df2686262fb25ceb19a81119823d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9577df2686262fb25ceb19a81119823d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.index-toolbar','data' => ['action' => route('admin.commercial.pos.reports.index'),'resetUrl' => route('admin.commercial.pos.reports.index'),'showReset' => false,'turboFrame' => ''.e(\App\Support\Navigation\WorkspaceEmbed::turboFrame()).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.index-toolbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.commercial.pos.reports.index')),'reset-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.commercial.pos.reports.index')),'show-reset' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'turbo-frame' => ''.e(\App\Support\Navigation\WorkspaceEmbed::turboFrame()).'']); ?>
        <select id="tab" name="tab" class="erp-toolbar-select min-w-[11rem]" aria-label="<?php echo e(__('Report view')); ?>" data-erp-auto-submit>
            <?php $__currentLoopData = $report_views; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $view): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($view['key']); ?>" <?php if(($filters['tab'] ?? 'sales_by_cashier') === $view['key']): echo 'selected'; endif; ?>><?php echo e($view['label']); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <input type="date" id="from_date" name="from_date" value="<?php echo e($filters['from_date']); ?>" class="erp-toolbar-input" aria-label="<?php echo e(__('From date')); ?>">
        <input type="date" id="to_date" name="to_date" value="<?php echo e($filters['to_date']); ?>" class="erp-toolbar-input" aria-label="<?php echo e(__('To date')); ?>">
        <select id="branch_id" name="branch_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Branch')); ?>">
            <option value=""><?php echo e(__('All branches')); ?></option>
            <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($branch->id); ?>" <?php if(($filters['branch_id'] ?? null) == $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <select id="cashier_id" name="cashier_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Cashier')); ?>">
            <option value=""><?php echo e(__('All cashiers')); ?></option>
            <?php $__currentLoopData = $cashiers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cashier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($cashier->id); ?>" <?php if(($filters['cashier_id'] ?? null) == $cashier->id): echo 'selected'; endif; ?>><?php echo e($cashier->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <select id="payment_method" name="payment_method" class="erp-toolbar-select" aria-label="<?php echo e(__('Payment method')); ?>">
            <option value=""><?php echo e(__('All payment methods')); ?></option>
            <?php $__currentLoopData = PosPaymentMethod::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($method->value); ?>" <?php if(($filters['payment_method'] ?? '') === $method->value): echo 'selected'; endif; ?>><?php echo e(ucfirst($method->value)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

         <?php $__env->slot('actions', null, []); ?> 
            <select id="status" name="status" class="erp-toolbar-select" aria-label="<?php echo e(__('Status')); ?>">
                <option value=""><?php echo e(__('All statuses')); ?></option>
                <?php $__currentLoopData = PosSaleStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status->value); ?>" <?php if(($filters['status'] ?? '') === $status->value): echo 'selected'; endif; ?>><?php echo e(ucfirst(str_replace('_', ' ', $status->value))); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <a
                href="<?php echo e(route('admin.commercial.pos.reports.index')); ?>"
                class="erp-btn-ghost shrink-0 py-1 text-xs text-slate-500"
                data-turbo-frame="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::turboFrame()); ?>"
            ><?php echo e(__('Reset')); ?></a>
         <?php $__env->endSlot(); ?>

         <?php $__env->slot('secondary', null, []); ?> 
            <input
                type="search"
                id="search"
                name="search"
                value="<?php echo e($filters['search'] ?? ''); ?>"
                placeholder="<?php echo e(__('Sale number…')); ?>"
                class="erp-toolbar-input w-full min-w-0 flex-1"
                data-erp-auto-search
                aria-label="<?php echo e(__('Search')); ?>"
            >
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9577df2686262fb25ceb19a81119823d)): ?>
<?php $attributes = $__attributesOriginal9577df2686262fb25ceb19a81119823d; ?>
<?php unset($__attributesOriginal9577df2686262fb25ceb19a81119823d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9577df2686262fb25ceb19a81119823d)): ?>
<?php $component = $__componentOriginal9577df2686262fb25ceb19a81119823d; ?>
<?php unset($__componentOriginal9577df2686262fb25ceb19a81119823d); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/commercial/pos/intelligence/partials/filters.blade.php ENDPATH**/ ?>