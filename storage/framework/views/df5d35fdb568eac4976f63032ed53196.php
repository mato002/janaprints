<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Posting rules'),'breadcrumbs' => [['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Posting rules')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php
        $bootstrap = [
            'routes' => [
                'show' => route('admin.accounting.posting.rules.show', ['rule' => '__ID__']),
                'index' => route('admin.accounting.posting.rules.index'),
            ],
            'canAudit' => $canAudit,
            'activeFilters' => $activeFilters,
        ];
    ?>

    <div
        class="posting-rules-workspace min-w-0"
        x-data="postingRulesWorkspace(<?php echo \Illuminate\Support\Js::from($bootstrap)->toHtml() ?>)"
        @keydown.escape.window="closeDrawer()"
    >
        <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Posting rules'),'description' => __('Complete visibility into how business events become journal entries.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Posting rules')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Complete visibility into how business events become journal entries.'))]); ?>
            <?php if($canManage ?? false): ?>
                <a href="<?php echo e(route('admin.accounting.posting.rules.create')); ?>" class="erp-btn-primary"><?php echo e(__('Create rule')); ?></a>
            <?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $attributes = $__attributesOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__attributesOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $component = $__componentOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__componentOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>

        
        <div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-6">
            <?php if (isset($component)) { $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.stat-card','data' => ['label' => __('Total rules'),'value' => $summary['total']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Total rules')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['total'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $attributes = $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $component = $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.stat-card','data' => ['label' => __('Active rules'),'value' => $summary['active']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Active rules')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['active'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $attributes = $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $component = $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.stat-card','data' => ['label' => __('Auto post rules'),'value' => $summary['auto_post']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Auto post rules')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['auto_post'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $attributes = $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $component = $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.stat-card','data' => ['label' => __('Manual rules'),'value' => $summary['manual']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Manual rules')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['manual'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $attributes = $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $component = $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.stat-card','data' => ['label' => __('Disabled rules'),'value' => $summary['disabled']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Disabled rules')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['disabled'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $attributes = $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $component = $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
            <div class="rounded-lg border border-erp-border bg-erp-card p-4 shadow-card <?php echo e($summary['validation_errors'] > 0 ? 'ring-1 ring-red-200' : ''); ?>">
                <p class="text-card-title text-erp-primary"><?php echo e(__('Validation errors')); ?></p>
                <p class="mt-1.5 text-card-value tabular-nums <?php echo e($summary['validation_errors'] > 0 ? 'text-red-700' : 'text-erp-primary'); ?>">
                    <?php echo e($summary['validation_errors']); ?>

                </p>
            </div>
        </div>

        
        <div class="erp-card mb-4">
            <h2 class="erp-card-title"><?php echo e(__('Rules by module')); ?></h2>
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
                <?php $__currentLoopData = $moduleSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $isActive = ($activeFilters['module'] ?? null) === $module['module'];
                        $linkFilters = $activeFilters;
                        if ($isActive) {
                            unset($linkFilters['module']);
                        } else {
                            $linkFilters['module'] = $module['module'];
                        }
                    ?>
                    <a
                        href="<?php echo e(route('admin.accounting.posting.rules.index', $linkFilters)); ?>"
                        data-turbo-frame="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::turboFrame()); ?>"
                        class="posting-module-card rounded-lg border px-3 py-2.5 transition-colors <?php echo e($isActive ? 'border-erp-accent bg-erp-accent/5 ring-1 ring-erp-accent/30' : 'border-erp-border bg-white hover:border-erp-accent/40'); ?>"
                    >
                        <p class="text-sm font-semibold text-erp-primary"><?php echo e($module['label']); ?></p>
                        <dl class="mt-2 grid grid-cols-3 gap-1 text-[10px]">
                            <div>
                                <dt class="text-slate-500"><?php echo e(__('Active')); ?></dt>
                                <dd class="font-semibold tabular-nums text-emerald-700"><?php echo e($module['active']); ?></dd>
                            </div>
                            <div>
                                <dt class="text-slate-500"><?php echo e(__('Off')); ?></dt>
                                <dd class="font-semibold tabular-nums text-slate-600"><?php echo e($module['disabled']); ?></dd>
                            </div>
                            <div>
                                <dt class="text-slate-500"><?php echo e(__('Errors')); ?></dt>
                                <dd class="font-semibold tabular-nums <?php echo e($module['validation_errors'] > 0 ? 'text-red-700' : 'text-slate-600'); ?>"><?php echo e($module['validation_errors']); ?></dd>
                            </div>
                        </dl>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <form method="GET" action="<?php echo e(route('admin.accounting.posting.rules.index')); ?>" x-data="erpIndexFilterForm()" @change="onFieldChange($event)" class="erp-card mb-4" data-turbo-frame="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::turboFrame()); ?>">
            <div class="flex flex-wrap items-center gap-2 p-4">
                <input id="filter-q" type="search" name="q" value="<?php echo e($activeFilters['q'] ?? ''); ?>" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="<?php echo e(__('Event, name, template…')); ?>" aria-label="<?php echo e(__('Search')); ?>" data-erp-auto-search>
                <select id="filter-module" name="module" class="erp-toolbar-select" aria-label="<?php echo e(__('Module')); ?>">
                    <option value=""><?php echo e(__('All modules')); ?></option>
                    <?php $__currentLoopData = $filterOptions['modules']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option['value']); ?>" <?php if(($activeFilters['module'] ?? '') === $option['value']): echo 'selected'; endif; ?>><?php echo e($option['label']); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select id="filter-status" name="status" class="erp-toolbar-select" aria-label="<?php echo e(__('Status')); ?>">
                    <option value=""><?php echo e(__('All statuses')); ?></option>
                    <?php $__currentLoopData = $filterOptions['statuses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option['value']); ?>" <?php if(($activeFilters['status'] ?? '') === $option['value']): echo 'selected'; endif; ?>><?php echo e($option['label']); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select id="filter-auto-post" name="auto_post" class="erp-toolbar-select" aria-label="<?php echo e(__('Auto post')); ?>">
                    <option value=""><?php echo e(__('Any')); ?></option>
                    <?php $__currentLoopData = $filterOptions['auto_post']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option['value']); ?>" <?php if(($activeFilters['auto_post'] ?? '') === $option['value']): echo 'selected'; endif; ?>><?php echo e($option['label']); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select id="filter-validation" name="validation_status" class="erp-toolbar-select" aria-label="<?php echo e(__('Validation status')); ?>">
                    <option value=""><?php echo e(__('Any')); ?></option>
                    <?php $__currentLoopData = $filterOptions['validation_statuses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option['value']); ?>" <?php if(($activeFilters['validation_status'] ?? '') === $option['value']): echo 'selected'; endif; ?>><?php echo e($option['label']); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select id="filter-rule-type" name="rule_type" class="erp-toolbar-select" aria-label="<?php echo e(__('Rule type')); ?>">
                    <option value=""><?php echo e(__('Any')); ?></option>
                    <?php $__currentLoopData = $filterOptions['rule_types']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option['value']); ?>" <?php if(($activeFilters['rule_type'] ?? '') === $option['value']): echo 'selected'; endif; ?>><?php echo e($option['label']); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <input id="filter-created-from" type="date" name="created_from" value="<?php echo e($activeFilters['created_from'] ?? ''); ?>" class="erp-toolbar-input" aria-label="<?php echo e(__('Created from')); ?>">
                <input id="filter-created-to" type="date" name="created_to" value="<?php echo e($activeFilters['created_to'] ?? ''); ?>" class="erp-toolbar-input" aria-label="<?php echo e(__('Created to')); ?>">
                <input id="filter-updated-from" type="date" name="updated_from" value="<?php echo e($activeFilters['updated_from'] ?? ''); ?>" class="erp-toolbar-input" aria-label="<?php echo e(__('Updated from')); ?>">
                <input id="filter-updated-to" type="date" name="updated_to" value="<?php echo e($activeFilters['updated_to'] ?? ''); ?>" class="erp-toolbar-input" aria-label="<?php echo e(__('Updated to')); ?>">
                <a href="<?php echo e(route('admin.accounting.posting.rules.index')); ?>" data-turbo-frame="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::turboFrame()); ?>" class="erp-btn-ghost py-1 text-xs text-slate-500"><?php echo e(__('Reset')); ?></a>
            </div>
            <?php if($activeFilters !== []): ?>
                <div class="mt-3 flex flex-wrap gap-1.5">
                    <?php $__currentLoopData = $activeFilters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="erp-filter-pill erp-filter-pill--active text-xs"><?php echo e(str($key)->replace('_', ' ')->title()); ?>: <?php echo e($value); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </form>

        
        <?php if (isset($component)) { $__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.data-table','data' => ['searchable' => false,'filterable' => false,'exportRoute' => 'admin.accounting.exports','exportRouteParams' => ['listing' => 'posting-rules'],'exportQuery' => request()->query(),'formatInPath' => true,'exportFilename' => 'posting-rules','class' => 'erp-table--grid']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.data-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['searchable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'filterable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'export-route' => 'admin.accounting.exports','export-route-params' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['listing' => 'posting-rules']),'export-query' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->query()),'format-in-path' => true,'export-filename' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('posting-rules'),'class' => 'erp-table--grid']); ?>
             <?php $__env->slot('head', null, []); ?> 
                <tr>
                    <th scope="col"><?php echo e(__('Event / Rule')); ?></th>
                    <th scope="col"><?php echo e(__('Module')); ?></th>
                    <th scope="col"><?php echo e(__('Template')); ?></th>
                    <th scope="col"><?php echo e(__('Auto post')); ?></th>
                    <th scope="col"><?php echo e(__('Status')); ?></th>
                    <th scope="col"><?php echo e(__('Validation status')); ?></th>
                    <th scope="col" class="w-12"><span class="sr-only"><?php echo e(__('Actions')); ?></span></th>
                </tr>
             <?php $__env->endSlot(); ?>
             <?php $__env->slot('body', null, []); ?> 
                <?php $__empty_1 = true; $__currentLoopData = $rules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $validation = $validations[$rule->id];
                    ?>
                    <tr
                        class="cursor-pointer hover:bg-erp-page/80"
                        @click="openDrawer(<?php echo e($rule->id); ?>)"
                    >
                        <td>
                            <span class="font-mono text-xs text-slate-600"><?php echo e($rule->event_code); ?></span>
                            <div class="text-sm font-medium text-erp-primary"><?php echo e($rule->name); ?></div>
                            <?php if($rule->is_system): ?>
                                <span class="erp-badge mt-0.5"><?php echo e(__('System')); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-sm"><?php echo e($rule->module->label()); ?></td>
                        <td class="text-sm">
                            <?php if($rule->template): ?>
                                <a
                                    href="<?php echo e(route('admin.accounting.posting.templates.show', $rule->template)); ?>"
                                    data-turbo-frame="erp-main"
                                    data-turbo-action="advance"
                                    class="text-erp-accent"
                                    @click.stop
                                ><?php echo e($rule->template->code); ?></a>
                            <?php else: ?>
                                <span class="text-red-600">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-sm"><?php echo e($rule->auto_post ? __('Yes') : __('No')); ?></td>
                        <td>
                            <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => $rule->is_active ? 'success' : 'neutral']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rule->is_active ? 'success' : 'neutral')]); ?>
                                <?php echo e($rule->is_active ? __('Active') : __('Inactive')); ?>

                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
                        </td>
                        <td>
                            <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => $validation->badgeVariant()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($validation->badgeVariant())]); ?>
                                <?php echo e($validation->label()); ?>

                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
                        </td>
                        <td @click.stop>
                            <?php if (isset($component)) { $__componentOriginalb5a89013017505cf4d4d69115d724d07 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb5a89013017505cf4d4d69115d724d07 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-row-actions','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-row-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                                <?php if (isset($component)) { $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-row-action','data' => ['type' => 'button','@click' => 'openDrawer('.e($rule->id).')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','@click' => 'openDrawer('.e($rule->id).')']); ?>
                                    <?php echo e(__('View details')); ?>

                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0)): ?>
