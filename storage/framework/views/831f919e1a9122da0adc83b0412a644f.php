<?php
    use App\Support\Inventory\CatalogueDeskViews;
    use App\Support\Navigation\WorkspaceEmbed;

    $active = CatalogueDeskViews::normalize($activeCatalogueView ?? request('view', CatalogueDeskViews::PRODUCTS));
    $frame = WorkspaceEmbed::turboFrame();
    $user = auth()->user();
    $canView = $user?->can('catalogue.view') ?? false;

    $modes = collect([
        [
            'key' => CatalogueDeskViews::PRODUCTS,
            'label' => __('Products'),
            'url' => CatalogueDeskViews::deskUrl(CatalogueDeskViews::PRODUCTS),
            'visible' => $canView,
        ],
        [
            'key' => CatalogueDeskViews::PRICE_LISTS,
            'label' => __('Price lists'),
            'url' => CatalogueDeskViews::deskUrl(CatalogueDeskViews::PRICE_LISTS),
            'visible' => $canView,
        ],
    ])->where('visible', true)->values();
?>

<?php if($modes->count() > 1): ?>
    <nav class="workspace-context-tabs" aria-label="<?php echo e(__('Catalogue desk modes')); ?>">
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\inventory\catalogue\partials\desk-mode-nav.blade.php ENDPATH**/ ?>