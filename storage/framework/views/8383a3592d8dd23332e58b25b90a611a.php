<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Payslip').' · '.($payslip->employee?->full_name ?? $payslip->reference),'breadcrumbs' => [['label' => __('Payroll'), 'url' => route('admin.hr.payroll.dashboard')], ['label' => $payslip->payrollRun?->reference, 'url' => route('admin.hr.payroll.show', $payslip->payrollRun)], ['label' => __('Payslip')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
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
        <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-erp-primary"><?php echo e($payslip->employee?->full_name); ?></h2>
                <p class="text-sm text-slate-600">
                    <?php echo e($payslip->payrollRun?->period_start?->format('M j, Y')); ?> – <?php echo e($payslip->payrollRun?->period_end?->format('M j, Y')); ?>

                </p>
            </div>
            <div class="flex gap-2">
                <?php if (isset($component)) { $__componentOriginal3c4886a9ff00288f144ef8192d533805 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c4886a9ff00288f144ef8192d533805 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.documents.pdf-download-button','data' => ['url' => route('admin.hr.payroll.payslip.download', $payslip),'filename' => ($payslip->reference ?? 'payslip-'.$payslip->id).'.pdf']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('documents.pdf-download-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.hr.payroll.payslip.download', $payslip)),'filename' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($payslip->reference ?? 'payslip-'.$payslip->id).'.pdf')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c4886a9ff00288f144ef8192d533805)): ?>
<?php $attributes = $__attributesOriginal3c4886a9ff00288f144ef8192d533805; ?>
<?php unset($__attributesOriginal3c4886a9ff00288f144ef8192d533805); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c4886a9ff00288f144ef8192d533805)): ?>
<?php $component = $__componentOriginal3c4886a9ff00288f144ef8192d533805; ?>
<?php unset($__componentOriginal3c4886a9ff00288f144ef8192d533805); ?>
<?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('process', $payslip->payrollRun)): ?>
                    <?php if($payslip->employee?->email): ?>
                        <form method="POST" action="<?php echo e(route('admin.hr.payroll.payslip.email', $payslip)); ?>" class="inline" data-turbo="false">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="erp-btn-secondary">
                                <?php echo e($payslip->emailed_at ? __('Resend email') : __('Email payslip')); ?>

                            </button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if($payslip->released_at): ?>
                    <span class="erp-badge erp-badge--success"><?php echo e(__('Released')); ?></span>
                <?php endif; ?>
                <?php if($payslip->emailed_at): ?>
                    <span class="erp-badge erp-badge--success"><?php echo e(__('Emailed :date', ['date' => $payslip->emailed_at->format('M j, Y')])); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <dl class="mb-6 grid gap-4 sm:grid-cols-3">
            <div><dt class="text-xs text-slate-500"><?php echo e(__('Basic salary')); ?></dt><dd class="font-medium tabular-nums"><?php echo e(number_format($payslip->basic_salary, 2)); ?></dd></div>
            <div><dt class="text-xs text-slate-500"><?php echo e(__('Gross pay')); ?></dt><dd class="font-medium tabular-nums"><?php echo e(number_format($payslip->gross_pay, 2)); ?></dd></div>
            <div><dt class="text-xs text-slate-500"><?php echo e(__('Net pay')); ?></dt><dd class="font-medium tabular-nums"><?php echo e(number_format($payslip->net_pay, 2)); ?></dd></div>
        </dl>

        <div class="grid gap-6 lg:grid-cols-2">
            <div>
                <h3 class="mb-2 text-sm font-semibold"><?php echo e(__('Earnings')); ?></h3>
                <div class="overflow-x-auto rounded-lg border border-erp-border">
                    <table class="erp-table w-full">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Item')); ?></th>
                                <th class="text-right"><?php echo e(__('Amount')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $payslip->items->where('item_type', App\Enums\PayrollItemType::Allowance); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($item->name); ?></td>
                                    <td class="tabular-nums text-right"><?php echo e(number_format($item->amount, 2)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <tr class="bg-slate-50">
                                <th scope="row"><?php echo e(__('Gross pay')); ?></th>
                                <th class="tabular-nums text-right"><?php echo e(number_format($payslip->gross_pay, 2)); ?></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                <h3 class="mb-2 text-sm font-semibold"><?php echo e(__('Deductions & statutories')); ?></h3>
                <div class="overflow-x-auto rounded-lg border border-erp-border">
                    <table class="erp-table w-full">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Item')); ?></th>
                                <th class="text-right"><?php echo e(__('Amount')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $payslip->items->where('item_type', App\Enums\PayrollItemType::Deduction); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($item->name); ?></td>
                                    <td class="tabular-nums text-right"><?php echo e(number_format($item->amount, 2)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <tr class="bg-slate-50">
                                <th scope="row"><?php echo e(__('Total deductions')); ?></th>
                                <th class="tabular-nums text-right"><?php echo e(number_format($payslip->total_deductions, 2)); ?></th>
                            </tr>
                            <tr class="bg-emerald-50">
                                <th scope="row"><?php echo e(__('Net pay')); ?></th>
                                <th class="tabular-nums text-right text-emerald-800"><?php echo e(number_format($payslip->net_pay, 2)); ?></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\payroll\payslip-show.blade.php ENDPATH**/ ?>