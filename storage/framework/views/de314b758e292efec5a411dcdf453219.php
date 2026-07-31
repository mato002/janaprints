<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $frame = WorkspaceEmbed::turboFrame();
?>

<?php if(count($fastActions ?? []) > 0): ?>
    <section class="store-desk-actions h-full rounded-xl border border-erp-border bg-white p-3 shadow-sm" aria-label="<?php echo e(__('Quick actions')); ?>">
        <h2 class="mb-2 text-sm font-semibold text-slate-900"><?php echo e(__('Quick actions')); ?></h2>
        <div class="grid grid-cols-3 gap-2 sm:grid-cols-3">
            <?php $__currentLoopData = $fastActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a
                    href="<?php echo e(($action['modal'] ?? false) ? $action['url'] : WorkspaceEmbed::url($action['url'])); ?>"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'store-desk-action-tile flex flex-col items-center justify-center gap-1.5 rounded-lg border px-2 py-3 text-center transition',
                        'border-erp-accent/40 bg-erp-accent/5 text-erp-primary hover:bg-erp-accent/10' => $action['primary'] ?? false,
                        'border-slate-200 bg-white text-slate-700 hover:border-erp-accent/30 hover:bg-slate-50' => ! ($action['primary'] ?? false),
                    ]); ?>"
                    <?php if($action['modal'] ?? false): ?>
                        data-erp-modal-open
                    <?php else: ?>
                        data-turbo-frame="<?php echo e($frame); ?>"
                        data-turbo-action="advance"
                    <?php endif; ?>
                >
                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'flex h-9 w-9 items-center justify-center rounded-lg',
                        'bg-erp-accent text-white' => $action['primary'] ?? false,
                        'bg-slate-100 text-slate-700' => ! ($action['primary'] ?? false),
                    ]); ?>">
                        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $action['icon'] ?? 'shopping-cart','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action['icon'] ?? 'shopping-cart'),'class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                    </span>
                    <span class="text-[11px] font-semibold leading-tight"><?php echo e($action['label']); ?></span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/procurement/desk/partials/fast-actions.blade.php ENDPATH**/ ?>