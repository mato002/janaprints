<?php
    $kpis = $kpis ?? [];
    $compact = (bool) ($compact ?? false);
    $kpiThemes = [
        'operation_completion' => 'production',
        'operators' => 'production',
        'quality' => 'qc',
        'dispatch_score' => 'dispatch',
        'materials' => 'materials',
        'wastage' => 'dispatch',
        'session_waste' => 'slate',
        'serial_loss' => 'slate',
    ];
    $allKeys = array_keys($kpiThemes);
    $displayKpis = collect($kpis)
        ->filter(fn ($kpi) => in_array($kpi['key'] ?? '', $allKeys, true))
        ->when($compact, fn ($c) => $c->reject(fn ($kpi) => ($kpi['key'] ?? '') === 'materials'))
        ->take($compact ? 5 : 6)
        ->values();
?>

<?php if($displayKpis->isNotEmpty()): ?>
    <?php if($compact): ?>
        <?php $__currentLoopData = $displayKpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $theme = $kpiThemes[$kpi['key'] ?? ''] ?? 'slate';
                $primaryMetric = $kpi['metrics'][0] ?? null;
                $displayValue = $primaryMetric['value'] ?? ($kpi['placeholder'] ?? '—');
                $shortLabel = match ($kpi['key'] ?? '') {
                    'operation_completion' => __('Complete'),
                    'operators' => __('Ops'),
                    'quality' => __('QC'),
                    'dispatch_score' => __('Dispatch'),
                    'materials' => __('Mat'),
                    'wastage' => __('Waste'),
                    'session_waste' => __('Sess'),
                    'serial_loss' => __('Serial'),
                    default => \Illuminate\Support\Str::limit($kpi['label'] ?? '', 8, ''),
                };
            ?>
            <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['mes-kpi', 'mes-kpi--'.$theme, 'mes-kpi--warning' => ! empty($kpi['warning'])]); ?>" title="<?php echo e($kpi['label'] ?? ''); ?>">
                <span class="mes-kpi__label"><?php echo e($shortLabel); ?></span>
                <span class="mes-kpi__value"><?php echo e($displayValue); ?></span>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
        <section aria-label="<?php echo e(__('Performance metrics')); ?>">
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                <?php $__currentLoopData = $displayKpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $theme = $kpiThemes[$kpi['key'] ?? ''] ?? 'slate';
                        $primaryMetric = $kpi['metrics'][0] ?? null;
                        $displayValue = $primaryMetric['value'] ?? ($kpi['placeholder'] ?? '—');
                    ?>
                    <?php if (isset($component)) { $__componentOriginal3919388ffc59925ca44d4d29cc578eca = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3919388ffc59925ca44d4d29cc578eca = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.job-kpi-tile','data' => ['theme' => $theme,'label' => $kpi['label'] ?? '','value' => $displayValue,'emphasis' => ! empty($kpi['warning'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.job-kpi-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['theme' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($theme),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['label'] ?? ''),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($displayValue),'emphasis' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(! empty($kpi['warning']))]); ?>
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
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/partials/performance-section.blade.php ENDPATH**/ ?>