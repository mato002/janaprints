<?php if (isset($component)) { $__componentOriginald3ad0f200dc20b794011e332a16c068d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3ad0f200dc20b794011e332a16c068d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal-form','data' => ['title' => $panel['header']['job_number'] ?? $jobCard->job_card_number,'maxWidth' => '4xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($panel['header']['job_number'] ?? $jobCard->job_card_number),'maxWidth' => '4xl']); ?>
    <div class="space-y-4">
        <div class="production-floor-panel-hero">
            <dl>
                <div class="sm:col-span-2">
                    <dt class="text-slate-500"><?php echo e(__('Customer')); ?></dt>
                    <dd class="text-base font-semibold text-erp-primary"><?php echo e($panel['header']['customer'] ?? '—'); ?></dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-slate-500"><?php echo e(__('Product')); ?></dt>
                    <dd class="font-medium"><?php echo e($panel['header']['product'] ?? '—'); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e(__('Artwork / job status')); ?></dt>
                    <dd><?php echo e($panel['job']['status_label'] ?? ($panel['header']['status'] ?? $jobCard->status->label())); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e(__('Production stage')); ?></dt>
                    <dd><?php echo e($panel['header']['stage'] ?? ''); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e(__('Required date')); ?></dt>
                    <dd><?php echo e($panel['header']['required_date'] ?? '—'); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e(__('Materials / fulfilment')); ?></dt>
                    <dd><?php echo e($panel['fulfilment']['status_label'] ?? __('Not started')); ?></dd>
                </div>
            </dl>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="production-floor-panel-status-card">
                <dt><?php echo e(__('Machine')); ?></dt>
                <dd><?php echo e($panel['job']['machine'] ?? '—'); ?></dd>
            </div>
            <div class="production-floor-panel-status-card">
                <dt><?php echo e(__('Work center')); ?></dt>
                <dd><?php echo e($panel['job']['work_center'] ?? '—'); ?></dd>
            </div>
            <div class="production-floor-panel-status-card">
                <dt><?php echo e(__('Priority')); ?></dt>
                <dd class="capitalize"><?php echo e($panel['job']['priority_label'] ?? '—'); ?></dd>
            </div>
            <div class="production-floor-panel-status-card">
                <dt><?php echo e(__('Vendor')); ?></dt>
                <dd><?php echo e($panel['job']['vendor'] ?? __('Not at vendor')); ?></dd>
            </div>
        </div>

        <?php if(! empty($panel['blockers'])): ?>
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                <p class="mb-1 font-medium"><?php echo e(__('Production blockers')); ?></p>
                <ul class="list-disc space-y-0.5 pl-4">
                    <?php $__currentLoopData = $panel['blockers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $blocker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($blocker); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if($jobCard->production_notes_snapshot): ?>
            <div class="rounded-lg border border-erp-border bg-white p-3">
                <h3 class="mb-1 text-xs font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Production notes')); ?></h3>
                <p class="whitespace-pre-wrap text-sm text-slate-700"><?php echo e($jobCard->production_notes_snapshot); ?></p>
            </div>
        <?php endif; ?>

        <details class="rounded-lg border border-erp-border bg-slate-50/60 p-3">
            <summary class="cursor-pointer text-sm font-medium text-slate-700"><?php echo e(__('Commercial summary')); ?></summary>
            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-slate-500"><?php echo e(__('Job status')); ?></dt><dd><?php echo e($panel['header']['status'] ?? $jobCard->status->label()); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Fulfilment')); ?></dt><dd><?php echo e($panel['fulfilment']['status_label'] ?? '—'); ?></dd></div>
            </dl>
        </details>

        <p class="text-xs text-slate-500"><?php echo e(__('Close this preview and use the job panel on the floor for next-step actions.')); ?></p>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald3ad0f200dc20b794011e332a16c068d)): ?>
<?php $attributes = $__attributesOriginald3ad0f200dc20b794011e332a16c068d; ?>
<?php unset($__attributesOriginald3ad0f200dc20b794011e332a16c068d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald3ad0f200dc20b794011e332a16c068d)): ?>
<?php $component = $__componentOriginald3ad0f200dc20b794011e332a16c068d; ?>
<?php unset($__componentOriginald3ad0f200dc20b794011e332a16c068d); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/floor/job-modal.blade.php ENDPATH**/ ?>