<?php $attributes = $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0; ?>
<?php unset($__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0)): ?>
<?php $component = $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0; ?>
<?php unset($__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0); ?>
<?php endif; ?>
                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb5a89013017505cf4d4d69115d724d07)): ?>
<?php $attributes = $__attributesOriginalb5a89013017505cf4d4d69115d724d07; ?>
<?php unset($__attributesOriginalb5a89013017505cf4d4d69115d724d07); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb5a89013017505cf4d4d69115d724d07)): ?>
<?php $component = $__componentOriginalb5a89013017505cf4d4d69115d724d07; ?>
<?php unset($__componentOriginalb5a89013017505cf4d4d69115d724d07); ?>
<?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7"><?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'cog','title' => __('No posting rules match your filters')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'cog','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No posting rules match your filters'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?></td></tr>
                <?php endif; ?>
             <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0)): ?>
<?php $attributes = $__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0; ?>
<?php unset($__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0)): ?>
<?php $component = $__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0; ?>
<?php unset($__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0); ?>
<?php endif; ?>

        
        <div x-show="drawerOpen" x-cloak class="fixed inset-0 z-40 bg-erp-primary/40" @click="closeDrawer()" aria-hidden="true"></div>
        <aside
            x-show="drawerOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            class="posting-rule-drawer"
            role="dialog"
            aria-modal="true"
            aria-labelledby="posting-rule-drawer-title"
        >
            <div class="flex items-start justify-between border-b border-erp-border px-4 py-3">
                <div class="min-w-0 pr-3">
                    <p class="font-mono text-[11px] text-erp-accent" x-text="rule?.event_code"></p>
                    <h2 id="posting-rule-drawer-title" class="truncate text-base font-semibold text-erp-primary" x-text="rule?.name"></h2>
                    <p class="mt-0.5 text-xs text-slate-500" x-text="rule?.event_label"></p>
                </div>
                <button type="button" @click="closeDrawer()" class="shrink-0 rounded-lg p-1.5 text-slate-500 hover:bg-erp-page" aria-label="<?php echo e(__('Close')); ?>">
                    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'x-mark','class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'h-5 w-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                </button>
            </div>

            <div x-show="drawerLoading" class="p-4 text-sm text-slate-500"><?php echo e(__('Loading rule details…')); ?></div>

            <div x-show="rule && !drawerLoading" class="flex flex-1 flex-col overflow-y-auto">
                
                <section class="border-b border-erp-border p-4">
                    <h3 class="posting-drawer-section-title"><?php echo e(__('Rule details')); ?></h3>
                    <dl class="mt-2 grid gap-2 text-sm sm:grid-cols-2">
                        <div><dt class="text-slate-500"><?php echo e(__('Module')); ?></dt><dd x-text="rule?.module_label"></dd></div>
                        <div><dt class="text-slate-500"><?php echo e(__('Template')); ?></dt><dd><a :href="rule?.template?.url" data-turbo-frame="erp-main" data-turbo-action="advance" class="text-erp-accent" x-text="rule?.template?.code" x-show="rule?.template"></a><span x-show="!rule?.template">—</span></dd></div>
                        <div><dt class="text-slate-500"><?php echo e(__('Auto post')); ?></dt><dd x-text="rule?.auto_post_label"></dd></div>
                        <div><dt class="text-slate-500"><?php echo e(__('Status')); ?></dt><dd x-text="rule?.status_label"></dd></div>
                        <div><dt class="text-slate-500"><?php echo e(__('Rule type')); ?></dt><dd x-text="rule?.rule_type_label"></dd></div>
                        <div><dt class="text-slate-500"><?php echo e(__('Priority')); ?></dt><dd x-text="rule?.priority"></dd></div>
                        <div><dt class="text-slate-500"><?php echo e(__('Created by')); ?></dt><dd x-text="rule?.created_by?.name ?? '—'"></dd></div>
                        <div><dt class="text-slate-500"><?php echo e(__('Updated by')); ?></dt><dd x-text="rule?.updated_by?.name ?? '—'"></dd></div>
                        <div><dt class="text-slate-500"><?php echo e(__('Created')); ?></dt><dd x-text="formatDate(rule?.created_at)"></dd></div>
                        <div><dt class="text-slate-500"><?php echo e(__('Updated')); ?></dt><dd x-text="formatDate(rule?.updated_at)"></dd></div>
                    </dl>
                </section>

                
                <section class="border-b border-erp-border p-4">
                    <h3 class="posting-drawer-section-title"><?php echo e(__('Rule validation')); ?></h3>
                    <p class="mt-1 text-sm">
                        <span class="erp-badge" :class="validationBadgeClass(rule?.validation?.status)" x-text="rule?.validation?.label"></span>
                        <span class="ml-2 text-xs text-slate-500"><?php echo e(__('Last checked')); ?>: <span x-text="formatDate(rule?.validation?.validated_at)"></span></span>
                    </p>
                    <ul class="mt-2 space-y-1" x-show="rule?.validation?.issues?.length">
                        <template x-for="(issue, idx) in rule?.validation?.issues ?? []" :key="idx">
                            <li class="rounded-md border border-erp-border px-2.5 py-1.5 text-xs" :class="issue.level === 'error' ? 'border-red-200 bg-red-50/50 text-red-800' : 'border-amber-200 bg-amber-50/50 text-amber-900'" x-text="issue.message"></li>
                        </template>
                    </ul>
                </section>

                
                <section class="border-b border-erp-border p-4">
                    <h3 class="posting-drawer-section-title"><?php echo e(__('Posting workflow')); ?></h3>
                    <ol class="posting-workflow mt-3 space-y-0">
                        <template x-for="(step, idx) in rule?.workflow?.steps ?? []" :key="step.key">
                            <li class="posting-workflow__step">
                                <div class="posting-workflow__node">
                                    <span class="posting-workflow__label" x-text="step.label"></span>
                                    <span class="posting-workflow__value" x-text="step.value"></span>
                                    <span class="posting-workflow__code font-mono text-[10px] text-slate-400" x-text="step.code" x-show="step.code"></span>
                                </div>
                                <div class="posting-workflow__arrow" x-show="idx < (rule?.workflow?.steps?.length ?? 0) - 1" aria-hidden="true">↓</div>
                            </li>
                        </template>
                    </ol>
                </section>

                
                <section class="border-b border-erp-border p-4">
                    <h3 class="posting-drawer-section-title"><?php echo e(__('Account mapping')); ?></h3>
                    <div class="mt-2 space-y-2">
                        <template x-for="mapping in rule?.account_mappings ?? []" :key="mapping.line_number">
                            <div class="rounded-lg border border-erp-border px-3 py-2 text-sm">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-medium capitalize" x-text="mapping.side_label"></span>
                                    <span class="text-[10px] text-slate-500" x-text="'#' + mapping.line_number"></span>
                                </div>
                                <template x-if="mapping.account">
                                    <dl class="mt-2 grid gap-1 text-xs sm:grid-cols-2">
                                        <div><dt class="text-slate-500"><?php echo e(__('Code')); ?></dt><dd class="font-mono" x-text="mapping.account.code"></dd></div>
                                        <div><dt class="text-slate-500"><?php echo e(__('Name')); ?></dt><dd x-text="mapping.account.name"></dd></div>
                                        <div><dt class="text-slate-500"><?php echo e(__('Type')); ?></dt><dd x-text="mapping.account.type ?? '—'"></dd></div>
                                        <div><dt class="text-slate-500"><?php echo e(__('Normal balance')); ?></dt><dd x-text="mapping.account.normal_balance ?? '—'"></dd></div>
                                        <div><dt class="text-slate-500"><?php echo e(__('Status')); ?></dt><dd x-text="mapping.account.status_label"></dd></div>
                                    </dl>
                                </template>
                                <p x-show="!mapping.account" class="mt-1 text-xs text-amber-700" x-text="mapping.resolution_note"></p>
                                <p class="mt-1 text-[10px] text-slate-400" x-text="mapping.resolver"></p>
                            </div>
                        </template>
                    </div>
                </section>

                
                <section class="border-b border-erp-border p-4">
                    <h3 class="posting-drawer-section-title"><?php echo e(__('Journal preview')); ?></h3>
                    <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Expected structure when the event fires. No journal is created.')); ?></p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Debit lines')); ?></h4>
                            <ul class="mt-1 space-y-1">
                                <template x-for="line in rule?.journal_preview?.debit_lines ?? []" :key="'d-' + line.line_number">
                                    <li class="rounded-md border border-erp-border px-2 py-1.5 text-xs">
                                        <span class="font-mono text-erp-accent" x-text="line.account_code"></span>
                                        <span class="ml-1" x-text="line.account_name"></span>
                                        <span class="mt-0.5 block text-[10px] text-slate-500" x-text="line.amount_source"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Credit lines')); ?></h4>
                            <ul class="mt-1 space-y-1">
                                <template x-for="line in rule?.journal_preview?.credit_lines ?? []" :key="'c-' + line.line_number">
                                    <li class="rounded-md border border-erp-border px-2 py-1.5 text-xs">
                                        <span class="font-mono text-erp-accent" x-text="line.account_code"></span>
                                        <span class="ml-1" x-text="line.account_name"></span>
                                        <span class="mt-0.5 block text-[10px] text-slate-500" x-text="line.amount_source"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </section>

                
                <section class="p-4" x-show="canAudit && rule?.audit">
                    <h3 class="posting-drawer-section-title"><?php echo e(__('Audit & usage')); ?></h3>
                    <dl class="mt-2 grid gap-2 text-sm sm:grid-cols-2">
                        <div><dt class="text-slate-500"><?php echo e(__('Last validation')); ?></dt><dd x-text="formatDate(rule?.audit?.last_validation_at)"></dd></div>
                        <div><dt class="text-slate-500"><?php echo e(__('Last usage')); ?></dt><dd x-text="formatDate(rule?.audit?.last_usage_at) ?? '—'"></dd></div>
                        <div><dt class="text-slate-500"><?php echo e(__('Last journal generated')); ?></dt><dd x-text="formatDate(rule?.audit?.last_journal_at) ?? '—'"></dd></div>
                        <div><dt class="text-slate-500"><?php echo e(__('Last posted')); ?></dt><dd x-text="formatDate(rule?.audit?.last_posted_at) ?? '—'"></dd></div>
                        <div><dt class="text-slate-500"><?php echo e(__('Total journals')); ?></dt><dd class="tabular-nums" x-text="rule?.audit?.total_journals ?? 0"></dd></div>
                        <div><dt class="text-slate-500"><?php echo e(__('Posted journals')); ?></dt><dd class="tabular-nums" x-text="rule?.audit?.posted_journals ?? 0"></dd></div>
                        <div class="sm:col-span-2"><dt class="text-slate-500"><?php echo e(__('Total amount posted')); ?></dt><dd class="font-mono font-semibold tabular-nums" x-text="rule?.audit?.total_amount_posted ?? '0.00'"></dd></div>
                    </dl>
                </section>
            </div>
        </aside>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\accounting\posting\rules\index.blade.php ENDPATH**/ ?>