<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $header = $header ?? [];
    $executionState = $executionState ?? [];
    $tabData = $tabData ?? [];
    $kpis = $kpis ?? [];
    $linkTurboAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();

    $summary = $tabData['summary'] ?? [];
    $queue = $tabData['queue'] ?? [];

    $qcKpi = collect($kpis)->firstWhere('key', 'quality');
    $dispatchKpi = collect($kpis)->firstWhere('key', 'dispatch_score');
    $completionKpi = collect($kpis)->firstWhere('key', 'operation_completion');

    $qcValue = $qcKpi['metrics'][0]['value'] ?? __('No QC recorded');
    $dispatchValue = ! empty($dispatchKpi['warning'])
        ? __('Blocked')
        : ($dispatchKpi['metrics'][0]['value'] ?? '—');
    $completionValue = $completionKpi['metrics'][0]['value'] ?? '0%';
?>

<aside class="space-y-3" aria-label="<?php echo e(__('Job status')); ?>">
    <?php if (isset($component)) { $__componentOriginalf57220fba53d148717b4781691527db9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf57220fba53d148717b4781691527db9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.job-module-card','data' => ['theme' => 'production','title' => __('Live status'),'icon' => 'view-grid','compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.job-module-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['theme' => 'production','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Live status')),'icon' => 'view-grid','compact' => true]); ?>
        <div class="grid grid-cols-2 gap-2">
            <?php if (isset($component)) { $__componentOriginal3919388ffc59925ca44d4d29cc578eca = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3919388ffc59925ca44d4d29cc578eca = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.job-kpi-tile','data' => ['theme' => 'slate','label' => __('Stage'),'value' => $header['current_stage_label'] ?? $executionState['stage_name'] ?? '—']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.job-kpi-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['theme' => 'slate','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Stage')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($header['current_stage_label'] ?? $executionState['stage_name'] ?? '—')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3919388ffc59925ca44d4d29cc578eca)): ?>
<?php $attributes = $__attributesOriginal3919388ffc59925ca44d4d29cc578eca; ?>
<?php unset($__attributesOriginal3919388ffc59925ca44d4d29cc578eca); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3919388ffc59925ca44d4d29cc578eca)): ?>
<?php $component = $__componentOriginal3919388ffc59925ca44d4d29cc578eca; ?>
<?php unset($__componentOriginal3919388ffc59925ca44d4d29cc578eca); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal3919388ffc59925ca44d4d29cc578eca = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3919388ffc59925ca44d4d29cc578eca = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.job-kpi-tile','data' => ['theme' => 'production','label' => __('Progress'),'value' => ((int) ($header['progress_percent'] ?? 0)).'%']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.job-kpi-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['theme' => 'production','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Progress')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(((int) ($header['progress_percent'] ?? 0)).'%')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3919388ffc59925ca44d4d29cc578eca)): ?>
<?php $attributes = $__attributesOriginal3919388ffc59925ca44d4d29cc578eca; ?>
<?php unset($__attributesOriginal3919388ffc59925ca44d4d29cc578eca); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3919388ffc59925ca44d4d29cc578eca)): ?>
<?php $component = $__componentOriginal3919388ffc59925ca44d4d29cc578eca; ?>
<?php unset($__componentOriginal3919388ffc59925ca44d4d29cc578eca); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal3919388ffc59925ca44d4d29cc578eca = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3919388ffc59925ca44d4d29cc578eca = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.job-kpi-tile','data' => ['theme' => 'dispatch','label' => __('Priority'),'value' => ucfirst(str_replace('_', ' ', $summary['priority'] ?? $header['priority']->value ?? '—'))]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.job-kpi-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['theme' => 'dispatch','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Priority')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(ucfirst(str_replace('_', ' ', $summary['priority'] ?? $header['priority']->value ?? '—')))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3919388ffc59925ca44d4d29cc578eca)): ?>
<?php $attributes = $__attributesOriginal3919388ffc59925ca44d4d29cc578eca; ?>
<?php unset($__attributesOriginal3919388ffc59925ca44d4d29cc578eca); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3919388ffc59925ca44d4d29cc578eca)): ?>
<?php $component = $__componentOriginal3919388ffc59925ca44d4d29cc578eca; ?>
<?php unset($__componentOriginal3919388ffc59925ca44d4d29cc578eca); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal3919388ffc59925ca44d4d29cc578eca = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3919388ffc59925ca44d4d29cc578eca = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.job-kpi-tile','data' => ['theme' => 'materials','label' => __('Queue'),'value' => ($queue['position'] ?? null) ? '#'.$queue['position'] : __('Not queued')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.job-kpi-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['theme' => 'materials','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Queue')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($queue['position'] ?? null) ? '#'.$queue['position'] : __('Not queued'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3919388ffc59925ca44d4d29cc578eca)): ?>
<?php $attributes = $__attributesOriginal3919388ffc59925ca44d4d29cc578eca; ?>
<?php unset($__attributesOriginal3919388ffc59925ca44d4d29cc578eca); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3919388ffc59925ca44d4d29cc578eca)): ?>
<?php $component = $__componentOriginal3919388ffc59925ca44d4d29cc578eca; ?>
<?php unset($__componentOriginal3919388ffc59925ca44d4d29cc578eca); ?>
<?php endif; ?>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf57220fba53d148717b4781691527db9)): ?>
<?php $attributes = $__attributesOriginalf57220fba53d148717b4781691527db9; ?>
<?php unset($__attributesOriginalf57220fba53d148717b4781691527db9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf57220fba53d148717b4781691527db9)): ?>
<?php $component = $__componentOriginalf57220fba53d148717b4781691527db9; ?>
<?php unset($__componentOriginalf57220fba53d148717b4781691527db9); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalf57220fba53d148717b4781691527db9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf57220fba53d148717b4781691527db9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.job-module-card','data' => ['theme' => 'qc','title' => __('QC & dispatch'),'icon' => 'shield-check','compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.job-module-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['theme' => 'qc','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('QC & dispatch')),'icon' => 'shield-check','compact' => true]); ?>
        <div class="grid grid-cols-1 gap-2">
            <?php if (isset($component)) { $__componentOriginal3919388ffc59925ca44d4d29cc578eca = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3919388ffc59925ca44d4d29cc578eca = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.job-kpi-tile','data' => ['theme' => 'qc','label' => __('QC status'),'value' => $qcValue,'emphasis' => str_contains((string) $qcValue, 'No QC')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.job-kpi-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['theme' => 'qc','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('QC status')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($qcValue),'emphasis' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(str_contains((string) $qcValue, 'No QC'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3919388ffc59925ca44d4d29cc578eca)): ?>
<?php $attributes = $__attributesOriginal3919388ffc59925ca44d4d29cc578eca; ?>
<?php unset($__attributesOriginal3919388ffc59925ca44d4d29cc578eca); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3919388ffc59925ca44d4d29cc578eca)): ?>
<?php $component = $__componentOriginal3919388ffc59925ca44d4d29cc578eca; ?>
<?php unset($__componentOriginal3919388ffc59925ca44d4d29cc578eca); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal3919388ffc59925ca44d4d29cc578eca = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3919388ffc59925ca44d4d29cc578eca = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.job-kpi-tile','data' => ['theme' => 'dispatch','label' => __('Dispatch'),'value' => $dispatchValue,'emphasis' => $dispatchValue === __('Blocked')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.job-kpi-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['theme' => 'dispatch','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Dispatch')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($dispatchValue),'emphasis' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($dispatchValue === __('Blocked'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3919388ffc59925ca44d4d29cc578eca)): ?>
<?php $attributes = $__attributesOriginal3919388ffc59925ca44d4d29cc578eca; ?>
<?php unset($__attributesOriginal3919388ffc59925ca44d4d29cc578eca); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3919388ffc59925ca44d4d29cc578eca)): ?>
<?php $component = $__componentOriginal3919388ffc59925ca44d4d29cc578eca; ?>
<?php unset($__componentOriginal3919388ffc59925ca44d4d29cc578eca); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal3919388ffc59925ca44d4d29cc578eca = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3919388ffc59925ca44d4d29cc578eca = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.job-kpi-tile','data' => ['theme' => 'production','label' => __('Completion'),'value' => $completionValue]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.job-kpi-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['theme' => 'production','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Completion')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($completionValue)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3919388ffc59925ca44d4d29cc578eca)): ?>
<?php $attributes = $__attributesOriginal3919388ffc59925ca44d4d29cc578eca; ?>
<?php unset($__attributesOriginal3919388ffc59925ca44d4d29cc578eca); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3919388ffc59925ca44d4d29cc578eca)): ?>
<?php $component = $__componentOriginal3919388ffc59925ca44d4d29cc578eca; ?>
<?php unset($__componentOriginal3919388ffc59925ca44d4d29cc578eca); ?>
<?php endif; ?>
        </div>
        <a href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'quality'])); ?>" class="mt-2 inline-flex text-xs font-medium text-violet-700 hover:underline" <?php $__currentLoopData = $linkTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>><?php echo e(__('Open QC tab')); ?> →</a>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf57220fba53d148717b4781691527db9)): ?>
<?php $attributes = $__attributesOriginalf57220fba53d148717b4781691527db9; ?>
<?php unset($__attributesOriginalf57220fba53d148717b4781691527db9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf57220fba53d148717b4781691527db9)): ?>
<?php $component = $__componentOriginalf57220fba53d148717b4781691527db9; ?>
<?php unset($__componentOriginalf57220fba53d148717b4781691527db9); ?>
<?php endif; ?>

    <?php echo $__env->make('admin.production.job-cards.workspace.partials.commercial-zone', [
        'jobCard' => $jobCard,
        'tabData' => $tabData,
        'dispatchSummary' => $dispatchSummary ?? null,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</aside>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\partials\status-sidebar.blade.php ENDPATH**/ ?>