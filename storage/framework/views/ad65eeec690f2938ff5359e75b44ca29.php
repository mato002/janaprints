<?php
    $cards = $tabData['dashboard_cards'] ?? [];
    $sections = $tabData['sections'] ?? [];
    $operators = $tabData['operators'] ?? [];
    $recommendations = $tabData['recommendations'] ?? [];
    $materialPlan = $tabData['material_plan'] ?? [];
    $costSummary = $tabData['cost_summary'] ?? null;
    $qcHints = $tabData['qc_hints'] ?? [];
    $artwork = $tabData['artwork'] ?? [];

    $cardToneClasses = [
        'success' => 'border-emerald-200 bg-emerald-50/60 hover:border-emerald-300',
        'warning' => 'border-amber-200 bg-amber-50/60 hover:border-amber-300',
        'danger' => 'border-red-200 bg-red-50/60 hover:border-red-300',
        'active' => 'border-sky-200 bg-sky-50/60 hover:border-sky-300',
        'neutral' => 'border-slate-200 bg-slate-50/80 hover:border-slate-300',
    ];

    $statusToneClasses = [
        'success' => 'bg-emerald-100 text-emerald-800',
        'warning' => 'bg-amber-100 text-amber-800',
        'danger' => 'bg-red-100 text-red-800',
        'active' => 'bg-sky-100 text-sky-800',
        'neutral' => 'bg-slate-100 text-slate-700',
    ];
?>

<div
    class="mfg-dashboard"
    x-data="{
        activeCard: null,
        openCard(id) { this.activeCard = id },
        closeDrawer() { this.activeCard = null },
        cardLabel(id) {
            if (id === 'cost') {
                return <?php echo \Illuminate\Support\Js::from(__('Cost summary'))->toHtml() ?>;
            }
            const labels = <?php echo \Illuminate\Support\Js::from(collect($cards)->mapWithKeys(fn ($c) => [$c['id'] => $c['label']])->all())->toHtml() ?>;
            return labels[id] ?? id;
        },
    }"
    @keydown.escape.window="closeDrawer()"
