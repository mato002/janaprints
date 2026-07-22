<?php
    $kpis = collect($dashboard['kpi_strip'])->keyBy('key');
    $finance = $dashboard['finance'];
    $production = $dashboard['production'];

    $salesToday = $kpis->get('sales_today');
    $activeJobs = $kpis->get('active_jobs');
    $receivables = $kpis->get('receivables');
    $profitMtd = $finance['profit_mtd'] ?? '—';
    $revenueMtd = $finance['revenue_mtd'] ?? '—';

    $salesRoute = $salesToday && ! empty($salesToday['route']) && Route::has($salesToday['route'])
        ? route($salesToday['route']) : null;
    $jobsRoute = $activeJobs && ! empty($activeJobs['route']) && Route::has($activeJobs['route'])
        ? route($activeJobs['route']) : null;

    $salesRaw = $salesToday['value'] ?? '0';
    $salesEmpty = $salesRaw === 'KES 0' || $salesRaw === '0';
    $jobsCount = (int) ($activeJobs['value'] ?? 0);
    $delayedJobs = (int) ($production['delayed'] ?? 0);
?>

<section class="exec-hero" aria-label="<?php echo e(__('Executive summary')); ?>">
    <?php if (isset($component)) { $__componentOriginal5c01a647b8631d8cddc3ecb7f9502c87 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c01a647b8631d8cddc3ecb7f9502c87 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-hero-metric','data' => ['label' => __('Today\'s Revenue'),'value' => $salesToday['value'] ?? 'KES 0','href' => $salesRoute,'empty' => $salesEmpty,'subtext' => $salesEmpty ? __('No revenue recorded today') : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-hero-metric'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Today\'s Revenue')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($salesToday['value'] ?? 'KES 0'),'href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($salesRoute),'empty' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($salesEmpty),'subtext' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($salesEmpty ? __('No revenue recorded today') : null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c01a647b8631d8cddc3ecb7f9502c87)): ?>
<?php $attributes = $__attributesOriginal5c01a647b8631d8cddc3ecb7f9502c87; ?>
<?php unset($__attributesOriginal5c01a647b8631d8cddc3ecb7f9502c87); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c01a647b8631d8cddc3ecb7f9502c87)): ?>
<?php $component = $__componentOriginal5c01a647b8631d8cddc3ecb7f9502c87; ?>
<?php unset($__componentOriginal5c01a647b8631d8cddc3ecb7f9502c87); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal5c01a647b8631d8cddc3ecb7f9502c87 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c01a647b8631d8cddc3ecb7f9502c87 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-hero-metric','data' => ['label' => __('Jobs In Production'),'value' => (string) $jobsCount,'href' => $jobsRoute,'empty' => $jobsCount === 0,'subtext' => $jobsCount === 0 ? __('No jobs in production') : ($delayedJobs > 0 ? __(':count overdue', ['count' => $delayedJobs]) : null)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-hero-metric'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Jobs In Production')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((string) $jobsCount),'href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($jobsRoute),'empty' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($jobsCount === 0),'subtext' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($jobsCount === 0 ? __('No jobs in production') : ($delayedJobs > 0 ? __(':count overdue', ['count' => $delayedJobs]) : null))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c01a647b8631d8cddc3ecb7f9502c87)): ?>
<?php $attributes = $__attributesOriginal5c01a647b8631d8cddc3ecb7f9502c87; ?>
<?php unset($__attributesOriginal5c01a647b8631d8cddc3ecb7f9502c87); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c01a647b8631d8cddc3ecb7f9502c87)): ?>
<?php $component = $__componentOriginal5c01a647b8631d8cddc3ecb7f9502c87; ?>
<?php unset($__componentOriginal5c01a647b8631d8cddc3ecb7f9502c87); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal5c01a647b8631d8cddc3ecb7f9502c87 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c01a647b8631d8cddc3ecb7f9502c87 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-hero-metric','data' => ['label' => __('Outstanding Receivables'),'value' => $receivables['value'] ?? '—','hint' => ($receivables['hint'] ?? null) ?: (($receivables['value'] ?? '—') === '—' ? __('Finance module') : null),'empty' => ($receivables['value'] ?? '—') === '—']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-hero-metric'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Outstanding Receivables')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($receivables['value'] ?? '—'),'hint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($receivables['hint'] ?? null) ?: (($receivables['value'] ?? '—') === '—' ? __('Finance module') : null)),'empty' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($receivables['value'] ?? '—') === '—')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c01a647b8631d8cddc3ecb7f9502c87)): ?>
<?php $attributes = $__attributesOriginal5c01a647b8631d8cddc3ecb7f9502c87; ?>
<?php unset($__attributesOriginal5c01a647b8631d8cddc3ecb7f9502c87); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c01a647b8631d8cddc3ecb7f9502c87)): ?>
<?php $component = $__componentOriginal5c01a647b8631d8cddc3ecb7f9502c87; ?>
<?php unset($__componentOriginal5c01a647b8631d8cddc3ecb7f9502c87); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal5c01a647b8631d8cddc3ecb7f9502c87 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c01a647b8631d8cddc3ecb7f9502c87 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-hero-metric','data' => ['label' => __('Net Profit MTD'),'value' => $profitMtd,'hint' => $profitMtd === '—' ? __('Finance module') : null,'subtext' => $profitMtd === '—' && $revenueMtd !== '—' ? __('Revenue MTD: :amount', ['amount' => $revenueMtd]) : null,'empty' => $profitMtd === '—']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-hero-metric'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Net Profit MTD')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($profitMtd),'hint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($profitMtd === '—' ? __('Finance module') : null),'subtext' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($profitMtd === '—' && $revenueMtd !== '—' ? __('Revenue MTD: :amount', ['amount' => $revenueMtd]) : null),'empty' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($profitMtd === '—')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c01a647b8631d8cddc3ecb7f9502c87)): ?>
<?php $attributes = $__attributesOriginal5c01a647b8631d8cddc3ecb7f9502c87; ?>
<?php unset($__attributesOriginal5c01a647b8631d8cddc3ecb7f9502c87); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c01a647b8631d8cddc3ecb7f9502c87)): ?>
<?php $component = $__componentOriginal5c01a647b8631d8cddc3ecb7f9502c87; ?>
<?php unset($__componentOriginal5c01a647b8631d8cddc3ecb7f9502c87); ?>
<?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\dashboard\partials\hero.blade.php ENDPATH**/ ?>