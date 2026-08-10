<?php
    use App\Support\Artwork\DesignerDeskViews;
    use App\Support\Navigation\WorkspaceEmbed;

    if (WorkspaceEmbed::inWorkspaceContext()) {
        return;
    }

    $active = DesignerDeskViews::normalize(request('filter', DesignerDeskViews::QUEUE));
    $frame = WorkspaceEmbed::turboFrame();
    $user = auth()->user();
    $modes = collect([
        [
            'key' => DesignerDeskViews::QUEUE,
            'label' => __('Queue'),
            'url' => DesignerDeskViews::deskUrl(DesignerDeskViews::QUEUE),
            'visible' => $user?->can('viewAny', \App\Models\Artwork\ArtworkRequest::class) ?? false,
        ],
        [
            'key' => DesignerDeskViews::AVAILABLE,
            'label' => __('Available'),
            'url' => DesignerDeskViews::availableUrl(),
            'visible' => $user?->can('viewAny', \App\Models\Artwork\ArtworkRequest::class) ?? false,
        ],
        [
            'key' => DesignerDeskViews::MINE,
            'label' => __('Mine'),
            'url' => DesignerDeskViews::mineUrl(),
            'visible' => $user?->can('viewAny', \App\Models\Artwork\ArtworkRequest::class) ?? false,
        ],
        [
            'key' => DesignerDeskViews::WORKING,
            'label' => __('Working'),
            'url' => DesignerDeskViews::workingUrl(),
            'visible' => $user?->can('viewAny', \App\Models\Artwork\ArtworkRequest::class) ?? false,
        ],
        [
            'key' => DesignerDeskViews::REVIEW,
            'label' => __('Review'),
            'url' => DesignerDeskViews::reviewUrl(),
            'visible' => $user?->can('viewAny', \App\Models\Artwork\ArtworkRequest::class) ?? false,
        ],
    ])->where('visible', true)->values();
?>

<?php if($modes->count() > 1): ?>
    <div class="designer-desk-ribbon mb-3 shrink-0">
        <nav class="designer-desk-ribbon__tabs" aria-label="<?php echo e(__('Designer desk modes')); ?>">
            <?php $__currentLoopData = $modes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a
                    href="<?php echo e(WorkspaceEmbed::url($mode['url'])); ?>"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'designer-desk-ribbon__tab',
                        'designer-desk-ribbon__tab--'.$mode['key'] => filled($mode['key'] ?? null),
                        'designer-desk-ribbon__tab--active' => $mode['key'] === $active,
                    ]); ?>"
                    data-turbo-frame="<?php echo e($frame); ?>"
                    data-turbo-action="advance"
                ><?php echo e($mode['label']); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/artwork/desk/partials/desk-mode-nav.blade.php ENDPATH**/ ?>