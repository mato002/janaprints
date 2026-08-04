<?php
    $nav = [
        ['label' => __('Overview'), 'route' => 'admin.inventory.intelligence.overview'],
        ['label' => __('Stockout Risk'), 'route' => 'admin.inventory.intelligence.stockout-risk'],
        ['label' => __('Dead Stock'), 'route' => 'admin.inventory.intelligence.dead-stock'],
        ['label' => __('Fast Movers'), 'route' => 'admin.inventory.intelligence.fast-movers'],
        ['label' => __('Slow Movers'), 'route' => 'admin.inventory.intelligence.slow-movers'],
        ['label' => __('Warehouse Velocity'), 'route' => 'admin.inventory.intelligence.warehouse-velocity'],
    ];

    if (auth()->user()?->can('inventory.intelligence.configure')) {
        $nav[] = ['label' => __('Settings'), 'route' => 'admin.inventory.intelligence.settings'];
    }
?>

<?php if (isset($component)) { $__componentOriginalaf09cf0e3994b3eea4e08edef5123a35 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaf09cf0e3994b3eea4e08edef5123a35 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.workspace-nav','data' => ['links' => $nav,'variant' => 'compact']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.workspace-nav'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['links' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($nav),'variant' => 'compact']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaf09cf0e3994b3eea4e08edef5123a35)): ?>
<?php $attributes = $__attributesOriginalaf09cf0e3994b3eea4e08edef5123a35; ?>
<?php unset($__attributesOriginalaf09cf0e3994b3eea4e08edef5123a35); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaf09cf0e3994b3eea4e08edef5123a35)): ?>
<?php $component = $__componentOriginalaf09cf0e3994b3eea4e08edef5123a35; ?>
<?php unset($__componentOriginalaf09cf0e3994b3eea4e08edef5123a35); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\inventory\intelligence\partials\nav.blade.php ENDPATH**/ ?>