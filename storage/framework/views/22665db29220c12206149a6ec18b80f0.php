<?php
    use App\Models\Production\ProductionJobCard;
    use App\Models\Production\ProductionOutput;
    use App\Models\Production\ProductionQueue;
    use App\Support\Navigation\WorkspaceEmbed;
    use App\Support\Production\ProductionFloorDeskViews;

    if (WorkspaceEmbed::inWorkspaceContext()) {
        return;
    }

    $active = ProductionFloorDeskViews::normalize($activeFloorView ?? request('view'));
    $frame = WorkspaceEmbed::turboFrame();
    $user = auth()->user();
    $modes = collect([
        [
            'key' => ProductionFloorDeskViews::FLOOR,
            'label' => __('Run'),
            'url' => ProductionFloorDeskViews::floorUrl(ProductionFloorDeskViews::FLOOR),
            'visible' => $user?->can('viewAny', ProductionJobCard::class) ?? false,
        ],
        [
            'key' => ProductionFloorDeskViews::REGISTER,
            'label' => __('Register'),
            'url' => ProductionFloorDeskViews::registerIndexUrl(),
            'visible' => $user?->can('viewAny', ProductionJobCard::class) ?? false,
        ],
        [
            'key' => ProductionFloorDeskViews::QUEUE,
            'label' => __('By department'),
            'url' => ProductionFloorDeskViews::queueIndexUrl(),
            'visible' => $user?->can('viewWorkspace', ProductionQueue::class) ?? false,
        ],
        [
            'key' => ProductionFloorDeskViews::OUTPUTS,
            'label' => __('Outputs'),
            'url' => ProductionFloorDeskViews::outputsIndexUrl(),
            'visible' => $user?->can('viewAny', ProductionOutput::class) ?? false,
        ],
    ])->where('visible', true)->values();
?>

<?php if($modes->count() > 1): ?>
    <nav class="workspace-context-tabs" aria-label="<?php echo e(__('Production floor modes')); ?>">
        <?php $__currentLoopData = $modes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a
                href="<?php echo e(WorkspaceEmbed::url($mode['url'])); ?>"
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'workspace-context-tab',
                    'workspace-context-tab--active' => $mode['key'] === $active,
                ]); ?>"
                data-turbo-frame="<?php echo e($frame); ?>"
                data-turbo-action="advance"
            ><?php echo e($mode['label']); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\floor\partials\desk-mode-nav.blade.php ENDPATH**/ ?>