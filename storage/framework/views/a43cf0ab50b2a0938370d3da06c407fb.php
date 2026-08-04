<?php
    $profile = $tabData['profile'] ?? [];
    $aging = $tabData['aging']['buckets'] ?? [];
    $collection = $tabData['collection'] ?? [];
    $section = $tabData['section'] ?? 'overview';
    $invoices = $tabData['invoices'] ?? null;
    $customer = $customer ?? null;
?>

<?php if(! empty($tabData['restricted'])): ?>
    <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'lock-closed','title' => __('Access restricted')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'lock-closed','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Access restricted'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php else: ?>
    <nav class="mb-4 flex gap-2 border-b border-slate-200">
        <a href="<?php echo e(route('admin.crm.customers.show', ['customer' => $customer, 'tab' => 'financial', 'financial_section' => 'overview'])); ?>"
           class="px-3 py-2 text-sm font-medium <?php echo e($section === 'overview' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-slate-600'); ?>">
            <?php echo e(__('Intelligence')); ?>

        </a>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('statements.view')): ?>
            <a href="<?php echo e(route('admin.crm.customers.show', ['customer' => $customer, 'tab' => 'financial', 'financial_section' => 'statement'])); ?>"
               class="px-3 py-2 text-sm font-medium <?php echo e($section === 'statement' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-slate-600'); ?>">
                <?php echo e(__('Statement')); ?>

            </a>
        <?php endif; ?>
    </nav>

    <?php if($section === 'statement' && ! empty($tabData['statement'])): ?>
        <?php echo $__env->make('admin.crm.customers.workspace.partials.statement-ledger', [
            'statement' => $tabData['statement'],
            'customer' => $customer,
            'from' => $tabData['statement_from'],
            'to' => $tabData['statement_to'],
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php else: ?>
        <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <?php $__currentLoopData = [
                ['label' => __('Total Invoiced'), 'value' => number_format($profile['total_invoiced'] ?? 0, 2)],
                ['label' => __('Total Paid'), 'value' => number_format($profile['total_paid'] ?? 0, 2)],
                ['label' => __('Outstanding'), 'value' => number_format($profile['outstanding'] ?? 0, 2)],
                ['label' => __('Customer Credit'), 'value' => number_format($profile['credit_balance'] ?? 0, 2)],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => $kpi['label'],'value' => $kpi['value'],'icon' => 'currency-dollar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['label']),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['value']),'icon' => 'currency-dollar']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $attributes = $__attributesOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $component = $__componentOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__componentOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="mb-6 grid gap-4 lg:grid-cols-2">
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
                <h3 class="mb-3 text-sm font-semibold"><?php echo e(__('Collection intelligence')); ?></h3>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-slate-500"><?php echo e(__('Overdue amount')); ?></dt><dd class="font-mono font-medium"><?php echo e(number_format($profile['overdue_amount'] ?? 0, 2)); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Collection risk')); ?></dt>
                        <dd>
                            <?php $risk = strtoupper($profile['collection_risk'] ?? 'LOW'); ?>
                            <span class="erp-badge <?php echo e($risk === 'HIGH' ? 'erp-badge-warning' : ($risk === 'MEDIUM' ? 'erp-badge-muted' : 'erp-badge-success')); ?>"><?php echo e($risk); ?></span>
                        </dd>
                    </div>
                    <div><dt class="text-slate-500"><?php echo e(__('Avg. payment days')); ?></dt><dd><?php echo e($profile['average_payment_days'] !== null ? $profile['average_payment_days'].' '.__('days') : '—'); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Oldest outstanding')); ?></dt>
                        <dd><?php echo e($profile['oldest_outstanding_invoice']['invoice_number'] ?? '—'); ?></dd>
                    </div>
                    <div><dt class="text-slate-500"><?php echo e(__('Invoice count')); ?></dt><dd><?php echo e($collection['invoice_count'] ?? 0); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Payment count')); ?></dt><dd><?php echo e($collection['payment_count'] ?? 0); ?></dd></div>
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
                <h3 class="mb-3 text-sm font-semibold"><?php echo e(__('AR aging (by due date)')); ?></h3>
                <dl class="space-y-2 text-sm">
                    <?php $__currentLoopData = [
                        'current' => __('Current'),
                        '1_30' => __('1–30 days'),
                        '31_60' => __('31–60 days'),
                        '61_90' => __('61–90 days'),
                        '90_plus' => __('90+ days'),
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex justify-between">
                            <dt class="text-slate-500"><?php echo e($label); ?></dt>
                            <dd class="font-mono"><?php echo e(number_format($aging[$key] ?? 0, 2)); ?></dd>
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
            <div class="mb-4 flex justify-between">
                <h3 class="text-sm font-semibold"><?php echo e(__('Open invoices')); ?></h3>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Sales\CustomerInvoice::class)): ?>
                    <a href="<?php echo e(route('admin.invoices.create', ['customer_id' => $customer->id])); ?>" class="erp-btn-primary text-sm"><?php echo e(__('New invoice')); ?></a>
                <?php endif; ?>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left"><?php echo e(__('Invoice')); ?></th>
                            <th class="px-3 py-2 text-left"><?php echo e(__('Due')); ?></th>
                            <th class="px-3 py-2 text-right"><?php echo e(__('Total')); ?></th>
                            <th class="px-3 py-2 text-left"><?php echo e(__('Status')); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="px-3 py-2"><a href="<?php echo e(route('admin.invoices.show', $invoice)); ?>" class="font-mono text-indigo-600"><?php echo e($invoice->invoice_number); ?></a></td>
                                <td class="px-3 py-2"><?php echo e($invoice->due_date->format('M j, Y')); ?></td>
                                <td class="px-3 py-2 text-right font-mono"><?php echo e(number_format($invoice->total_amount, 2)); ?></td>
                                <td class="px-3 py-2"><?php if (isset($component)) { $__componentOriginal6b1e38fda99422b6943f33aba545ca7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.enum-status-badge','data' => ['status' => $invoice->status->value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.enum-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice->status->value)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $attributes = $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $component = $__componentOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="px-3 py-6 text-center text-slate-500"><?php echo e(__('No invoices')); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if($invoices && method_exists($invoices, 'links')): ?>
                <div class="mt-4"><?php if (isset($component)) { $__componentOriginal26f2fa5d0c1830da52192272c1d5b300 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26f2fa5d0c1830da52192272c1d5b300 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-pagination','data' => ['paginator' => $invoices]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoices)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal26f2fa5d0c1830da52192272c1d5b300)): ?>
<?php $attributes = $__attributesOriginal26f2fa5d0c1830da52192272c1d5b300; ?>
<?php unset($__attributesOriginal26f2fa5d0c1830da52192272c1d5b300); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal26f2fa5d0c1830da52192272c1d5b300)): ?>
<?php $component = $__componentOriginal26f2fa5d0c1830da52192272c1d5b300; ?>
<?php unset($__componentOriginal26f2fa5d0c1830da52192272c1d5b300); ?>
<?php endif; ?></div>
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
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\workspace\tabs\financial.blade.php ENDPATH**/ ?>