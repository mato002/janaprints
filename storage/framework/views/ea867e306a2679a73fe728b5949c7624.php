<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $shell['title'],'compactWorkspace' => true] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if(! empty($showWebsiteCmsSupport)): ?>
        <?php echo $__env->make('admin.website.partials.cms-support-panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal389c6c7326277510c33cc8ff1022a5f7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal389c6c7326277510c33cc8ff1022a5f7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.workspace-shell','data' => ['title' => $shell['title'],'description' => $shell['description'],'primaryWorkspaces' => $shell['primary_workspaces'],'activePrimary' => $shell['active_primary'],'secondaryWorkspaces' => $shell['secondary_workspaces'],'activeSecondary' => $shell['active_secondary'],'secondaryToolbarActions' => $shell['secondary_toolbar_actions'] ?? [],'contentUrl' => $shell['content_url']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.workspace-shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($shell['title']),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($shell['description']),'primary-workspaces' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($shell['primary_workspaces']),'active-primary' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($shell['active_primary']),'secondary-workspaces' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($shell['secondary_workspaces']),'active-secondary' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($shell['active_secondary']),'secondary-toolbar-actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($shell['secondary_toolbar_actions'] ?? []),'content-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($shell['content_url'])]); ?>
         <?php $__env->slot('search', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginald764208fd114615406565a97fe01ebfd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald764208fd114615406565a97fe01ebfd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.workspace-search-bar','data' => ['moduleTitle' => $shell['title'],'moduleKey' => $moduleKey]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.workspace-search-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['module-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($shell['title']),'module-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($moduleKey)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald764208fd114615406565a97fe01ebfd)): ?>
<?php $attributes = $__attributesOriginald764208fd114615406565a97fe01ebfd; ?>
<?php unset($__attributesOriginald764208fd114615406565a97fe01ebfd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald764208fd114615406565a97fe01ebfd)): ?>
<?php $component = $__componentOriginald764208fd114615406565a97fe01ebfd; ?>
<?php unset($__componentOriginald764208fd114615406565a97fe01ebfd); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>
        <?php if(empty($shell['content_url']) && empty($shell['secondary_workspaces'])): ?>
            <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => ''.e($shell['icon'] ?? 'inbox').'','title' => __('Select a workspace'),'description' => __('Choose a workspace tab above to open operational content.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => ''.e($shell['icon'] ?? 'inbox').'','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Select a workspace')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Choose a workspace tab above to open operational content.'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
        <?php elseif(empty($shell['content_url'])): ?>
            <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => ''.e($shell['active_secondary']['icon'] ?? 'inbox').'','title' => $shell['active_secondary']['label'] ?? __('Coming soon'),'description' => $shell['active_secondary']['description'] ?? __('This workspace is not available yet.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => ''.e($shell['active_secondary']['icon'] ?? 'inbox').'','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($shell['active_secondary']['label'] ?? __('Coming soon')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($shell['active_secondary']['description'] ?? __('This workspace is not available yet.'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal389c6c7326277510c33cc8ff1022a5f7)): ?>
<?php $attributes = $__attributesOriginal389c6c7326277510c33cc8ff1022a5f7; ?>
<?php unset($__attributesOriginal389c6c7326277510c33cc8ff1022a5f7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal389c6c7326277510c33cc8ff1022a5f7)): ?>
<?php $component = $__componentOriginal389c6c7326277510c33cc8ff1022a5f7; ?>
<?php unset($__componentOriginal389c6c7326277510c33cc8ff1022a5f7); ?>
<?php endif; ?>
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
<?php /**PATH C:\Users\HP\Desktop\Newfolder\janaprints\resources\views/admin/workspaces/module-desk.blade.php ENDPATH**/ ?>