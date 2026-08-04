<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $fiscalYear->name,'breadcrumbs' => [['label' => __('Accounting Periods'), 'url' => route('admin.accounting.periods.index')], ['label' => $fiscalYear->name]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $fiscalYear->name,'description' => $fiscalYear->code.' · '.$fiscalYear->start_date->format('Y-m-d').' → '.$fiscalYear->end_date->format('Y-m-d')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fiscalYear->name),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fiscalYear->code.' · '.$fiscalYear->start_date->format('Y-m-d').' → '.$fiscalYear->end_date->format('Y-m-d'))]); ?>
        <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => match($fiscalYear->status) {
            App\Enums\FiscalYearStatus::Open => 'success',
            App\Enums\FiscalYearStatus::YearEndPreparation => 'warning',
            App\Enums\FiscalYearStatus::Closed => 'neutral',
            App\Enums\FiscalYearStatus::Locked => 'danger',
        }]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match($fiscalYear->status) {
            App\Enums\FiscalYearStatus::Open => 'success',
            App\Enums\FiscalYearStatus::YearEndPreparation => 'warning',
            App\Enums\FiscalYearStatus::Closed => 'neutral',
            App\Enums\FiscalYearStatus::Locked => 'danger',
        })]); ?><?php echo e($fiscalYear->status->label()); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
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

    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
        <h3 class="mb-3 text-sm font-semibold"><?php echo e(__('Fiscal year controls')); ?></h3>
        <div class="flex flex-wrap gap-2">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('yearEndPrep', $fiscalYear)): ?>
                <?php if($fiscalYear->status === App\Enums\FiscalYearStatus::Open): ?>
                    <form method="POST" action="<?php echo e(route('admin.accounting.periods.fiscal-years.year-end-prep', $fiscalYear)); ?>"><?php echo csrf_field(); ?>
                        <button type="submit" class="erp-btn-secondary"><?php echo e(__('Year-end preparation')); ?></button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('close', $fiscalYear)): ?>
                <?php if(in_array($fiscalYear->status, [App\Enums\FiscalYearStatus::Open, App\Enums\FiscalYearStatus::YearEndPreparation], true)): ?>
                    <form method="POST" action="<?php echo e(route('admin.accounting.periods.fiscal-years.close', $fiscalYear)); ?>"><?php echo csrf_field(); ?>
                        <button type="submit" class="erp-btn-primary"><?php echo e(__('Close fiscal year')); ?></button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('lock', $fiscalYear)): ?>
                <?php if($fiscalYear->status === App\Enums\FiscalYearStatus::Closed): ?>
                    <form method="POST" action="<?php echo e(route('admin.accounting.periods.fiscal-years.lock', $fiscalYear)); ?>"><?php echo csrf_field(); ?>
                        <button type="submit" class="erp-btn-secondary"><?php echo e(__('Lock fiscal year')); ?></button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('reopen', $fiscalYear)): ?>
                <?php if(in_array($fiscalYear->status, [App\Enums\FiscalYearStatus::Closed, App\Enums\FiscalYearStatus::YearEndPreparation], true)): ?>
                    <form method="POST" action="<?php echo e(route('admin.accounting.periods.fiscal-years.reopen', $fiscalYear)); ?>"><?php echo csrf_field(); ?>
                        <button type="submit" class="erp-btn-secondary"><?php echo e(__('Reopen fiscal year')); ?></button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php if($fiscalYear->year_end_prep_at): ?>
            <p class="mt-2 text-[11px] text-slate-500"><?php echo e(__('Year-end prep')); ?>: <?php echo e($fiscalYear->year_end_prep_at->format('Y-m-d H:i')); ?> — <?php echo e($fiscalYear->yearEndPrepByUser?->name); ?></p>
        <?php endif; ?>
        <?php if($fiscalYear->notes): ?>
            <p class="mt-2 text-sm text-slate-600"><?php echo e($fiscalYear->notes); ?></p>
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

    <?php if($closeAudits->isNotEmpty()): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
            <h3 class="mb-3 text-sm font-semibold"><?php echo e(__('Close audit trail')); ?></h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-erp-border text-left text-[11px] uppercase text-slate-500">
                        <th class="pb-2"><?php echo e(__('Date')); ?></th>
                        <th class="pb-2"><?php echo e(__('Type')); ?></th>
                        <th class="pb-2"><?php echo e(__('Period')); ?></th>
                        <th class="pb-2"><?php echo e(__('Net amount')); ?></th>
                        <th class="pb-2"><?php echo e(__('Journal')); ?></th>
                        <th class="pb-2"><?php echo e(__('By')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $closeAudits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $audit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-t border-erp-border">
                            <td class="py-2"><?php echo e($audit->performed_at->format('Y-m-d H:i')); ?></td>
                            <td class="py-2"><?php echo e($audit->close_type->label()); ?></td>
                            <td class="py-2"><?php echo e($audit->accountingPeriod?->code ?? '—'); ?></td>
                            <td class="py-2 font-mono"><?php echo e(number_format($audit->net_amount, 2)); ?></td>
                            <td class="py-2">
                                <?php if($audit->journal_id): ?>
                                    <a href="<?php echo e(route('admin.accounting.journals.show', $audit->journal_id)); ?>" class="text-erp-accent"><?php echo e($audit->journal?->journal_number); ?></a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="py-2"><?php echo e($audit->performedByUser?->name); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
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

    <?php if (isset($component)) { $__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.data-table','data' => ['searchPlaceholder' => __('Search periods…'),'exportRoute' => 'admin.accounting.exports','exportRouteParams' => ['listing' => 'accounting-periods'],'exportQuery' => ['fiscal_year_id' => $fiscalYear->id],'formatInPath' => true,'exportFilename' => 'accounting-periods']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.data-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['search-placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Search periods…')),'export-route' => 'admin.accounting.exports','export-route-params' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['listing' => 'accounting-periods']),'export-query' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['fiscal_year_id' => $fiscalYear->id]),'format-in-path' => true,'export-filename' => 'accounting-periods']); ?>
         <?php $__env->slot('head', null, []); ?> 
            <tr>
                <th scope="col">#</th>
                <th scope="col"><?php echo e(__('Period')); ?></th>
                <th scope="col" class="hidden md:table-cell"><?php echo e(__('Dates')); ?></th>
                <th scope="col"><?php echo e(__('Status')); ?></th>
                <th scope="col" class="erp-table-actions-col"><?php echo e(__('Actions')); ?></th>
            </tr>
         <?php $__env->endSlot(); ?>
         <?php $__env->slot('body', null, []); ?> 
            <?php $__currentLoopData = $fiscalYear->periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr x-show="rowVisible(<?php echo \Illuminate\Support\Js::from(strtolower($period->name.' '.$period->code.' '.$period->status->value))->toHtml() ?>)">
                    <td class="text-slate-500"><?php echo e($period->period_number); ?></td>
                    <td>
                        <span class="font-medium"><?php echo e($period->name); ?></span>
                        <div class="font-mono text-[11px] text-slate-500"><?php echo e($period->code); ?>

                            <?php if($period->is_current): ?><span class="text-erp-accent">· <?php echo e(__('Current')); ?></span><?php endif; ?>
                        </div>
                    </td>
                    <td class="hidden md:table-cell text-sm text-slate-600"><?php echo e($period->start_date->format('Y-m-d')); ?> → <?php echo e($period->end_date->format('Y-m-d')); ?></td>
                    <td>
                        <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => match($period->status) {
                            App\Enums\AccountingPeriodStatus::Open => 'success',
                            App\Enums\AccountingPeriodStatus::Closed => 'neutral',
                            App\Enums\AccountingPeriodStatus::Locked => 'danger',
                        }]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match($period->status) {
                            App\Enums\AccountingPeriodStatus::Open => 'success',
                            App\Enums\AccountingPeriodStatus::Closed => 'neutral',
                            App\Enums\AccountingPeriodStatus::Locked => 'danger',
                        })]); ?><?php echo e($period->status->label()); ?> <?php echo $__env->renderComponent(); ?>
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
                    <td class="erp-table-actions-col">
                        <div class="flex flex-wrap justify-end gap-1">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('setCurrent', $period)): ?>
                                <?php if($period->status === App\Enums\AccountingPeriodStatus::Open && ! $period->is_current): ?>
                                    <form method="POST" action="<?php echo e(route('admin.accounting.periods.set-current', $period)); ?>"><?php echo csrf_field(); ?>
                                        <button type="submit" class="text-[11px] text-erp-accent"><?php echo e(__('Set current')); ?></button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('close', $period)): ?>
                                <?php if($period->status === App\Enums\AccountingPeriodStatus::Open): ?>
                                    <form method="POST" action="<?php echo e(route('admin.accounting.periods.close', $period)); ?>"><?php echo csrf_field(); ?>
                                        <button type="submit" class="text-[11px] text-erp-accent"><?php echo e(__('Close')); ?></button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('lock', $period)): ?>
                                <?php if($period->status === App\Enums\AccountingPeriodStatus::Closed): ?>
                                    <form method="POST" action="<?php echo e(route('admin.accounting.periods.lock', $period)); ?>"><?php echo csrf_field(); ?>
                                        <button type="submit" class="text-[11px] text-erp-accent"><?php echo e(__('Lock')); ?></button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('reopen', $period)): ?>
                                <?php if($period->status === App\Enums\AccountingPeriodStatus::Closed): ?>
                                    <form method="POST" action="<?php echo e(route('admin.accounting.periods.reopen', $period)); ?>"><?php echo csrf_field(); ?>
                                        <button type="submit" class="text-[11px] text-slate-600"><?php echo e(__('Reopen')); ?></button>
                                    </form>
                                <?php endif; ?>
                                <?php if($period->status === App\Enums\AccountingPeriodStatus::Locked): ?>
                                    <form method="POST" action="<?php echo e(route('admin.accounting.periods.unlock', $period)); ?>"><?php echo csrf_field(); ?>
                                        <button type="submit" class="text-[11px] text-slate-600"><?php echo e(__('Unlock')); ?></button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\accounting\periods\show.blade.php ENDPATH**/ ?>