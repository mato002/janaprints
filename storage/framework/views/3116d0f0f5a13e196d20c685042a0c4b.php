<?php
    use App\Models\Assets\FixedAsset;
    use App\Support\Production\ProductionFloorDeskViews;

    $operatorMode = (bool) ($operatorMode ?? false);
    $activeFloorView = ProductionFloorDeskViews::normalize($activeFloorView ?? request('view'));
    $embeddedInFloor = (bool) ($embeddedInFloor ?? ($activeFloorView !== ProductionFloorDeskViews::FLOOR));

    $machinesForUi = [];
    if ($activeFloorView === ProductionFloorDeskViews::FLOOR) {
        $machineMeta = FixedAsset::query()
            ->forTenant()
            ->whereHas('machineProfile')
            ->with('machineProfile:id,fixed_asset_id,production_status')
            ->orderBy('asset_name')
            ->get(['id', 'asset_name'])
            ->mapWithKeys(function ($machine) {
                $status = $machine->machineProfile?->production_status;

                return [
                    (string) $machine->id => [
                        'status' => $status?->value,
                        'status_label' => $status?->label(),
                        'icon' => match ($status?->value) {
                            'available' => '🟢',
                            'running', 'idle' => '🟡',
                            'maintenance' => '🔴',
                            'offline', 'retired' => '⚪',
                            default => '⚪',
                        },
                    ],
                ];
            });

        $machinesForUi = collect($filter_options['machines'] ?? [])->map(function ($machine) use ($machineMeta) {
            $meta = $machineMeta[(string) $machine['value']] ?? null;
            $label = $machine['label'];

            if ($meta) {
                $label = trim(($meta['icon'] ?? '').' '.$machine['label'].' · '.($meta['status_label'] ?? ''));
            }

            return [
                'value' => $machine['value'],
                'label' => $label,
            ];
        })->values()->all();
    }
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $operatorMode ? __('Operator Floor') : __('Production Floor'),'breadcrumbs' => $operatorMode
        ? [['label' => __('Operator Floor')]]
        : [
            ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
            ['label' => __('Production Floor')],
        ],'compactPage' => false] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="production-floor-shell">
        <?php if($operatorMode): ?>
            <div class="mb-3 flex flex-col gap-2 rounded-lg border border-erp-accent/25 bg-erp-accent/5 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-erp-primary"><?php echo e(__('Operator mode')); ?></p>
                    <p class="text-xs text-slate-600"><?php echo e(__('Work arrives here — use Next step on each job. Preview orders and jobs in modals without leaving the floor.')); ?></p>
                </div>
            </div>
        <?php elseif($activeFloorView === ProductionFloorDeskViews::FLOOR && ! \App\Support\Navigation\WorkspaceEmbed::inWorkspaceContext()): ?>
            <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Production Floor'),'description' => __('Production queue — work arrives here ready to run. Assign machines, execute stages, and dispatch.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Production Floor')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Production queue — work arrives here ready to run. Assign machines, execute stages, and dispatch.'))]); ?>
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
        <?php endif; ?>

        <?php echo $__env->make('admin.production.floor.partials.desk-mode-nav', ['activeFloorView' => $activeFloorView], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if(session('status')): ?>
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

        <?php if($activeFloorView === ProductionFloorDeskViews::REGISTER): ?>
            <?php echo $__env->make('admin.production.job-cards.partials.register-content', ['embeddedInFloor' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif($activeFloorView === ProductionFloorDeskViews::QUEUE): ?>
            <?php echo $__env->make('admin.production.queue.partials.workspace-content', ['embeddedInFloor' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif($activeFloorView === ProductionFloorDeskViews::OUTPUTS): ?>
            <?php echo $__env->make('admin.production.outputs.partials.register-content', ['embeddedInFloor' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <div
                class="production-floor"
                x-data="productionFloor(<?php echo \Illuminate\Support\Js::from([
                    'panelBase' => url('admin/production/floor/jobs'),
                    'initialJobKey' => request('job'),
                    'assignMachineUrl' => url('admin/production/floor/jobs'),
                    'labelUrl' => url('admin/production/job-cards'),
                    'jobCardUrl' => url('admin/production/job-cards'),
                    'csrf' => csrf_token(),
                    'machines' => $machinesForUi,
                    'operatorCreateUrl' => auth()->user()?->can('employees.manage')
                        ? route('admin.operators.quick-create')
                        : null,
                    'operatorsRefreshUrl' => route('admin.lookups.operators'),
                    'modalTitles' => [
                        'operator' => __('Assign operator'),
                        'machine' => __('Assign machine'),
                        'outsource-send' => __('Send to vendor'),
                        'outsource-return' => __('Mark returned from vendor'),
                        'qc' => __('Record inspection'),
                        'fulfilment' => __('Hand off'),
                        'default' => __('Next step'),
                    ],
                ])->toHtml() ?>)"
                x-cloak
            >
                <div class="production-floor-command-sticky" x-ref="commandBar">
                    <?php echo $__env->make('admin.production.floor.partials.summary-strip', ['summary' => $summary], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false,'class' => 'mb-0 shadow-sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'class' => 'mb-0 shadow-sm']); ?>
                        <?php if (isset($component)) { $__componentOriginal9577df2686262fb25ceb19a81119823d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9577df2686262fb25ceb19a81119823d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.index-toolbar','data' => ['action' => route('admin.production.floor'),'resetUrl' => route('admin.production.floor'),'dataProductionFloorLiveFilters' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.index-toolbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.production.floor')),'reset-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.production.floor')),'data-production-floor-live-filters' => true]); ?>
                            <?php if(request('desk')): ?>
                                <input type="hidden" name="desk" value="<?php echo e(request('desk')); ?>">
                            <?php endif; ?>
                            <input
                                type="search"
                                name="search"
                                value="<?php echo e($filters['search']); ?>"
                                class="erp-toolbar-input min-w-[12rem] flex-1"
                                placeholder="<?php echo e(__('Job or product…')); ?>"
                                aria-label="<?php echo e(__('Search')); ?>"
                                data-erp-auto-search
                            >
                            <select name="stage" class="erp-toolbar-select" aria-label="<?php echo e(__('Stage')); ?>" data-erp-auto-submit>
                                <option value=""><?php echo e(__('All active stages')); ?></option>
                                <?php $__currentLoopData = $filter_options['stages']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($stage['value']); ?>" <?php if($filters['stage'] === $stage['value']): echo 'selected'; endif; ?>>
                                        <?php echo e($stage['label']); ?>

                                        <?php if(($stage_counts[$stage['value']] ?? 0) > 0): ?>
                                            (<?php echo e($stage_counts[$stage['value']]); ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <select name="machine_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Machine')); ?>" data-erp-auto-submit>
                                <option value=""><?php echo e(__('All machines')); ?></option>
                                <?php $__currentLoopData = $filter_options['machines']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $machine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($machine['value']); ?>" <?php if($filters['machine_id'] === $machine['value']): echo 'selected'; endif; ?>><?php echo e($machine['label']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <select name="vendor_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Vendor')); ?>" data-erp-auto-submit>
                                <option value=""><?php echo e(__('All vendors')); ?></option>
                                <?php $__currentLoopData = $filter_options['vendors']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($vendor['value']); ?>" <?php if($filters['vendor_id'] === $vendor['value']): echo 'selected'; endif; ?>><?php echo e($vendor['label']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <select name="priority" class="erp-toolbar-select" aria-label="<?php echo e(__('Priority')); ?>" data-erp-auto-submit>
                                <option value=""><?php echo e(__('All priorities')); ?></option>
                                <?php $__currentLoopData = $filter_options['priorities']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($priority['value']); ?>" <?php if($filters['priority'] === $priority['value']): echo 'selected'; endif; ?>><?php echo e($priority['label']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <label class="inline-flex items-center gap-1.5 text-xs text-slate-600">
                                <input type="checkbox" name="overdue" value="1" class="rounded border-slate-300" data-erp-auto-submit <?php if($filters['overdue'] === '1'): echo 'checked'; endif; ?>>
                                <?php echo e(__('Overdue only')); ?>

                            </label>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9577df2686262fb25ceb19a81119823d)): ?>
