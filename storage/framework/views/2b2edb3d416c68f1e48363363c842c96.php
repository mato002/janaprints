<?php
    $focusPanel = $focusPanel ?? request('panel');
?>

<?php if (isset($component)) { $__componentOriginald3ad0f200dc20b794011e332a16c068d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3ad0f200dc20b794011e332a16c068d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal-form','data' => ['title' => $request->request_number,'maxWidth' => '5xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($request->request_number),'maxWidth' => '5xl']); ?>
    <div
        class="artwork-request-detail space-y-4"
        <?php if($focusPanel === 'versions'): ?>
            x-data
            x-init="$nextTick(() => document.getElementById('designer-desk-versions')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))"
        <?php endif; ?>
    >
        <?php echo $__env->make('admin.artwork.requests.partials.detail-header', [
            'request' => $request,
            'designerOperator' => true,
            'compact' => true,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.artwork.requests.partials.workflow-panel', [
            'request' => $request,
            'fromDesk' => true,
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
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <?php echo $__env->make('admin.artwork.requests.partials.details-grid', ['request' => $request], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('admin.artwork.requests.partials.versions-panel', [
                    'request' => $request,
                    'fromDesk' => true,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <?php echo $__env->make('admin.artwork.requests.partials.reference-files-panel', [
                    'request' => $request,
                    'fromDesk' => true,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('admin.artwork.requests.partials.comments-panel', [
                    'request' => $request,
                    'fromDesk' => true,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php if (isset($__attributesOriginald3ad0f200dc20b794011e332a16c068d)): ?>
<?php $attributes = $__attributesOriginald3ad0f200dc20b794011e332a16c068d; ?>
<?php unset($__attributesOriginald3ad0f200dc20b794011e332a16c068d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald3ad0f200dc20b794011e332a16c068d)): ?>
<?php $component = $__componentOriginald3ad0f200dc20b794011e332a16c068d; ?>
<?php unset($__componentOriginald3ad0f200dc20b794011e332a16c068d); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\artwork\desk\request-modal.blade.php ENDPATH**/ ?>