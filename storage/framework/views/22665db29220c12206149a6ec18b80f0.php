<?php
    use App\Models\Production\ProductionJobCard;
    use App\Models\Production\ProductionOutput;
    use App\Models\Production\ProductionQueue;
    use App\Support\Navigation\WorkspaceEmbed;
    use App\Support\Production\ProductionDeskPersona;
    use App\Support\Production\ProductionFloorDeskViews;

    if (WorkspaceEmbed::inWorkspaceContext()) {
        return;
    }

    $persona = $deskPersona ?? ProductionDeskPersona::resolve(auth()->user());
    $active = ProductionFloorDeskViews::normalize($activeFloorView ?? request('view'));
    $activeDepartment = is_string(request('department')) ? request('department') : null;
    $frame = WorkspaceEmbed::turboFrame();
    $user = auth()->user();

    // Department tabs live in the queue ribbon when viewing a department queue.
    if ($persona->usesDepartmentOperationsModes() && $active === ProductionFloorDeskViews::QUEUE) {
        return;
    }

    if ($persona->usesDepartmentOperationsModes()) {
        $modes = collect($persona->standaloneFloorModes($activeDepartment))->map(function (array $mode) use ($active, $activeDepartment) {
            $isActive = $mode['key'] === ProductionFloorDeskViews::FLOOR
                ? $active === ProductionFloorDeskViews::FLOOR
                : ($active === ProductionFloorDeskViews::QUEUE && $activeDepartment === $mode['key']);

            return [
                'key' => $mode['key'],
                'label' => $mode['label'],
                'url' => $mode['url'],
                'active' => $isActive,
            ];
        })->values();
    } else {
        $modes = collect([
            [
                'key' => ProductionFloorDeskViews::FLOOR,
                'label' => __('Run'),
                'url' => ProductionFloorDeskViews::floorUrl(ProductionFloorDeskViews::FLOOR),
                'visible' => $user?->can('viewAny', ProductionJobCard::class) ?? false,
                'active' => $active === ProductionFloorDeskViews::FLOOR,
            ],
            [
                'key' => ProductionFloorDeskViews::REGISTER,
                'label' => __('Register'),
                'url' => ProductionFloorDeskViews::registerIndexUrl(),
                'visible' => $user?->can('viewAny', ProductionJobCard::class) ?? false,
                'active' => $active === ProductionFloorDeskViews::REGISTER,
            ],
            [
                'key' => ProductionFloorDeskViews::QUEUE,
                'label' => __('By department'),
                'url' => ProductionFloorDeskViews::queueIndexUrl(),
                'visible' => $user?->can('viewWorkspace', ProductionQueue::class) ?? false,
                'active' => $active === ProductionFloorDeskViews::QUEUE,
            ],
            [
                'key' => ProductionFloorDeskViews::OUTPUTS,
                'label' => __('Outputs'),
                'url' => ProductionFloorDeskViews::outputsIndexUrl(),
                'visible' => $user?->can('viewAny', ProductionOutput::class) ?? false,
                'active' => $active === ProductionFloorDeskViews::OUTPUTS,
            ],
        ])->where('visible', true)->values();
    }
?>

<?php if($modes->count() > 1): ?>
    <?php if($persona->usesDepartmentOperationsModes()): ?>
        <nav class="production-floor-dept-segments" aria-label="<?php echo e(__('Production floor modes')); ?>">
            <?php $__currentLoopData = $modes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a
                    href="<?php echo e(WorkspaceEmbed::url($mode['url'])); ?>"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'production-floor-dept-segment',
                        'production-floor-dept-segment--'.$mode['key'] => filled($mode['key'] ?? null),
                        'production-floor-dept-segment--active' => $mode['active'] ?? ($mode['key'] === $active),
                    ]); ?>"
                    data-turbo-frame="<?php echo e($frame); ?>"
                    data-turbo-action="advance"
                ><?php echo e($mode['label']); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>
    <?php else: ?>
        <nav class="workspace-context-tabs" aria-label="<?php echo e(__('Production floor modes')); ?>">
            <?php $__currentLoopData = $modes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a
                    href="<?php echo e(WorkspaceEmbed::url($mode['url'])); ?>"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'workspace-context-tab',
                        'workspace-context-tab--active' => $mode['active'] ?? ($mode['key'] === $active),
                    ]); ?>"
                    data-turbo-frame="<?php echo e($frame); ?>"
                    data-turbo-action="advance"
                ><?php echo e($mode['label']); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\floor\partials\desk-mode-nav.blade.php ENDPATH**/ ?>