>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <div class="mb-4 flex flex-wrap items-end justify-between gap-2">
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Manufacturing overview')); ?></h3>
                <?php if(! empty($tabData['template_name'])): ?>
                    <p class="mt-0.5 text-xs text-slate-500"><?php echo e(__('Template')); ?>: <?php echo e($tabData['template_name']); ?></p>
                <?php endif; ?>
            </div>
            <p class="text-xs text-slate-500"><?php echo e(__('Click a module for details')); ?></p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $tone = $card['tone'] ?? 'neutral';
                    $cardClass = $cardToneClasses[$tone] ?? $cardToneClasses['neutral'];
                    $badgeClass = $statusToneClasses[$tone] ?? $statusToneClasses['neutral'];
                ?>
                <button
                    type="button"
                    class="group flex min-h-[7.5rem] w-full flex-col rounded-lg border p-4 text-left shadow-sm transition hover:shadow-md focus:outline-none focus:ring-2 focus:ring-erp-primary/25 <?php echo e($cardClass); ?>"
                    @click="openCard(<?php echo \Illuminate\Support\Js::from($card['id'])->toHtml() ?>)"
                    :aria-expanded="activeCard === <?php echo \Illuminate\Support\Js::from($card['id'])->toHtml() ?>"
                >
                    <div class="flex w-full items-start justify-between gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-600"><?php echo e($card['label']); ?></span>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide <?php echo e($badgeClass); ?>">
                            <?php echo e($card['status']); ?>

                        </span>
                    </div>
                    <?php if(! empty($card['summary'])): ?>
                        <p class="mt-2 line-clamp-2 text-sm font-medium text-slate-800"><?php echo e($card['summary']); ?></p>
                    <?php else: ?>
                        <p class="mt-2 text-sm text-slate-400"><?php echo e(__('No summary')); ?></p>
                    <?php endif; ?>
                    <span class="mt-auto pt-3 text-xs font-medium text-slate-500 group-hover:text-erp-primary">
                        <?php echo e(__('View details')); ?> →
                    </span>
                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php if($costSummary): ?>
            <div class="mt-3 border-t border-erp-border pt-3">
                <button
                    type="button"
                    class="flex w-full max-w-xs items-center justify-between gap-3 rounded-lg border border-slate-200 bg-slate-50/80 px-4 py-3 text-left transition hover:border-slate-300 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-erp-primary/25"
                    @click="openCard('cost')"
                >
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-600"><?php echo e(__('Cost summary')); ?></span>
                    <span class="font-semibold tabular-nums text-slate-900"><?php echo e(number_format($costSummary['total'], 2)); ?></span>
                </button>
            </div>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>

    
    <div
        class="fixed inset-0 z-40 flex justify-end"
        x-show="activeCard"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-slate-900/30" @click="closeDrawer()" aria-hidden="true"></div>
        <aside
            class="relative z-10 flex h-full w-full max-w-md flex-col border-l border-erp-border bg-white shadow-xl"
            role="dialog"
            aria-modal="true"
            :aria-labelledby="'mfg-drawer-title-' + activeCard"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
        >
            <div class="flex items-center justify-between gap-3 border-b border-erp-border px-5 py-4">
                <h4 class="text-base font-semibold text-erp-primary" :id="'mfg-drawer-title-' + activeCard" x-text="cardLabel(activeCard)"></h4>
                <button type="button" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" @click="closeDrawer()" aria-label="<?php echo e(__('Close')); ?>">
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

            <div class="flex-1 overflow-y-auto px-5 py-4">
                <div x-show="activeCard === 'general'" x-cloak>
                    <?php if(! empty($sections['general'])): ?>
                        <?php echo $__env->make('admin.production.job-cards.workspace.partials.manufacturing-field-list', ['fields' => $sections['general']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php else: ?>
                        <p class="text-sm text-slate-500"><?php echo e(__('No general specification fields.')); ?></p>
                    <?php endif; ?>
                    <?php if(! empty($sections['notes'])): ?>
                        <div class="mt-4 border-t border-erp-border pt-4">
                            <h5 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Production notes')); ?></h5>
                            <?php echo $__env->make('admin.production.job-cards.workspace.partials.manufacturing-field-list', ['fields' => $sections['notes']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                    <?php endif; ?>
                    <?php if(! empty($sections['delivery'])): ?>
                        <div class="mt-4 border-t border-erp-border pt-4">
                            <h5 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Delivery')); ?></h5>
                            <?php echo $__env->make('admin.production.job-cards.workspace.partials.manufacturing-field-list', ['fields' => $sections['delivery']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div x-show="activeCard === 'materials'" x-cloak>
                    <?php if(! empty($sections['material'])): ?>
                        <?php echo $__env->make('admin.production.job-cards.workspace.partials.manufacturing-field-list', ['fields' => $sections['material']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?>
                    <?php if(! empty($materialPlan['paper']) || ! empty($materialPlan['estimated_sheets'])): ?>
                        <div class="<?php if(! empty($sections['material'])): ?> mt-4 border-t border-erp-border pt-4 <?php endif; ?>">
                            <h5 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Material summary')); ?></h5>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <div class="rounded-lg border border-erp-border bg-slate-50 p-3">
                                    <div class="text-xs text-slate-500"><?php echo e(__('Paper')); ?></div>
                                    <div class="mt-1 font-medium"><?php echo e($materialPlan['paper'] ?? '—'); ?></div>
                                </div>
                                <div class="rounded-lg border border-erp-border bg-slate-50 p-3">
                                    <div class="text-xs text-slate-500"><?php echo e(__('Estimated sheets')); ?></div>
                                    <div class="mt-1 font-medium tabular-nums"><?php echo e($materialPlan['estimated_sheets'] ?? '—'); ?></div>
                                </div>
                                <div class="rounded-lg border border-erp-border bg-slate-50 p-3">
                                    <div class="text-xs text-slate-500"><?php echo e(__('Waste')); ?></div>
                                    <div class="mt-1 font-medium tabular-nums">
                                        <?php if(($materialPlan['waste_percent'] ?? null) !== null): ?>
                                            <?php echo e(number_format((float) $materialPlan['waste_percent'], 1)); ?>%
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-slate-500"><?php echo e(__('Planning view only — stock is not reserved from this panel.')); ?></p>
                        </div>
                    <?php endif; ?>
                    <a href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials'])); ?>" class="mt-4 inline-flex items-center text-xs font-medium text-erp-primary hover:underline"><?php echo e(__('Open materials tab')); ?> →</a>
                </div>

                <div x-show="activeCard === 'production'" x-cloak>
                    <?php if(! empty($sections['production'])): ?>
                        <?php echo $__env->make('admin.production.job-cards.workspace.partials.manufacturing-field-list', ['fields' => $sections['production']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php else: ?>
                        <p class="text-sm text-slate-500"><?php echo e(__('No production specification fields.')); ?></p>
                    <?php endif; ?>
                </div>

                <div x-show="activeCard === 'printing'" x-cloak>
                    <?php if(! empty($sections['printing'])): ?>
                        <?php echo $__env->make('admin.production.job-cards.workspace.partials.manufacturing-field-list', ['fields' => $sections['printing']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php else: ?>
                        <p class="text-sm text-slate-500"><?php echo e(__('No printing specification fields.')); ?></p>
                    <?php endif; ?>
                </div>

                <div x-show="activeCard === 'finishing'" x-cloak>
                    <?php if(! empty($sections['finishing'])): ?>
                        <?php echo $__env->make('admin.production.job-cards.workspace.partials.manufacturing-field-list', ['fields' => $sections['finishing']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php else: ?>
                        <p class="text-sm text-slate-500"><?php echo e(__('No finishing specification fields.')); ?></p>
                    <?php endif; ?>
                </div>

                <div x-show="activeCard === 'qc'" x-cloak>
                    <ul class="space-y-2 text-sm text-slate-700">
                        <?php $__currentLoopData = $qcHints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hint): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-start gap-2">
                                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-erp-primary"></span>
                                <span><?php echo e($hint); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <a href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'quality'])); ?>" class="mt-4 inline-flex items-center text-xs font-medium text-erp-primary hover:underline"><?php echo e(__('Open QC tab')); ?> →</a>
                </div>

                <div x-show="activeCard === 'dispatch'" x-cloak>
                    <?php if(! empty($sections['delivery'])): ?>
                        <?php echo $__env->make('admin.production.job-cards.workspace.partials.manufacturing-field-list', ['fields' => $sections['delivery']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php else: ?>
                        <p class="text-sm text-slate-600"><?php echo e(__('Dispatch details are managed from the Dispatch tab once the job is ready.')); ?></p>
                    <?php endif; ?>
                    <a href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch'])); ?>" class="mt-4 inline-flex items-center text-xs font-medium text-erp-primary hover:underline"><?php echo e(__('Open dispatch tab')); ?> →</a>
                </div>

                <div x-show="activeCard === 'artwork'" x-cloak>
                    <?php if(! empty($sections['artwork'])): ?>
                        <?php echo $__env->make('admin.production.job-cards.workspace.partials.manufacturing-field-list', ['fields' => $sections['artwork']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?>
                    <?php if(! empty($artwork) && empty($artwork['empty'])): ?>
                        <div class="<?php if(! empty($sections['artwork'])): ?> mt-4 border-t border-erp-border pt-4 <?php endif; ?>">
                            <?php if($artwork['request'] ?? null): ?>
                                <p class="font-medium"><?php echo e($artwork['request']->request_number); ?> · v<?php echo e($artwork['request']->current_version); ?></p>
                                <p class="text-xs text-slate-500"><?php echo e(str_replace('_', ' ', $artwork['approval_status'] ?? '')); ?></p>
                            <?php else: ?>
                                <p class="text-sm text-slate-500"><?php echo e(__('No artwork request linked.')); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <a href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'artwork'])); ?>" class="mt-4 inline-flex items-center text-xs font-medium text-erp-primary hover:underline"><?php echo e(__('Open artwork tab')); ?> →</a>
                </div>

                <div x-show="activeCard === 'machine'" x-cloak>
                    <h5 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Machine recommendation')); ?></h5>
                    <dl class="divide-y divide-erp-border rounded-lg border border-erp-border text-sm">
                        <div class="flex justify-between gap-3 px-3 py-2"><dt class="text-slate-500"><?php echo e(__('Recommended work centre')); ?></dt><dd class="font-medium"><?php echo e($recommendations['work_center'] ?? '—'); ?></dd></div>
                        <div class="flex justify-between gap-3 px-3 py-2"><dt class="text-slate-500"><?php echo e(__('Recommended machine')); ?></dt><dd class="font-medium"><?php echo e($recommendations['machine'] ?? '—'); ?></dd></div>
                        <div class="flex justify-between gap-3 px-3 py-2"><dt class="text-slate-500"><?php echo e(__('Recommended department')); ?></dt><dd class="font-medium"><?php echo e($recommendations['department'] ?? '—'); ?></dd></div>
                    </dl>
                    <p class="mt-2 text-xs text-slate-500"><?php echo e(__('Recommendations only — operators may override assignments.')); ?></p>

                    <div class="mt-4 border-t border-erp-border pt-4">
                        <h5 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Operator information')); ?></h5>
                        <dl class="divide-y divide-erp-border rounded-lg border border-erp-border text-sm">
                            <div class="flex justify-between gap-3 px-3 py-2"><dt class="text-slate-500"><?php echo e(__('Assigned operator')); ?></dt><dd class="font-medium"><?php echo e($operators['operator'] ?? '—'); ?></dd></div>
                            <div class="flex justify-between gap-3 px-3 py-2"><dt class="text-slate-500"><?php echo e(__('Assigned supervisor')); ?></dt><dd class="font-medium"><?php echo e($operators['supervisor'] ?? '—'); ?></dd></div>
                            <div class="flex justify-between gap-3 px-3 py-2"><dt class="text-slate-500"><?php echo e(__('Assigned machine')); ?></dt><dd class="font-medium"><?php echo e($operators['machine'] ?? '—'); ?></dd></div>
                            <div class="flex justify-between gap-3 px-3 py-2"><dt class="text-slate-500"><?php echo e(__('Assigned department')); ?></dt><dd class="font-medium"><?php echo e($operators['department'] ?? '—'); ?></dd></div>
                        </dl>
                    </div>
                </div>

                <?php if($costSummary): ?>
                    <div x-show="activeCard === 'cost'" x-cloak>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <?php $__currentLoopData = [__('Material') => $costSummary['material'], __('Labour') => $costSummary['labor'], __('Outsource') => $costSummary['outsource'], __('Total') => $costSummary['total']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="rounded-lg border border-erp-border bg-slate-50 p-3">
                                    <div class="text-xs text-slate-500"><?php echo e($label); ?></div>
                                    <div class="mt-1 font-semibold tabular-nums"><?php echo e(number_format($value, 2)); ?></div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <p class="mt-3 text-xs text-slate-500"><?php echo e(__('Read-only — use Commercial tab or costing workspace for full detail.')); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/partials/manufacturing-dashboard.blade.php ENDPATH**/ ?>