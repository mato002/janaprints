<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $summary = $controlCenter['summary'];
    $health = $controlCenter['health'];
    $auditRoute = Route::has('admin.security.audit.index') ? route('admin.security.audit.index') : null;
    $turboFrame = WorkspaceEmbed::turboFrame();
?>

<div
    x-data="formsControlCenter(<?php echo \Illuminate\Support\Js::from([
        'cards' => $controlCenter['cards'],
        'exportPayload' => $controlCenter['export_payload'],
        'auditUrl' => $auditRoute,
    ])->toHtml() ?>)"
    x-cloak
    class="forms-control-center w-full min-w-0"
>
    
    <div class="erp-stats-strip forms-kpi-strip mb-3 rounded-lg border border-erp-border bg-white px-3 py-2.5 shadow-sm">
        <span>
            <span class="text-slate-400"><?php echo e(__('Total Forms')); ?>:</span>
            <strong class="text-erp-primary"><?php echo e(number_format($summary['total_forms'])); ?></strong>
        </span>
        <span>
            <span class="text-slate-400"><?php echo e(__('Active Forms')); ?>:</span>
            <strong class="text-emerald-700"><?php echo e(number_format($summary['active_forms'])); ?></strong>
        </span>
        <span>
            <span class="text-slate-400"><?php echo e(__('Planned Forms')); ?>:</span>
            <strong class="text-slate-600"><?php echo e(number_format($summary['planned_forms'])); ?></strong>
        </span>
        <span>
            <span class="text-slate-400"><?php echo e(__('Total Managed Fields')); ?>:</span>
            <strong class="text-erp-accent"><?php echo e(number_format($summary['managed_fields'])); ?></strong>
        </span>
    </div>

    <?php echo $__env->make('admin.settings.forms.partials.governance-compliance', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="forms-control-layout">
        
        <aside class="forms-control-nav" aria-label="<?php echo e(__('Form categories')); ?>">
            <p class="mb-2 hidden text-[10px] font-semibold uppercase tracking-wide text-slate-400 lg:block"><?php echo e(__('Categories')); ?></p>

            <div class="forms-control-nav-list">
                <button
                    type="button"
                    class="forms-control-nav-item"
                    :class="activeCategory === 'all' ? 'forms-control-nav-item--active' : ''"
                    @click="setCategory('all')"
                >
                    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'view-grid','class' => 'h-4 w-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'view-grid','class' => 'h-4 w-4 shrink-0']); ?>
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
                    <span class="min-w-0 truncate"><?php echo e(__('All Forms')); ?></span>
                    <span class="ml-auto shrink-0 text-[10px] opacity-70"><?php echo e($summary['total_forms']); ?></span>
                </button>

                <?php $__currentLoopData = $controlCenter['categories']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button
                        type="button"
                        class="forms-control-nav-item"
                        :class="activeCategory === <?php echo \Illuminate\Support\Js::from($category['slug'])->toHtml() ?> ? 'forms-control-nav-item--active' : ''"
                        @click="setCategory(<?php echo \Illuminate\Support\Js::from($category['slug'])->toHtml() ?>)"
                        title="<?php echo e($category['description']); ?>"
                    >
                        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $category['icon'],'class' => 'h-4 w-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($category['icon']),'class' => 'h-4 w-4 shrink-0']); ?>
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
                        <span class="min-w-0 truncate"><?php echo e($category['label']); ?></span>
                        <span class="ml-auto shrink-0 text-[10px] opacity-70"><?php echo e($category['count']); ?></span>
                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </aside>

        
        <div class="forms-control-main min-w-0">
            <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="relative min-w-0 flex-1">
                    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'search','class' => 'pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','class' => 'pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400']); ?>
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
                    <input
                        type="search"
                        x-model="query"
                        class="erp-input w-full py-2 pl-8 text-sm"
                        placeholder="<?php echo e(__('Search forms, fields, or modules…')); ?>"
                        aria-label="<?php echo e(__('Search forms')); ?>"
                        autocomplete="off"
                    >
                </div>

                <div class="flex shrink-0 flex-wrap gap-2">
                    <button type="button" class="forms-quick-action" @click="exportConfiguration()">
                        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'download','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'download','class' => 'h-3.5 w-3.5']); ?>
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
                        <?php echo e(__('Export Configuration')); ?>

                    </button>
                    <button type="button" class="forms-quick-action" @click="importModalOpen = true">
                        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'archive','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'archive','class' => 'h-3.5 w-3.5']); ?>
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
                        <?php echo e(__('Import Configuration')); ?>

                    </button>
                    <button type="button" class="forms-quick-action" @click="auditForms()">
                        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'shield-check','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shield-check','class' => 'h-3.5 w-3.5']); ?>
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
                        <?php echo e(__('Audit Forms')); ?>

                    </button>
                </div>
            </div>

            <p
                x-show="auditMode"
                x-cloak
                class="mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900"
            >
                <?php echo e(__('Audit mode: showing forms with configuration governance issues only.')); ?>

                <button type="button" class="ml-2 font-semibold underline" @click="auditMode = false"><?php echo e(__('Clear')); ?></button>
            </p>

            <div class="forms-control-body">
                <div class="forms-control-cards min-w-0">
                    
                    <?php $__currentLoopData = $controlCenter['categories']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <section
                            class="mb-6"
                            x-show="sectionVisible(<?php echo \Illuminate\Support\Js::from($category['slug'])->toHtml() ?>)"
                            x-cloak
                        >
                            <div class="mb-3 flex items-center gap-2">
                                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $category['icon'],'class' => 'h-4 w-4 text-slate-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($category['icon']),'class' => 'h-4 w-4 text-slate-400']); ?>
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
                                <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e($category['label']); ?></h2>
                            </div>

                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                <?php $__currentLoopData = $controlCenter['active_cards']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($card['category_slug'] === $category['slug']): ?>
                                        <div x-show="cardVisible(<?php echo \Illuminate\Support\Js::from($card['id'])->toHtml() ?>)" x-cloak>
                                            <?php echo $__env->make('admin.settings.forms.partials.form-card', ['card' => $card], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </section>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    
                    <section class="mb-4" x-show="plannedSectionVisible()" x-cloak>
                        <div class="mb-3 flex items-center justify-between gap-2 border-t border-erp-border pt-4">
                            <div class="flex items-center gap-2">
                                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'clock','class' => 'h-4 w-4 text-slate-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clock','class' => 'h-4 w-4 text-slate-400']); ?>
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
                                <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Planned Forms')); ?></h2>
                            </div>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">
                                <?php echo e(number_format($summary['planned_forms'])); ?>

                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <?php $__currentLoopData = $controlCenter['planned_cards']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div x-show="cardVisible(<?php echo \Illuminate\Support\Js::from($card['id'])->toHtml() ?>)" x-cloak>
                                    <?php echo $__env->make('admin.settings.forms.partials.form-card', ['card' => $card], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </section>

                    <p
                        x-show="visibleCount === 0"
                        x-cloak
                        class="rounded-lg border border-dashed border-erp-border px-4 py-8 text-center text-sm text-slate-500"
                    >
                        <?php echo e(__('No forms match your search or filter.')); ?>

                    </p>
                </div>

                
                <aside class="forms-control-widgets space-y-3">
                    
                    <div id="forms-health-widget" class="rounded-xl border border-erp-border bg-white p-4 shadow-sm">
                        <div class="mb-3 flex items-center justify-between gap-2">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Configuration Health')); ?></h3>
                            <?php if($health['healthy']): ?>
                                <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                    <?php echo e(__('Healthy')); ?>

                                </span>
                            <?php else: ?>
                                <span class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-800 ring-1 ring-inset ring-amber-600/20">
                                    <?php echo e(__('Attention')); ?>

                                </span>
                            <?php endif; ?>
                        </div>

                        <dl class="space-y-2.5 text-xs">
                            <div class="flex items-center justify-between gap-2">
                                <dt class="text-slate-500"><?php echo e(__('Missing required fields')); ?></dt>
                                <dd class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'font-semibold tabular-nums',
                                    'text-amber-700' => $health['missing_required'] > 0,
                                    'text-slate-700' => $health['missing_required'] === 0,
                                ]); ?>"><?php echo e(number_format($health['missing_required'])); ?></dd>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <dt class="text-slate-500"><?php echo e(__('Hidden required fields')); ?></dt>
                                <dd class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'font-semibold tabular-nums',
                                    'text-red-700' => $health['hidden_required'] > 0,
                                    'text-slate-700' => $health['hidden_required'] === 0,
                                ]); ?>"><?php echo e(number_format($health['hidden_required'])); ?></dd>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <dt class="text-slate-500"><?php echo e(__('Inactive forms')); ?></dt>
                                <dd class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'font-semibold tabular-nums',
                                    'text-amber-700' => $health['inactive_forms'] > 0,
                                    'text-slate-700' => $health['inactive_forms'] === 0,
                                ]); ?>"><?php echo e(number_format($health['inactive_forms'])); ?></dd>
                            </div>
                            <div class="flex items-center justify-between gap-2 border-t border-erp-border/70 pt-2">
                                <dt class="font-medium text-erp-primary"><?php echo e(__('Governance issues')); ?></dt>
                                <dd class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'font-semibold tabular-nums',
                                    'text-red-700' => $health['governance_issues'] > 0,
                                    'text-emerald-700' => $health['governance_issues'] === 0,
                                ]); ?>"><?php echo e(number_format($health['governance_issues'])); ?></dd>
                            </div>
                        </dl>
                    </div>

                    
                    <div class="rounded-xl border border-erp-border bg-white p-4 shadow-sm">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Recently Modified')); ?></h3>

                        <?php if(count($controlCenter['recently_modified']) === 0): ?>
                            <p class="text-xs text-slate-400"><?php echo e(__('No configuration changes recorded yet.')); ?></p>
                        <?php else: ?>
                            <ul class="space-y-2">
                                <?php $__currentLoopData = $controlCenter['recently_modified']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>
                                        <a
                                            href="<?php echo e(WorkspaceEmbed::url($item['href'])); ?>"
                                            data-turbo-frame="<?php echo e($turboFrame); ?>"
                                            data-turbo-action="advance"
                                            class="group block rounded-lg border border-transparent px-2 py-1.5 transition-colors hover:border-erp-border hover:bg-erp-page/50"
                                        >
                                            <p class="truncate text-xs font-semibold text-erp-primary group-hover:text-erp-accent"><?php echo e($item['title']); ?></p>
                                            <p class="mt-0.5 truncate text-[10px] text-slate-400">
                                                <?php echo e($item['category_label']); ?> · <?php echo e($item['updated_label']); ?>

                                            </p>
                                        </a>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    
    <div
        x-show="importModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
        role="dialog"
        aria-modal="true"
        aria-labelledby="forms-import-title"
    >
        <div class="absolute inset-0 bg-slate-900/40" @click="importModalOpen = false"></div>
        <div class="relative w-full max-w-md rounded-xl border border-erp-border bg-white p-5 shadow-xl">
            <div class="mb-3 flex items-start justify-between gap-3">
                <div>
                    <h2 id="forms-import-title" class="text-sm font-semibold text-erp-primary"><?php echo e(__('Import Configuration')); ?></h2>
                    <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Upload a previously exported forms configuration snapshot.')); ?></p>
                </div>
                <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-erp-page hover:text-slate-600" @click="importModalOpen = false">
                    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'x-mark','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'h-4 w-4']); ?>
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

            <input
                type="file"
                accept="application/json,.json"
                class="erp-input w-full text-xs"
                @change="handleImportSelect($event)"
            >

            <p x-show="importMessage" x-text="importMessage" x-cloak class="mt-3 text-xs text-amber-800"></p>
            <p class="mt-2 text-[10px] text-slate-400"><?php echo e(__('Import applies server-side configuration changes and is not yet enabled from this screen.')); ?></p>

            <div class="mt-4 flex justify-end gap-2">
                <button type="button" class="erp-btn-secondary text-xs" @click="importModalOpen = false"><?php echo e(__('Close')); ?></button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/settings/forms/partials/landing.blade.php ENDPATH**/ ?>