<?php $attributes = $__attributesOriginal9577df2686262fb25ceb19a81119823d; ?>
<?php unset($__attributesOriginal9577df2686262fb25ceb19a81119823d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9577df2686262fb25ceb19a81119823d)): ?>
<?php $component = $__componentOriginal9577df2686262fb25ceb19a81119823d; ?>
<?php unset($__componentOriginal9577df2686262fb25ceb19a81119823d); ?>
<?php endif; ?>

                        <div class="production-floor-toolbar-extras">
                            <label class="inline-flex items-center gap-1.5 text-xs text-slate-600">
                                <span class="font-medium text-slate-700"><?php echo e(__('Group by')); ?></span>
                                <select class="erp-toolbar-select text-xs" x-model="groupBy" @change="applyGrouping()" aria-label="<?php echo e(__('Group queue by')); ?>">
                                    <option value=""><?php echo e(__('None')); ?></option>
                                    <option value="machine"><?php echo e(__('Machine')); ?></option>
                                    <option value="stage"><?php echo e(__('Stage')); ?></option>
                                    <option value="priority"><?php echo e(__('Priority')); ?></option>
                                    <option value="vendor"><?php echo e(__('Vendor')); ?></option>
                                    <option value="due"><?php echo e(__('Due date')); ?></option>
                                    <option value="operator"><?php echo e(__('Operator / work center')); ?></option>
                                    <option value="customer"><?php echo e(__('Customer')); ?></option>
                                </select>
                            </label>
                        </div>
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
                </div>

                <div
                    class="production-floor-batch-bar"
                    x-ref="batchBar"
                    x-show="selectedJobs.length > 0"
                    x-cloak
                >
                    <span class="production-floor-batch-bar__count" x-text="`${selectedJobs.length} <?php echo e(__('selected')); ?>`"></span>
                    <button type="button" class="erp-btn-secondary text-xs py-1 px-2" @click="openBatchMachineAssign()"><?php echo e(__('Assign machine')); ?></button>
                    <button type="button" class="erp-btn-secondary text-xs py-1 px-2" @click="batchPrintLabels()"><?php echo e(__('Print labels')); ?></button>
                    <button type="button" class="erp-btn-secondary text-xs py-1 px-2" @click="batchPrintJobCards()"><?php echo e(__('Print job sheets')); ?></button>
                    <button type="button" class="erp-btn-ghost text-xs py-1 px-2" @click="clearSelection()"><?php echo e(__('Clear')); ?></button>
                </div>

                <div
                    x-show="batchMachineOpen"
                    x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    @keydown.escape.window="batchMachineOpen = false"
                >
                    <div class="absolute inset-0 bg-slate-900/40" @click="batchMachineOpen = false"></div>
                    <div class="relative z-10 w-full max-w-md rounded-lg border border-erp-border bg-white p-4 shadow-xl">
                        <h3 class="mb-3 text-sm font-semibold text-erp-primary"><?php echo e(__('Assign machine to selected jobs')); ?></h3>
                        <select class="erp-select w-full text-sm" x-model="batchMachineId">
                            <option value=""><?php echo e(__('Assign')); ?></option>
                            <template x-for="machine in machines" :key="machine.value">
                                <option :value="machine.value" x-text="machine.label"></option>
                            </template>
                        </select>
                        <div class="mt-4 flex justify-end gap-2">
                            <button type="button" class="erp-btn-secondary text-sm" @click="batchMachineOpen = false"><?php echo e(__('Cancel')); ?></button>
                            <button type="button" class="erp-btn-primary text-sm" @click="submitBatchMachineAssign()" :disabled="batchSubmitting">
                                <span x-show="!batchSubmitting"><?php echo e(__('Apply')); ?></span>
                                <span x-show="batchSubmitting"><?php echo e(__('Applying…')); ?></span>
                            </button>
                        </div>
                    </div>
                </div>

                <?php echo $__env->make('admin.production.floor.partials.action-modal', ['operatorMode' => $operatorMode], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <div class="mb-2 flex items-center justify-between gap-3">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500"><?php echo e(__('Production Queue')); ?></h2>
                    <p class="text-xs text-slate-400"><?php echo e(__('No creation — execution only.')); ?></p>
                </div>

                <?php echo $__env->make('admin.production.floor.partials.table', [
                    'rows' => $rows,
                    'filter_options' => $filter_options,
                    'filters' => $filters,
                    'operatorMode' => $operatorMode,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <div class="mt-4 pb-6"><?php echo e($jobs->links()); ?></div>

                <?php echo $__env->make('admin.production.floor.partials.job-panel', ['operatorMode' => $operatorMode], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/floor/index.blade.php ENDPATH**/ ?>