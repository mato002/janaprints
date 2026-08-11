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
        $header = $workspace['header'];
        $next = $workspace['next_action'];
        $score = $workspace['lead_score'];
    ?>

    <div
        x-data="qr360PrintingIntelligence({
            summary: <?php echo \Illuminate\Support\Js::from($workspace['printing_intelligence']['summary'] ?? null)->toHtml() ?>,
            modalUrl: <?php echo \Illuminate\Support\Js::from(route('admin.public-quote-requests.printing-analysis.modal', [$quoteRequest, $artworkFileId]))->toHtml() ?>,
            runUrl: <?php echo \Illuminate\Support\Js::from(route('admin.public-quote-requests.printing-analysis.run', [$quoteRequest, $artworkFileId]))->toHtml() ?>,
            rerunUrl: <?php echo \Illuminate\Support\Js::from(route('admin.public-quote-requests.printing-analysis.rerun', [$quoteRequest, $artworkFileId]))->toHtml() ?>,
            applyUrl: <?php echo \Illuminate\Support\Js::from(route('admin.public-quote-requests.printing-analysis.apply-quotation', [$quoteRequest, $artworkFileId]))->toHtml() ?>,
            activeArtwork: <?php echo \Illuminate\Support\Js::from($workspace['artwork_files'][0]['id'] ?? 'primary')->toHtml() ?>,
        })"
    >
        <?php if (isset($component)) { $__componentOriginal3a04fe7abf854a2aabf6494f7e646a9c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a04fe7abf854a2aabf6494f7e646a9c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.record-workspace.shell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.record-workspace.shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
             <?php $__env->slot('header', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginal0521626098a0ef1191262ef00e69cdb5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0521626098a0ef1191262ef00e69cdb5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.record-workspace.header','data' => ['eyebrow' => $header['reference'],'backUrl' => route('admin.public-quote-requests.index'),'backLabel' => __('Quote Requests'),'title' => $header['customer_name'],'subtitle' => $header['company'] ?: __('Individual / no company'),'meta' => array_values(array_filter([
                        $header['service'],
                        $header['quantity'] !== '—' ? $header['quantity'].' '.__('units') : null,
                        $header['submitted_at'],
                    ])),'metrics' => [
                        ['label' => __('Expected value'), 'value' => $header['expected_value']],
                        ['label' => __('Assigned'), 'value' => $header['assigned_to']],
                    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.record-workspace.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['eyebrow' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($header['reference']),'back-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.public-quote-requests.index')),'back-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Quote Requests')),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($header['customer_name']),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($header['company'] ?: __('Individual / no company')),'meta' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(array_values(array_filter([
                        $header['service'],
                        $header['quantity'] !== '—' ? $header['quantity'].' '.__('units') : null,
                        $header['submitted_at'],
                    ]))),'metrics' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                        ['label' => __('Expected value'), 'value' => $header['expected_value']],
                        ['label' => __('Assigned'), 'value' => $header['assigned_to']],
                    ])]); ?>
                     <?php $__env->slot('badges', null, []); ?> 
                        <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => $header['status_variant']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($header['status_variant'])]); ?><?php echo e($header['status_label']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
                        <span class="rw-score rw-score--<?php echo e($score['variant']); ?>"><?php echo e($score['label']); ?></span>
                        <span class="rw-stars" title="<?php echo e($score['hint']); ?>"><?php echo e($score['stars']); ?></span>
                     <?php $__env->endSlot(); ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0521626098a0ef1191262ef00e69cdb5)): ?>
<?php $attributes = $__attributesOriginal0521626098a0ef1191262ef00e69cdb5; ?>
<?php unset($__attributesOriginal0521626098a0ef1191262ef00e69cdb5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0521626098a0ef1191262ef00e69cdb5)): ?>
<?php $component = $__componentOriginal0521626098a0ef1191262ef00e69cdb5; ?>
<?php unset($__componentOriginal0521626098a0ef1191262ef00e69cdb5); ?>
<?php endif; ?>
             <?php $__env->endSlot(); ?>

             <?php $__env->slot('workflow', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginala3520277e72cae670812b60faad05e32 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala3520277e72cae670812b60faad05e32 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.record-workspace.workflow','data' => ['steps' => $workspace['workflow']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.record-workspace.workflow'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['steps' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($workspace['workflow'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala3520277e72cae670812b60faad05e32)): ?>
<?php $attributes = $__attributesOriginala3520277e72cae670812b60faad05e32; ?>
<?php unset($__attributesOriginala3520277e72cae670812b60faad05e32); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala3520277e72cae670812b60faad05e32)): ?>
<?php $component = $__componentOriginala3520277e72cae670812b60faad05e32; ?>
<?php unset($__componentOriginala3520277e72cae670812b60faad05e32); ?>
<?php endif; ?>
             <?php $__env->endSlot(); ?>

             <?php $__env->slot('actions', null, []); ?> 
                <?php echo $__env->make('admin.customer-service.quote-requests.workspace.action-bar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
             <?php $__env->endSlot(); ?>

             <?php $__env->slot('main', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginala963937ab212deff58683767e6c82aa7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala963937ab212deff58683767e6c82aa7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.record-workspace.next-action','data' => ['label' => $next['label'],'hint' => $next['hint'],'tone' => $next['tone'],'when' => $next['when'],'reasons' => $next['reasons']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.record-workspace.next-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($next['label']),'hint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($next['hint']),'tone' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($next['tone']),'when' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($next['when']),'reasons' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($next['reasons'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala963937ab212deff58683767e6c82aa7)): ?>
<?php $attributes = $__attributesOriginala963937ab212deff58683767e6c82aa7; ?>
<?php unset($__attributesOriginala963937ab212deff58683767e6c82aa7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala963937ab212deff58683767e6c82aa7)): ?>
<?php $component = $__componentOriginala963937ab212deff58683767e6c82aa7; ?>
<?php unset($__componentOriginala963937ab212deff58683767e6c82aa7); ?>
<?php endif; ?>

                <div class="rw__work-grid">
                    <?php echo $__env->make('admin.customer-service.quote-requests.workspace.timeline', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div class="rw__work-side">
                        <?php echo $__env->make('admin.customer-service.quote-requests.workspace.snapshot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php echo $__env->make('admin.customer-service.quote-requests.workspace.artwork', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php echo $__env->make('admin.customer-service.quote-requests.workspace.printing-intelligence-panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                </div>

                <?php echo $__env->make('admin.customer-service.quote-requests.workspace.collaboration', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('admin.customer-service.quote-requests.workspace.sales-review', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
             <?php $__env->endSlot(); ?>

             <?php $__env->slot('rail', null, []); ?> 
                <?php echo $__env->make('admin.customer-service.quote-requests.workspace.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
             <?php $__env->endSlot(); ?>

             <?php $__env->slot('modals', null, []); ?> 
                <?php echo $__env->make('admin.customer-service.quote-requests.workspace.artwork-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('admin.customer-service.quote-requests.workspace.printing-intelligence-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
             <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3a04fe7abf854a2aabf6494f7e646a9c)): ?>
<?php $attributes = $__attributesOriginal3a04fe7abf854a2aabf6494f7e646a9c; ?>
<?php unset($__attributesOriginal3a04fe7abf854a2aabf6494f7e646a9c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3a04fe7abf854a2aabf6494f7e646a9c)): ?>
<?php $component = $__componentOriginal3a04fe7abf854a2aabf6494f7e646a9c; ?>
<?php unset($__componentOriginal3a04fe7abf854a2aabf6494f7e646a9c); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/customer-service/quote-requests/show.blade.php ENDPATH**/ ?>