<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Artwork Analysis Details'),'breadcrumbs' => [
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Artwork Analysis'), 'url' => route('admin.printing-intelligence.artwork-analysis.index')],
    ['label' => $analysis->original_filename],
]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $analysis->original_filename,'description' => __('Artwork metadata, colour, ink, production, and quotation recommendations (PI5).')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($analysis->original_filename),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Artwork metadata, colour, ink, production, and quotation recommendations (PI5).'))]); ?>
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

    <?php echo $__env->make('admin.printing-intelligence.partials.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php echo $__env->make('admin.printing-intelligence.partials.environment-warnings', ['environment' => $environment ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <a href="<?php echo e(route('admin.printing-intelligence.artwork-analysis.index')); ?>"
           class="text-xs font-medium text-slate-600 hover:text-slate-900">&larr; <?php echo e(__('Back to list')); ?></a>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.artwork.colour-analyze')): ?>
            <?php if($analysis->colour_analysis_status !== \App\Enums\ColourAnalysisStatus::Processing): ?>
                <form method="POST" action="<?php echo e(route('admin.printing-intelligence.artwork-analysis.colour-analysis', $analysis)); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="erp-btn-secondary text-xs"><?php echo e(__('Run Colour Analysis')); ?></button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.artwork.estimate-ink')): ?>
            <?php if(in_array($analysis->colour_analysis_status, [\App\Enums\ColourAnalysisStatus::Completed, \App\Enums\ColourAnalysisStatus::ManualReview], true)
                && ! $analysis->inkEstimates->contains(fn ($e) => $e->estimation_status === \App\Enums\InkEstimationStatus::Processing)): ?>
                <form method="POST" action="<?php echo e(route('admin.printing-intelligence.artwork-analysis.estimate-ink', $analysis)); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="erp-btn-secondary text-xs"><?php echo e(__('Run Ink Estimation')); ?></button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.artwork.estimate-production')): ?>
            <?php if(! ($productionEstimate?->estimation_status === \App\Enums\ProductionEstimationStatus::Processing)): ?>
                <form method="POST" action="<?php echo e(route('admin.printing-intelligence.artwork-analysis.estimate-production', $analysis)); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="erp-btn-secondary text-xs"><?php echo e(__('Run Production Estimation')); ?></button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
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
            <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('File info')); ?></h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Metadata status')); ?></dt>
                    <dd><span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', $analysis->analysis_status->badgeClass()]); ?>"><?php echo e($analysis->analysis_status->label()); ?></span></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Colour status')); ?></dt>
                    <dd>
                        <?php if($analysis->colour_analysis_status): ?>
                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', $analysis->colour_analysis_status->badgeClass()]); ?>"><?php echo e($analysis->colour_analysis_status->label()); ?></span>
                        <?php else: ?> — <?php endif; ?>
                    </dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Pages')); ?></dt><dd><?php echo e($analysis->page_count ?? '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('DPI')); ?></dt><dd><?php echo e($analysis->resolution_dpi ?? '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Colour analysed')); ?></dt><dd><?php echo e($analysis->colour_analyzed_at?->format('Y-m-d H:i:s') ?? '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Heavy coverage score')); ?></dt><dd><?php echo e($analysis->heavy_coverage_score !== null ? number_format((float) $analysis->heavy_coverage_score, 1) : '—'); ?></dd></div>
            </dl>
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
            <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Ink coverage summary')); ?></h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Total CMYK coverage')); ?></dt><dd><?php echo e($analysis->cmyk_coverage_percent !== null ? number_format((float) $analysis->cmyk_coverage_percent, 2).'%' : '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('RGB inked area')); ?></dt><dd><?php echo e($analysis->rgb_coverage_percent !== null ? number_format((float) $analysis->rgb_coverage_percent, 2).'%' : '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('White / no-ink')); ?></dt><dd><?php echo e($analysis->white_area_percent !== null ? number_format((float) $analysis->white_area_percent, 2).'%' : '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Transparent')); ?></dt><dd><?php echo e($analysis->transparent_area_percent !== null ? number_format((float) $analysis->transparent_area_percent, 2).'%' : '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Coverage class')); ?></dt>
                    <dd>
                        <?php if($analysis->coverage_class): ?>
                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', $analysis->coverage_class->badgeClass()]); ?>"><?php echo e($analysis->coverage_class->label()); ?></span>
                        <?php else: ?> — <?php endif; ?>
                    </dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Avg ink density')); ?></dt><dd><?php echo e($analysis->average_ink_density_percent !== null ? number_format((float) $analysis->average_ink_density_percent, 2).'%' : '—'); ?></dd></div>
            </dl>

            <h4 class="mb-2 mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('CMYK breakdown')); ?></h4>
            <dl class="grid grid-cols-4 gap-2 text-sm">
                <?php $__currentLoopData = ['cyan' => __('Cyan'), 'magenta' => __('Magenta'), 'yellow' => __('Yellow'), 'black' => __('Black')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rounded-md bg-slate-50 p-2 text-center">
                        <dt class="text-[10px] uppercase text-slate-500"><?php echo e($label); ?></dt>
                        <dd class="font-semibold"><?php echo e($analysis->{$key.'_coverage_percent'} !== null ? number_format((float) $analysis->{$key.'_coverage_percent'}, 1).'%' : '—'); ?></dd>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </dl>
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

    <?php if(! empty($analysis->dominant_colours)): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6']); ?>
            <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Dominant colours')); ?></h3>
            <div class="flex flex-wrap gap-3">
                <?php $__currentLoopData = $analysis->dominant_colours; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $colour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm">
                        <span class="inline-block h-6 w-6 rounded border border-slate-300" style="background-color: <?php echo e($colour['hex'] ?? '#ccc'); ?>"></span>
                        <span><?php echo e($colour['hex'] ?? '—'); ?></span>
                        <span class="text-xs text-slate-500"><?php echo e(($colour['percent'] ?? 0).'%'); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
    <?php endif; ?>

    <?php if(! empty($analysis->colour_analysis_warnings)): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6']); ?>
            <h3 class="mb-3 text-sm font-semibold text-amber-900"><?php echo e(__('Colour analysis warnings')); ?></h3>
            <ul class="list-inside list-disc space-y-1 text-sm text-amber-900">
                <?php $__currentLoopData = $analysis->colour_analysis_warnings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e(is_string($warning) ? $warning : json_encode($warning)); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
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
    <?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6']); ?>
        <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Ink estimate')); ?></h3>

        <?php if($inkEstimate): ?>
            <dl class="grid grid-cols-2 gap-3 text-sm lg:grid-cols-4">
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Status')); ?></dt>
                    <dd><span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', $inkEstimate->estimation_status->badgeClass()]); ?>"><?php echo e($inkEstimate->estimation_status->label()); ?></span></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Estimated total ink')); ?></dt><dd><?php echo e($inkEstimate->estimated_total_ml !== null ? number_format((float) $inkEstimate->estimated_total_ml, 2).' ml' : '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Estimated cost')); ?></dt><dd><?php echo e($inkEstimate->estimated_ink_cost !== null ? number_format((float) $inkEstimate->estimated_ink_cost, 2) : '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Confidence')); ?></dt><dd><?php echo e($inkEstimate->confidence_score !== null ? number_format((float) $inkEstimate->confidence_score, 1).'%' : '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Formula')); ?></dt><dd><?php echo e($inkEstimate->formula_version ?? '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Coverage area')); ?></dt><dd><?php echo e($inkEstimate->coverage_area_sq_m !== null ? number_format((float) $inkEstimate->coverage_area_sq_m, 4).' m²' : '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Cartridge use')); ?></dt><dd><?php echo e($inkEstimate->estimated_cartridge_percent !== null ? number_format((float) $inkEstimate->estimated_cartridge_percent, 1).'%' : '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Ink profile')); ?></dt><dd><?php echo e($inkEstimate->inkProfile?->name ?? '—'); ?></dd></div>
            </dl>

            <h4 class="mb-2 mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('CMYK ink (ml)')); ?></h4>
            <dl class="grid grid-cols-4 gap-2 text-sm">
                <?php $__currentLoopData = ['cyan' => __('Cyan'), 'magenta' => __('Magenta'), 'yellow' => __('Yellow'), 'black' => __('Black')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rounded-md bg-slate-50 p-2 text-center">
                        <dt class="text-[10px] uppercase text-slate-500"><?php echo e($label); ?></dt>
                        <dd class="font-semibold"><?php echo e($inkEstimate->{'estimated_'.$key.'_ml'} !== null ? number_format((float) $inkEstimate->{'estimated_'.$key.'_ml'}, 2) : '—'); ?></dd>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </dl>

            <?php if(! empty($inkEstimate->warnings)): ?>
                <h4 class="mb-2 mt-4 text-xs font-semibold uppercase tracking-wide text-amber-800"><?php echo e(__('Ink estimation warnings')); ?></h4>
                <ul class="list-inside list-disc space-y-1 text-sm text-amber-900">
                    <?php $__currentLoopData = $inkEstimate->warnings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e(is_string($warning) ? $warning : json_encode($warning)); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php endif; ?>

            <?php if(! empty($inkEstimate->metadata)): ?>
                <details class="mt-4">
                    <summary class="cursor-pointer text-xs font-semibold text-slate-700"><?php echo e(__('Technical estimation data')); ?></summary>
                    <pre class="mt-2 overflow-x-auto rounded-md bg-slate-50 p-3 text-xs text-slate-800"><?php echo e(json_encode($inkEstimate->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                </details>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-sm text-slate-600"><?php echo e(__('No ink estimate yet. Complete colour analysis, then run ink estimation.')); ?></p>
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

    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6']); ?>
        <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Production estimate')); ?></h3>

        <?php if($productionEstimate): ?>
            <dl class="grid grid-cols-2 gap-3 text-sm lg:grid-cols-4">
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Status')); ?></dt>
                    <dd><span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', $productionEstimate->estimation_status->badgeClass()]); ?>"><?php echo e($productionEstimate->estimation_status->label()); ?></span></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Selected machine')); ?></dt><dd><?php echo e($productionEstimate->machineProfile?->machine_code ?? '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Run time')); ?></dt><dd><?php echo e($productionEstimate->estimated_run_hours !== null ? number_format((float) $productionEstimate->estimated_run_hours, 2).' h' : '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Total production cost')); ?></dt><dd><?php echo e($productionEstimate->estimated_total_production_cost !== null ? number_format((float) $productionEstimate->estimated_total_production_cost, 2) : '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Machine cost')); ?></dt><dd><?php echo e($productionEstimate->estimated_machine_cost !== null ? number_format((float) $productionEstimate->estimated_machine_cost, 2) : '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Labour cost')); ?></dt><dd><?php echo e($productionEstimate->estimated_labour_cost !== null ? number_format((float) $productionEstimate->estimated_labour_cost, 2) : '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Ink cost (included)')); ?></dt><dd><?php echo e($productionEstimate->estimated_ink_cost !== null ? number_format((float) $productionEstimate->estimated_ink_cost, 2) : '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Confidence')); ?></dt><dd><?php echo e($productionEstimate->confidence_score !== null ? number_format((float) $productionEstimate->confidence_score, 1).'%' : '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Selection score')); ?></dt><dd><?php echo e($productionEstimate->selection_score !== null ? number_format((float) $productionEstimate->selection_score, 1) : '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Formula')); ?></dt><dd><?php echo e($productionEstimate->formula_version ?? '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Setup cost')); ?></dt><dd><?php echo e($productionEstimate->estimated_setup_cost !== null ? number_format((float) $productionEstimate->estimated_setup_cost, 2) : '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Overhead')); ?></dt><dd><?php echo e($productionEstimate->estimated_overhead_cost !== null ? number_format((float) $productionEstimate->estimated_overhead_cost, 2) : '—'); ?></dd></div>
            </dl>

            <?php if(! empty($productionEstimate->machine_alternatives)): ?>
                <h4 class="mb-2 mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Alternative machines')); ?></h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                                <th class="py-2 pr-3"><?php echo e(__('Machine')); ?></th>
                                <th class="py-2 pr-3"><?php echo e(__('Run (h)')); ?></th>
                                <th class="py-2 pr-3"><?php echo e(__('Cost')); ?></th>
                                <th class="py-2 pr-3"><?php echo e(__('Score')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php $__currentLoopData = $productionEstimate->machine_alternatives; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="py-2 pr-3"><?php echo e($alt['machine_code'] ?? '—'); ?></td>
                                    <td class="py-2 pr-3"><?php echo e(isset($alt['estimated_run_hours']) ? number_format((float) $alt['estimated_run_hours'], 2) : '—'); ?></td>
                                    <td class="py-2 pr-3"><?php echo e(isset($alt['estimated_total_production_cost']) ? number_format((float) $alt['estimated_total_production_cost'], 2) : '—'); ?></td>
                                    <td class="py-2 pr-3"><?php echo e(isset($alt['selection_score']) ? number_format((float) $alt['selection_score'], 1) : '—'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php if(! empty($productionEstimate->warnings)): ?>
                <h4 class="mb-2 mt-4 text-xs font-semibold uppercase tracking-wide text-amber-800"><?php echo e(__('Production estimation warnings')); ?></h4>
                <ul class="list-inside list-disc space-y-1 text-sm text-amber-900">
                    <?php $__currentLoopData = $productionEstimate->warnings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e(is_string($warning) ? $warning : json_encode($warning)); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php endif; ?>

            <?php if(! empty($productionEstimate->metadata)): ?>
                <details class="mt-4">
                    <summary class="cursor-pointer text-xs font-semibold text-slate-700"><?php echo e(__('Technical production data')); ?></summary>
                    <pre class="mt-2 overflow-x-auto rounded-md bg-slate-50 p-3 text-xs text-slate-800"><?php echo e(json_encode($productionEstimate->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                </details>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-sm text-slate-600"><?php echo e(__('No production estimate yet. Run production estimation to select a machine and calculate costs.')); ?></p>
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

    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6']); ?>
        <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Quotation recommendation')); ?></h3>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.quotation.estimate')): ?>
            <form method="POST" action="<?php echo e(route('admin.printing-intelligence.artwork-analysis.estimate-quotation', $analysis)); ?>" class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="text-xs text-slate-500"><?php echo e(__('Quantity')); ?></label>
                    <input type="number" name="quantity" min="1" value="<?php echo e(old('quantity', $quotationEstimate?->quantity ?? 1)); ?>" class="erp-input w-full text-sm">
                </div>
                <div>
                    <label class="text-xs text-slate-500"><?php echo e(__('Material item')); ?></label>
                    <select name="material_inventory_item_id" class="erp-select w-full text-sm">
                        <option value=""><?php echo e(__('Select material')); ?></option>
                        <?php $__currentLoopData = $materialItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($item->id); ?>" <?php if(old('material_inventory_item_id', $quotationEstimate?->material_inventory_item_id) == $item->id): echo 'selected'; endif; ?>><?php echo e($item->item_name); ?> (<?php echo e($item->sku); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-500"><?php echo e(__('Manual material unit cost')); ?></label>
                    <input type="number" step="0.0001" name="material_unit_cost_override" value="<?php echo e(old('material_unit_cost_override')); ?>" class="erp-input w-full text-sm" placeholder="<?php echo e(__('Optional override')); ?>">
                </div>
                <div>
                    <label class="text-xs text-slate-500"><?php echo e(__('Minimum margin %')); ?></label>
                    <input type="number" step="0.1" name="minimum_margin_percent" value="<?php echo e(old('minimum_margin_percent', $quotationEstimate?->minimum_margin_percent ?? $piConfig['default_minimum_margin_percent'])); ?>" class="erp-input w-full text-sm">
                </div>
                <div>
                    <label class="text-xs text-slate-500"><?php echo e(__('Target margin %')); ?></label>
                    <input type="number" step="0.1" name="target_margin_percent" value="<?php echo e(old('target_margin_percent', $quotationEstimate?->target_margin_percent ?? $piConfig['default_target_margin_percent'])); ?>" class="erp-input w-full text-sm">
                </div>
                <div>
                    <label class="text-xs text-slate-500"><?php echo e(__('Wastage %')); ?></label>
                    <input type="number" step="0.1" name="wastage_percent" value="<?php echo e(old('wastage_percent', $piConfig['default_wastage_percent'])); ?>" class="erp-input w-full text-sm">
                </div>
                <div class="md:col-span-2 lg:col-span-3">
                    <button type="submit" class="erp-btn-secondary text-xs"><?php echo e(__('Generate Quotation Estimate')); ?></button>
                </div>
            </form>
        <?php endif; ?>

        <?php if($quotationEstimate): ?>
            <dl class="grid grid-cols-2 gap-3 text-sm lg:grid-cols-4">
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Status')); ?></dt>
                    <dd><span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', $quotationEstimate->estimation_status->badgeClass()]); ?>"><?php echo e($quotationEstimate->estimation_status->label()); ?></span></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Total cost')); ?></dt><dd><?php echo e(number_format((float) $quotationEstimate->estimated_total_cost, 2)); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Minimum selling price')); ?></dt><dd><?php echo e(number_format((float) $quotationEstimate->minimum_selling_price, 2)); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Recommended price')); ?></dt><dd class="font-semibold"><?php echo e(number_format((float) $quotationEstimate->recommended_selling_price, 2)); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Expected margin')); ?></dt><dd><?php echo e($quotationEstimate->expected_margin_percent !== null ? number_format((float) $quotationEstimate->expected_margin_percent, 1).'%' : '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Confidence')); ?></dt><dd><?php echo e($quotationEstimate->confidence_score !== null ? number_format((float) $quotationEstimate->confidence_score, 1).'%' : '—'); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Material')); ?></dt><dd><?php echo e(number_format((float) $quotationEstimate->estimated_material_cost, 2)); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Ink')); ?></dt><dd><?php echo e(number_format((float) $quotationEstimate->estimated_ink_cost, 2)); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Machine/process')); ?></dt><dd><?php echo e(number_format((float) $quotationEstimate->estimated_machine_cost, 2)); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Labour')); ?></dt><dd><?php echo e(number_format((float) $quotationEstimate->estimated_labour_cost, 2)); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Electricity')); ?></dt><dd><?php echo e(number_format((float) $quotationEstimate->estimated_electricity_cost, 2)); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Overhead')); ?></dt><dd><?php echo e(number_format((float) $quotationEstimate->estimated_overhead_cost, 2)); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Wastage')); ?></dt><dd><?php echo e(number_format((float) $quotationEstimate->estimated_wastage_cost, 2)); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Formula')); ?></dt><dd><?php echo e($quotationEstimate->formula_version ?? '—'); ?></dd></div>
            </dl>

            <?php if(! empty($quotationEstimate->warnings)): ?>
                <h4 class="mb-2 mt-4 text-xs font-semibold uppercase tracking-wide text-amber-800"><?php echo e(__('Quotation estimation warnings')); ?></h4>
                <ul class="list-inside list-disc space-y-1 text-sm text-amber-900">
                    <?php $__currentLoopData = $quotationEstimate->warnings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e(is_string($warning) ? $warning : json_encode($warning)); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php endif; ?>

            <?php if($analysis->quotation && $piConfig['allow_apply_to_quotation'] && in_array($quotationEstimate->estimation_status, [\App\Enums\QuotationEstimationStatus::Completed, \App\Enums\QuotationEstimationStatus::ManualReview], true)): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.quotation.apply-estimate')): ?>
                    <form method="POST" action="<?php echo e(route('admin.printing-intelligence.artwork-analysis.apply-quotation-estimate', [$analysis, $quotationEstimate])); ?>" class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-4">
                        <?php echo csrf_field(); ?>
                        <p class="text-sm text-amber-900"><?php echo e(__('This does not change quotation line totals or approval status. It only records advisory estimate fields.')); ?></p>
                        <?php if($piConfig['require_confirmation_to_apply']): ?>
                            <label class="mt-3 flex items-center gap-2 text-sm text-amber-900">
                                <input type="checkbox" name="confirm_apply" value="1" required class="rounded border-amber-400">
                                <?php echo e(__('I confirm applying this advisory estimate to the linked quotation.')); ?>

                            </label>
                        <?php endif; ?>
                        <button type="submit" class="erp-btn-secondary mt-3 text-xs"><?php echo e(__('Apply advisory estimate to quotation')); ?></button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-sm text-slate-600"><?php echo e(__('Generate a quotation estimate after ink and production estimates are available.')); ?></p>
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

    <?php if($analysis->pages->isNotEmpty()): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6']); ?>
            <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Per-page coverage')); ?></h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="py-2 pr-3">#</th>
                            <th class="py-2 pr-3"><?php echo e(__('CMYK %')); ?></th>
                            <th class="py-2 pr-3"><?php echo e(__('C/M/Y/K')); ?></th>
                            <th class="py-2 pr-3"><?php echo e(__('White %')); ?></th>
                            <th class="py-2 pr-3"><?php echo e(__('Transparent %')); ?></th>
                            <th class="py-2 pr-3"><?php echo e(__('Class')); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $__currentLoopData = $analysis->pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="py-2 pr-3"><?php echo e($page->page_number); ?></td>
                                <td class="py-2 pr-3"><?php echo e($page->cmyk_coverage_percent !== null ? number_format((float) $page->cmyk_coverage_percent, 2) : '—'); ?></td>
                                <td class="py-2 pr-3 text-xs">
                                    <?php if($page->cyan_coverage_percent !== null): ?>
                                        <?php echo e(number_format((float) $page->cyan_coverage_percent, 1)); ?>/<?php echo e(number_format((float) $page->magenta_coverage_percent, 1)); ?>/<?php echo e(number_format((float) $page->yellow_coverage_percent, 1)); ?>/<?php echo e(number_format((float) $page->black_coverage_percent, 1)); ?>

                                    <?php else: ?> — <?php endif; ?>
                                </td>
                                <td class="py-2 pr-3"><?php echo e($page->white_area_percent !== null ? number_format((float) $page->white_area_percent, 2) : '—'); ?></td>
                                <td class="py-2 pr-3"><?php echo e($page->transparent_area_percent !== null ? number_format((float) $page->transparent_area_percent, 2) : '—'); ?></td>
                                <td class="py-2 pr-3"><?php echo e($page->coverage_class?->label() ?? '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
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
    <?php endif; ?>

    <?php if(! empty($analysis->colour_analysis_raw)): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6']); ?>
            <details>
                <summary class="cursor-pointer text-sm font-semibold text-slate-900"><?php echo e(__('Raw colour analysis data')); ?></summary>
                <pre class="mt-3 overflow-x-auto rounded-md bg-slate-50 p-3 text-xs text-slate-800"><?php echo e(json_encode($analysis->colour_analysis_raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
            </details>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\printing-intelligence\artwork-analysis\show.blade.php ENDPATH**/ ?>