<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Quote Request').' '.$workspace['reference'],'breadcrumbs' => [
        ['label' => __('Commercial'), 'url' => route('admin.workspaces.commercial')],
        ['label' => __('Customer Service'), 'url' => route('admin.workspaces.commercial.section', 'customer-service')],
        ['label' => __('Quote Requests'), 'url' => route('admin.public-quote-requests.index')],
        ['label' => $workspace['reference']],
    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php
        $artworkFileId = $workspace['printing_intelligence']['artwork_file_id'] ?? 'primary';
    ?>

    <div
        class="qr-360"
        x-data="qr360PrintingIntelligence({
            summary: <?php echo \Illuminate\Support\Js::from($workspace['printing_intelligence']['summary'] ?? null)->toHtml() ?>,
            modalUrl: <?php echo \Illuminate\Support\Js::from(route('admin.public-quote-requests.printing-analysis.modal', [$quoteRequest, $artworkFileId]))->toHtml() ?>,
            runUrl: <?php echo \Illuminate\Support\Js::from(route('admin.public-quote-requests.printing-analysis.run', [$quoteRequest, $artworkFileId]))->toHtml() ?>,
            rerunUrl: <?php echo \Illuminate\Support\Js::from(route('admin.public-quote-requests.printing-analysis.rerun', [$quoteRequest, $artworkFileId]))->toHtml() ?>,
            applyUrl: <?php echo \Illuminate\Support\Js::from(route('admin.public-quote-requests.printing-analysis.apply-quotation', [$quoteRequest, $artworkFileId]))->toHtml() ?>,
            activeArtwork: <?php echo \Illuminate\Support\Js::from($workspace['artwork_files'][0]['id'] ?? 'primary')->toHtml() ?>,
        })"
    >
        <?php echo $__env->make('admin.customer-service.quote-requests.workspace.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            <div class="space-y-4 xl:col-span-8">
                <?php echo $__env->make('admin.customer-service.quote-requests.workspace.next-action', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('admin.customer-service.quote-requests.workspace.snapshot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('admin.customer-service.quote-requests.workspace.artwork', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('admin.customer-service.quote-requests.workspace.printing-intelligence-panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('admin.customer-service.quote-requests.workspace.action-bar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('admin.customer-service.quote-requests.workspace.sales-review', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('admin.customer-service.quote-requests.workspace.timeline', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('admin.customer-service.quote-requests.workspace.collaboration', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('admin.customer-service.quote-requests.workspace.conversion-tracker', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <div class="xl:col-span-4">
                <?php echo $__env->make('admin.customer-service.quote-requests.workspace.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>

        <?php echo $__env->make('admin.customer-service.quote-requests.workspace.artwork-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.customer-service.quote-requests.workspace.printing-intelligence-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/customer-service/quote-requests/show.blade.php ENDPATH**/ ?>