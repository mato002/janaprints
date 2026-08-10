<?php
    $designerOperator = auth()->user()?->prefersDesignerOperatorMode() ?? false;
    $artworkHomeLabel = $designerOperator ? __('Designer Desk') : __('Artwork');
    $artworkHomeUrl = $designerOperator
        ? route('admin.artwork.desk')
        : route('admin.artwork.dashboard');
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $request->request_number,'breadcrumbs' => [['label' => $artworkHomeLabel, 'url' => $artworkHomeUrl], ['label' => $request->request_number]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="artwork-request-detail">
        <?php echo $__env->make('admin.artwork.requests.partials.detail-header', [
            'request' => $request,
            'designerOperator' => $designerOperator,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.artwork.requests.partials.workflow-panel', [
            'request' => $request,
            'fromDesk' => false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if (isset($component)) { $__componentOriginal0de0e52b643095bf2659e655794f27e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0de0e52b643095bf2659e655794f27e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.artwork-preview-lightbox','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.artwork-preview-lightbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <?php echo $__env->make('admin.artwork.requests.partials.details-grid', ['request' => $request], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('admin.artwork.requests.partials.versions-panel', ['request' => $request, 'fromDesk' => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <?php echo $__env->make('admin.artwork.requests.partials.reference-files-panel', ['request' => $request, 'fromDesk' => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('admin.artwork.requests.partials.comments-panel', ['request' => $request, 'fromDesk' => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0de0e52b643095bf2659e655794f27e9)): ?>
<?php $attributes = $__attributesOriginal0de0e52b643095bf2659e655794f27e9; ?>
<?php unset($__attributesOriginal0de0e52b643095bf2659e655794f27e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0de0e52b643095bf2659e655794f27e9)): ?>
<?php $component = $__componentOriginal0de0e52b643095bf2659e655794f27e9; ?>
<?php unset($__componentOriginal0de0e52b643095bf2659e655794f27e9); ?>
<?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/artwork/requests/show.blade.php ENDPATH**/ ?>